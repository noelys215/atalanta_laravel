<?php

namespace Database\Factories;

use App\Models\SeasonalCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SeasonalCardFactory extends Factory
{
    protected $model = SeasonalCard::class;

    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->word),
        ];
    }
}
