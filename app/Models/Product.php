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

    // Mutator to ensure price is saved as a number
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (float)$value;
    }

    // Mutator to ensure inventory quantity is saved as a number
    public function setInventoryAttribute($value)
    {
        foreach ($value as &$item) {
            $item['quantity'] = (int)$item['quantity'];
        }
        $this->attributes['inventory'] = json_encode($value);
    }
}
