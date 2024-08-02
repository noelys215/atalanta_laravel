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

    public function setInventoryAttribute($value)
    {
        foreach ($value as &$item) {
            $item['quantity'] = (int)$item['quantity'];
        }
        $this->attributes['inventory'] = json_encode($value);
    }
}
