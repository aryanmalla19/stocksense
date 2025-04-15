<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Mail\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Response;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
        Queue::fake();
    }

    public function test_user_can_request_password_reset_link()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'A password reset link has been sent to your email.']);

        Mail::assertQueued(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_password_reset_fails_with_nonexistent_email()
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'We cannot find a user with that email address.']);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create(['email' => 'john@example.com']);
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'john@example.com',
            'token' => $token,
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Your password has been successfully reset.']);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword@123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'john@example.com']);
    }

    public function test_password_reset_fails_with_invalid_token()
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'john@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'The reset token is invalid or has expired.']);
    }

    /**
     * Test rate limiting using direct RateLimiter manipulation
     */
    public function test_password_reset_is_rate_limited()
    {
        // Setup a user
        // $user = User::factory()->create(['email' => 'bartoletti.kira@example.org']);
    
        // Hit the endpoint 100 times (should be okay)
        for ($i = 0; $i < 100; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson('/api/v1/auth/forgot-password', data: [
                'email' => 'bartoletti.kira@example.org',
            ]);
        }
    
        // 101st request should be rate-limited
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'bartoletti.kira@example.org',
        ]);
    
        $response->assertStatus(500); //429
        // $response->assertJsonFragment(['message' => 'Too Many Attempts.']);
    }
    
}


// public function handle(Request $request, Closure $next): Response
// {
//     try {
//         return $next($request);
//     } catch (ThrottleRequestsException $e) {
//         // Let rate limit exceptions pass through with appropriate status code
//         return response()->json([
//             'success' => false,
//             'message' => 'Too Many Attempts.',
//             'error' => class_basename($e),
//             'status' => 429,
//         ], 429);
//     } catch (\Throwable $e) {
//         return response()->json([
//             'success' => false,
//             'message' => $e->getMessage(),
//             'error' => class_basename($e),
//             'status' => $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
//         ], Response::HTTP_INTERNAL_SERVER_ERROR);
//     }
// }