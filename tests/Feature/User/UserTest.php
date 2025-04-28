<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
        Storage::fake('public');
    }

    public function test_authenticated_user_can_retrieve_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'bio',
                    'profile_image',
                    'is_active',
                    'role',
                    'two_factor_enabled',
                    'theme',
                    'notification_enabled',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'bio' => $user->bio,
                ],
            ]);
    }


    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/profile');
        $response->assertStatus(401);
    }
}    