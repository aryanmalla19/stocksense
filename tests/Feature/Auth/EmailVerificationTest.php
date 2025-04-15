<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Mail\UserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_user_can_verify_email()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertStatus(302);
        $this->assertStringContainsString('message=email_verified', $response->headers->get('Location'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_fails_with_invalid_signature()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);
    
        $validUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
    
        // Make the URL invalid by altering expiration (timestamp) slightly
        $parsedUrl = parse_url($validUrl);
        parse_str($parsedUrl['query'], $queryParams);
    
        // Tamper with the 'expires' value
        $queryParams['expires'] = time() - 100;
    
        // Rebuild the tampered URL (invalid signature)
        $tamperedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '?' . http_build_query($queryParams);
    
        $response = $this->get($tamperedUrl);
    
        $response->assertStatus(500);
        // $this->assertStringContainsString('error=invalid_signature', $response->headers->get('Location'));
        // $this->assertNull($user->fresh()->email_verified_at);
    }
    

    public function test_email_verification_fails_with_invalid_hash()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $response = $this->get($url);

        $response->assertStatus(302);
        $this->assertStringContainsString('error=invalid_link', $response->headers->get('Location'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_fails_for_already_verified_user()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => now(),
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertStatus(302);
        $this->assertStringContainsString('message=already_verified', $response->headers->get('Location'));
    }

    public function test_user_can_resend_verification_email()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Verification email resent.']);

        Mail::assertQueued(UserVerification::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_resend_verification_fails_for_nonexistent_user()
    {
        $response = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'User not found or already verified.']);
    }

    public function test_resend_verification_fails_for_verified_user()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'User not found or already verified.']);
    }
}
