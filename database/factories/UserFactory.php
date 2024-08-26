<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified' => false,
            'email_verification_token' => Str::random(60),
            'password' => bcrypt('password'), // default password
            'telephone' => $this->faker->phoneNumber,
            'country' => $this->faker->country,
            'address' => $this->faker->address,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
        ];
    }
}
