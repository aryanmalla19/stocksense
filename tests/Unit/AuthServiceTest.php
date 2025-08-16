<?php

namespace Tests\Unit;

use App\Events\UserRegistered;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_creates_user_successfully()
    {
        $user = Mockery::mock('overload:'.User::class);
        $user->shouldReceive('save')->once()->andReturn(true);
        $user->name = 'John Doe';
        $user->email = 'john@example.com';

        $this->mock('events', function ($mock) {
            $mock->shouldReceive('dispatch')->withArgs(function ($event) {
                return $event instanceof UserRegistered;
            })->once();
        });

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ];

        $result = $this->authService->register($data);

        $this->assertEquals(201, $result['status']);
        $this->assertEquals('User registered successfully. Check email for verification', $result['message']);
        $this->assertEquals(['name' => 'John Doe', 'email' => 'john@example.com'], $result['user']);
    }

    public function test_register_fails_when_save_fails()
    {
        $user = Mockery::mock('overload:'.User::class);
        $user->shouldReceive('save')->once()->andReturn(false);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ];

        $result = $this->authService->register($data);

        $this->assertEquals(500, $result['status']);
        $this->assertEquals('Error registering user', $result['error']);
    }

    public function test_login_succeeds_with_valid_credentials()
    {
        $userMock = Mockery::mock('alias:'.User::class);
        $userMock->shouldReceive('where')->with('email', 'john@example.com')->andReturnSelf();
        $userMock->shouldReceive('first')->andReturnSelf();
        $userMock->shouldReceive('hasVerifiedEmail')->andReturn(true);
        $userMock->shouldReceive('forceFill')->once()->andReturnSelf();
        $userMock->shouldReceive('save')->once()->andReturn(true);
        $userMock->shouldReceive('notify')->once()->andReturn(null);
        $userMock->password = Hash::make('Password@123');
        $userMock->two_factor_enabled = false;

        JWTAuth::shouldReceive('attempt')->once()->with([
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ])->andReturn('jwt-token-here');

        Notification::fake();

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ];

        $result = $this->authService->login($credentials);

        $this->assertEquals(200, $result['status']);
        $this->assertEquals('jwt-token-here', $result['access_token']);
        $this->assertEquals('bearer', $result['token_type']);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertEquals(32, strlen($result['refresh_token']));
    }

    public function test_login_fails_with_invalid_password()
    {
        $userMock = Mockery::mock('alias:'.User::class);
        $userMock->shouldReceive('where')->with('email', 'john@example.com')->andReturnSelf();
        $userMock->shouldReceive('first')->andReturnSelf();
        $userMock->password = Hash::make('Password@123');

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ];

        $result = $this->authService->login($credentials);

        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Invalid password', $result['error']);
    }

    public function test_login_fails_with_nonexistent_email()
    {
        $userMock = Mockery::mock('alias:'.User::class);
        $userMock->shouldReceive('where')->with('email', 'john@example.com')->andReturnSelf();
        $userMock->shouldReceive('first')->andReturn(null);

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ];

        $result = $this->authService->login($credentials);

        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Email does not exist', $result['error']);
    }

    public function test_login_fails_with_unverified_email()
    {
        $userMock = Mockery::mock('alias:'.User::class);
        $userMock->shouldReceive('where')->with('email', 'john@example.com')->andReturnSelf();
        $userMock->shouldReceive('first')->andReturnSelf();
        $userMock->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $userMock->password = Hash::make('Password@123');

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ];

        $result = $this->authService->login($credentials);

        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Please verify your email before logging in.', $result['error']);
    }

    public function test_login_prompts_2fa_when_enabled()
    {
        $userMock = Mockery::mock('alias:'.User::class);
        $userMock->shouldReceive('where')->with('email', 'john@example.com')->andReturnSelf();
        $userMock->shouldReceive('first')->andReturnSelf();
        $userMock->shouldReceive('hasVerifiedEmail')->andReturn(true);
        $userMock->shouldReceive('notify')->once()->andReturn(null);
        $userMock->shouldReceive('forceFill')->once()->andReturnSelf();
        $userMock->shouldReceive('save')->once()->andReturn(true);
        $userMock->password = Hash::make('Password@123');
        $userMock->two_factor_enabled = true;
        $userMock->email = 'john@example.com'; // Fix: Define email property

        Notification::fake();
        Mail::fake();
        Mail::shouldReceive('to')->with('john@example.com')->once()->andReturnSelf();
        Mail::shouldReceive('queue')->once();

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'Password@123',
        ];

        $result = $this->authService->login($credentials);

        $this->assertEquals(202, $result['status']);
        $this->assertEquals('OTP required for 2FA authentication.', $result['message']);
        $this->assertEquals(6, $result['otp_length']);
        $this->assertEquals(300, $result['expires_in']);
        $this->assertArrayHasKey('private_token', $result);
        $this->assertEquals(32, strlen($result['private_token']));
    }
}
