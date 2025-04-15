<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_user_can_login_successfully_without_2fa()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('Password@123'),
            'email_verified_at' => now(),
            'two_factor_enabled' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'expires_in',
                     'refresh_token',
                 ])
                 ->assertJson([
                     'token_type' => 'bearer',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'refresh_token' => $response->json('refresh_token'),
            'refresh_token_expires_at' => Carbon::now()->addDays(30)->toDateTimeString(),
        ]);
    }

    public function test_login_prompts_2fa_when_enabled()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('Password@123'),
            'email_verified_at' => now(),
            'two_factor_enabled' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(202)
                 ->assertJsonStructure([
                     'message',
                     'private_token',
                     'otp_length',
                     'expires_in',
                 ])
                 ->assertJson([
                     'message' => 'OTP required for 2FA authentication.',
                     'otp_length' => 6,
                     'expires_in' => 300,
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'two_factor_secret' => $response->json('private_token'),
            'two_factor_expires_at' => Carbon::now()->addMinutes(5)->toDateTimeString(),
        ]);
    }

    public function test_login_fails_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Password@123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPass@123', // Matches validation rules but not the user's password
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'error' => 'Invalid password',
                 ]);
    }

    public function test_login_fails_with_nonexistent_email()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'error' => 'Email does not exist',
                 ]);
    }

    public function test_login_fails_with_unverified_email()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Password@123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'error' => 'Please verify your email before logging in.',
                 ]);
    }

    public function test_login_fails_validation_with_missing_fields()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors' => ['email', 'password'],
                 ])
                 ->assertJson([
                     'message' => 'Email is missing (and 1 more error)',
                 ]);
    }
}