<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'price',
        'category',
        'department',
        'brand',
        'color',
        'description',
        'inventory',
        'image',
        'image_path',
        'slug',
        'external_source',
        'external_id',
    ];

    protected $casts = [
        'inventory' => 'array',
        'image' => 'array',
    ];

    // Mutator to ensure price is saved as a number
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (float)$value;
    }

    // Mutator to ensure inventory quantity is saved as a number
    public function setInventoryAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        if (! is_array($value)) {
            $value = [];
        }

        foreach ($value as &$item) {
            if (is_array($item) && array_key_exists('quantity', $item)) {
                $item['quantity'] = (int) $item['quantity'];
            }
        }

        $this->attributes['inventory'] = json_encode($value);
    }

    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name', config('filesystems.default'));

        $this->addMediaCollection('product_images')
            ->useDisk($disk);
    }

    public function getImageAttribute($value)
    {
        $mediaUrls = $this->getMedia('product_images')
            ->map(fn ($media) => $media->getUrl())
            ->all();

        if (count($mediaUrls) > 0) {
            return $mediaUrls;
        }

        if (is_array($value)) {
            return $value;
        }

        return $value ? json_decode($value, true) : [];
    }
}
