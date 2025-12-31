<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatziProductImporter
{
    private const MAX_PRICE = 999999.99;

    public function import(
        int $limit,
        int $page = 1,
        bool $downloadImages = false,
        ?Command $command = null
    ): array {
        $baseUrl = rtrim(config('seed_sources.platzi.base_url'), '/');
        $perPage = (int) config('seed_sources.platzi.per_page', 25);
        $totalLimit = max(1, $limit);
        $offset = max(0, ($page - 1) * $perPage);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;

        while ($processed < $totalLimit) {
            $batchLimit = min($perPage, $totalLimit - $processed);
            $url = $baseUrl . '/products?offset=' . $offset . '&limit=' . $batchLimit;

            $response = Http::retry(3, 250)
                ->timeout(15)
                ->get($url);

            if (! $response->successful()) {
                $this->log($command, "Platzi request failed: {$response->status()} {$url}");
                break;
            }

            $items = $response->json();
            if (! is_array($items) || count($items) === 0) {
                $this->log($command, 'No more Platzi products to import.');
                break;
            }

            foreach ($items as $item) {
                if ($processed >= $totalLimit) {
                    break;
                }

                $externalId = data_get($item, 'id');
                $name = data_get($item, 'title');

                if (! $externalId || ! $name) {
                    $skipped++;
                    $processed++;
                    continue;
                }

                $images = data_get($item, 'images', []);
                $categoryName = data_get($item, 'category.name') ?? data_get($item, 'category');

                $price = (float) data_get($item, 'price', 0);
                $price = max(0, min(self::MAX_PRICE, $price));

                $payload = [
                    'name' => $name,
                    'description' => data_get($item, 'description'),
                    'price' => $price,
                    'department' => config('seed_sources.platzi.defaults.department', 'Essentials'),
                    'brand' => config('seed_sources.platzi.defaults.brand', 'Platzi'),
                    'color' => config('seed_sources.platzi.defaults.color', 'multicolor'),
                    'inventory' => config('seed_sources.platzi.defaults.inventory', []),
                    'external_source' => 'platzi',
                    'external_id' => (string) $externalId,
                ];

                if ($categoryName && Schema::hasColumn('products', 'category')) {
                    $payload['category'] = $categoryName;
                }

                $existing = Product::where('external_source', 'platzi')
                    ->where('external_id', (string) $externalId)
                    ->first();

                $payload['slug'] = $this->makeUniqueSlug(
                    Str::slug($name),
                    $existing?->id
                );

                $imageUrls = $this->normalizeImageUrls($images);
                $payload['image'] = $imageUrls;
                $payload['image_path'] = null;

                $this->attachCategory($categoryName, $payload);

                $product = Product::updateOrCreate(
                    [
                        'external_source' => 'platzi',
                        'external_id' => (string) $externalId,
                    ],
                    $payload
                );

                if ($downloadImages && count($imageUrls) > 0) {
                    $this->syncMediaFromUrls($product, $imageUrls);
                    $payload['image'] = $product->getMedia('product_images')
                        ->map(fn ($media) => $media->getUrl())
                        ->all();
                    $product->forceFill(['image' => $payload['image'], 'image_path' => null])->save();
                }

                if ($product->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }

                $processed++;
            }

            if (count($items) < $batchLimit) {
                break;
            }

            $offset += $batchLimit;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    private function normalizeImageUrls(array $images): array
    {
        return array_values(array_filter(
            $images,
            fn ($value) => is_string($value) && $value !== ''
        ));
    }

    private function syncMediaFromUrls(Product $product, array $imageUrls): void
    {
        $product->clearMediaCollection('product_images');

        foreach ($imageUrls as $imageUrl) {
            try {
                $product->addMediaFromUrl($imageUrl)
                    ->toMediaCollection('product_images');
            } catch (\Throwable $exception) {
                Log::warning("Failed to attach Platzi image: {$imageUrl} ({$exception->getMessage()})");
            }
        }
    }

    private function makeUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Product::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function attachCategory(?string $categoryName, array &$payload): void
    {
        if (! $categoryName) {
            return;
        }

        if (! Schema::hasTable('categories') || ! Schema::hasColumn('products', 'category_id')) {
            return;
        }

        if (! class_exists('App\\Models\\Category')) {
            $categoryId = DB::table('categories')->where('name', $categoryName)->value('id');

            if (! $categoryId) {
                $categoryId = DB::table('categories')->insertGetId(['name' => $categoryName]);
            }

            $payload['category_id'] = $categoryId;
            return;
        }

        $categoryClass = 'App\\Models\\Category';
        $category = $categoryClass::firstOrCreate(['name' => $categoryName]);
        $payload['category_id'] = $category->id;
    }

    private function log(?Command $command, string $message): void
    {
        if ($command) {
            $command->info($message);
        } else {
            Log::info($message);
        }
    }
}
