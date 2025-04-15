<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_user_can_verify_2fa_otp()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'two_factor_enabled' => true,
            'two_factor_otp' => '123456',
            'two_factor_secret' => Str::random(32),
            'two_factor_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'john@example.com',
            'otp' => '123456',
            'private_token' => $user->two_factor_secret,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'refresh_token',
                     'token_type',
                     'expires_in',
                 ]);

        $user = $user->fresh();
        $this->assertNull($user->two_factor_otp);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_expires_at);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'refresh_token' => $response->json('refresh_token'),
        ]);
    }

    public function test_2fa_verification_fails_with_invalid_otp()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'two_factor_enabled' => true,
            'two_factor_otp' => '123456',
            'two_factor_secret' => Str::random(32),
            'two_factor_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'john@example.com',
            'otp' => '999999',
            'private_token' => $user->two_factor_secret,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Invalid OTP']);
    }

    public function test_2fa_verification_fails_with_expired_otp()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'two_factor_enabled' => true,
            'two_factor_otp' => '123456',
            'two_factor_secret' => Str::random(32),
            'two_factor_expires_at' => Carbon::now()->subMinute(),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'john@example.com',
            'otp' => '123456',
            'private_token' => $user->two_factor_secret,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'OTP expired']);
    }

    public function test_2fa_verification_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'two_factor_enabled' => true,
            'two_factor_otp' => '123456',
            'two_factor_secret' => Str::random(32),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'john@example.com',
            'otp' => '123456',
            'private_token' => str_repeat('x', 32), // <-- must be 32 chars
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'User not found or invalid token']);
    }

    public function test_authenticated_user_can_enable_2fa()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'two_factor_enabled' => false,
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/v1/auth/2fa/enable');

        $response->assertStatus(200)
                 ->assertJson(['message' => '2FA enabled successfully']);

        $this->assertTrue($user->fresh()->two_factor_enabled);
    }

    public function test_authenticated_user_can_disable_2fa()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'two_factor_enabled' => true,
            'two_factor_otp' => '123456',
            'two_factor_secret' => Str::random(32),
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/v1/auth/2fa/disable');

        $response->assertStatus(200)
                 ->assertJson(['message' => '2FA disabled successfully']);

        $user = $user->fresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_otp);
        $this->assertNull($user->two_factor_secret);
    }

    public function test_2fa_enable_requires_authentication()
    {
        $response = $this->postJson('/api/v1/auth/2fa/enable');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']); // <-- match actual response
    }
}
