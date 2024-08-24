<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeasonalCard extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'video_src',
        'image_src',
        'upload_image',
        'link_title',
        'link'
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
