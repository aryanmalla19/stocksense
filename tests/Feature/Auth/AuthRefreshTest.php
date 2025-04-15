<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_user_can_refresh_token_successfully()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Password@123'),
            'refresh_token' => 'valid-refresh-token',
            'refresh_token_expires_at' => Carbon::now()->addDays(1),
        ]);

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'valid-refresh-token',
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
        $this->assertNotEquals('valid-refresh-token', $response->json('refresh_token'));
    }

    public function test_refresh_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'refresh_token' => 'valid-refresh-token',
            'refresh_token_expires_at' => Carbon::now()->addDays(1),
        ]);

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'invalid-refresh-token',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'error' => 'Invalid or expired refresh token',
                 ]);
    }

    public function test_refresh_fails_with_expired_token()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'refresh_token' => 'expired-refresh-token',
            'refresh_token_expires_at' => Carbon::now()->subDay(),
        ]);

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'expired-refresh-token',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'error' => 'Invalid or expired refresh token',
                 ]);
    }

    public function test_refresh_fails_validation_with_missing_token()
    {
        $response = $this->postJson('/api/v1/auth/refresh', []);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors' => ['refresh_token'],
                 ]);
    }
}