<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HeroCard extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'video_src', 'image_src', 'upload_image'
    ];

    // Automatically generate slug from title
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }
}

