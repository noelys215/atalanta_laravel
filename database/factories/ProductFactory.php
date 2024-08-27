<?php


namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'category' => $this->faker->word,
            'department' => $this->faker->word,
            'brand' => $this->faker->word,
            'color' => $this->faker->safeColorName,
            'description' => $this->faker->sentence,
            'inventory' => [
                ['size' => 'M', 'quantity' => $this->faker->numberBetween(1, 100)],
                ['size' => 'L', 'quantity' => $this->faker->numberBetween(1, 100)],
            ],
            'image' => [$this->faker->imageUrl()],
            'slug' => Str::slug($this->faker->word),
        ];
    }
}
