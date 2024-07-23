<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Product extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'category',
        'department',
        'brand',
        'color',
        'description',
        'slug',
        'image',
        'inventory',
    ];

    protected $casts = [
        'image' => 'array',
        'inventory' => 'array',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
