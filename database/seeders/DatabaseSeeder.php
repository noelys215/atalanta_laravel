<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => 'password',
                'is_admin' => false,
            ]
        );
    }
}
