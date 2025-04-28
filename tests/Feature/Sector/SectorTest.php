<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_authenticated_user_can_retrieve_all_sectors()
    {
        $user = User::factory()->create();

        $sectorNames = [
            'Banking', 'Hydropower', 'Life Insurance', 'Non-life Insurance',
            'Health', 'Manufacturing', 'Hotel', 'Trading',
            'Microfinance', 'Finance', 'Investment', 'Others',
        ];

        foreach ($sectorNames as $name) {
            Sector::factory()->create(['name' => $name]);
        }

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/sectors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);

        $responseData = $response->json('data');
        $expectedData = array_map(function ($name, $index) {
            return [
                'id' => $index + 1,
                'name' => $name,
            ];
        }, $sectorNames, array_keys($sectorNames));

        $this->assertEquals($expectedData, $responseData);
    }
    public function test_unauthenticated_user_cannot_retrieve_sectors()
    {
        $response = $this->getJson('/api/v1/sectors');
        $response->assertStatus(401);
    }
    public function test_admin_user_can_create_sector()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/sectors', [
                'name' => 'Banking',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully created sector',
                'data' => [
                    'name' => 'Banking',
                ],
            ]);

        $this->assertDatabaseHas('sectors', [
            'name' => 'Banking',
        ]);
    }

    public function test_create_sector_fails_with_invalid_name()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/sectors', [
                'name' => 'invalid-sector',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['Sector name must be in Predefined values'],
                ],
            ]);
    }

    public function test_create_sector_fails_with_missing_name()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/sectors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['Sector name is required'],
                ],
            ]);
    }

    public function test_create_sector_fails_with_duplicate_name()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sector::factory()->create(['name' => 'Banking']);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/sectors', [
                'name' => 'Banking',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['Sector name must be unique'],
                ],
            ]);
    }

    
}