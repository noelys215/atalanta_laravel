<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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
        'slug',
    ];

    protected $casts = [
        'inventory' => 'array',
        'image' => 'array',
    ];
}
