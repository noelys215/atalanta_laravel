<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackfillProductMedia extends Command
{
    protected $signature = 'products:backfill-media
                            {--limit= : Maximum number of products to process}
                            {--force : Re-attach media even if already present}';

    protected $description = 'Backfill product image URLs into Spatie Media Library';

    public function handle(): int
    {
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = (bool) $this->option('force');

        $query = Product::query();

        if (! $force) {
            $query->whereDoesntHave('media', function ($mediaQuery) {
                $mediaQuery->where('collection_name', 'product_images');
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $processed = 0;
        $attached = 0;
        $skipped = 0;

        $query->chunkById(100, function ($products) use (&$processed, &$attached, &$skipped) {
            foreach ($products as $product) {
                $processed++;

                $images = is_array($product->image) ? $product->image : [];
                $images = array_values(array_filter($images, fn ($value) => is_string($value) && $value !== ''));

                if (count($images) === 0) {
                    $skipped++;
                    continue;
                }

                $product->clearMediaCollection('product_images');

                foreach ($images as $imageUrl) {
                    try {
                        $localPath = $this->resolveLocalImagePath($imageUrl);

                        if ($localPath) {
                            $product->addMedia($localPath)->toMediaCollection('product_images');
                        } else {
                            $product->addMediaFromUrl($imageUrl)->toMediaCollection('product_images');
                        }

                        $attached++;
                    } catch (\Throwable $exception) {
                        $this->warn("Failed to attach image for product {$product->id}: {$exception->getMessage()}");
                    }
                }

                $product->forceFill([
                    'image' => $product->getMedia('product_images')->map(fn ($media) => $media->getUrl())->all(),
                ])->save();
            }
        });

        $this->info("Processed: {$processed}, Attached: {$attached}, Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function resolveLocalImagePath(string $imageUrl): ?string
    {
        $storagePrefix = '/storage/';

        if (Str::startsWith($imageUrl, $storagePrefix)) {
            $relativePath = ltrim(Str::after($imageUrl, $storagePrefix), '/');
            $fullPath = Storage::disk('public')->path($relativePath);

            return is_file($fullPath) ? $fullPath : null;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $appStoragePrefix = $appUrl !== '' ? $appUrl . $storagePrefix : '';

        if ($appStoragePrefix !== '' && Str::startsWith($imageUrl, $appStoragePrefix)) {
            $relativePath = ltrim(Str::after($imageUrl, $appStoragePrefix), '/');
            $fullPath = Storage::disk('public')->path($relativePath);

            return is_file($fullPath) ? $fullPath : null;
        }

        return null;
    }
}
