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

    public function test_authenticated_user_can_update_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/profile', [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone_number' => '+12345678901',
                'bio' => 'Updated bio text',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully updated profile',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '+12345678901',
            'bio' => 'Updated bio text',
        ]);
    }

    
    public function test_authenticated_user_cannot_update_another_user_profile()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user2, 'api')
            ->postJson('/api/v1/profile', [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully updated profile',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user1->id,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }


    public function test_authenticated_user_can_update_profile_image()
    {
        $user = User::factory()->create(['profile_image' => 'profile_images/old_image.jpg']);
        Storage::disk('public')->put('profile_images/old_image.jpg', 'old content');

        $newImage = UploadedFile::fake()->image('new_image.jpg');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/profile', [
                'profile_image' => $newImage,
                'name' => $user->name,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully updated profile',
            ]);

        $user->refresh();
        $this->assertStringContainsString('profile_images/', $user->profile_image);
        $this->assertStringNotContainsString('old_image.jpg', $user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
        Storage::disk('public')->assertMissing('profile_images/old_image.jpg');
    }


    public function test_update_profile_with_invalid_data_fails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/profile', [
                'name' => 'Jo', // Too short
                'email' => 'invalid-email', // Invalid format
                'phone_number' => '123', // Invalid format
                'bio' => str_repeat('a', 501), // Too long
                'profile_image' => UploadedFile::fake()->create('document.pdf'), // Invalid mime
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone_number', 'bio', 'profile_image'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['The name field must be at least 3 characters.'],
                    'email' => ['The email field must be a valid email address.'],
                    'phone_number' => ['The phone number field format is invalid.'],
                    'bio' => ['The bio field must not be greater than 500 characters.'],
                    'profile_image' => [
                        'The profile image field must be an image.',
                        'The profile image field must be a file of type: jpg, jpeg, png.',
                    ],
                ],
            ]);
    }



    

}