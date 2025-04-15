<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthLogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_user_can_logout_successfully()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'refresh_token' => 'some-refresh-token',
        ]);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully logged out',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'refresh_token' => null,
            'refresh_token_expires_at' => null,
        ]);

        // Verify token is invalidated (optional, requires JWT blacklist support)
        $this->assertFalse(JWTAuth::setToken($token)->check());
    }

    public function test_logout_fails_when_unauthenticated()
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }
}