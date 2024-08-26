<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function it_returns_error_for_invalid_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid email or password']);
    }

    #[Test]
    public function it_fails_registration_for_existing_email()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/pre-register', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Email is taken']);
    }


    #[Test]
    public function it_can_reset_password()
    {
        $user = User::factory()->create([
            'password_reset_token' => Str::random(60),
        ]);

        $response = $this->postJson('/api/reset-password', [
            'token' => $user->password_reset_token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Password has been reset successfully.']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
