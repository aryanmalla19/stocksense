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

    public function test_authenticated_user_can_retrieve_specific_sector()
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create([
            'name' => 'Finance',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/sectors/{$sector->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $sector->id,
                    'name' => 'Finance',
                    'total_no_of_stocks' => 0,
                    'total_price' => 0,
                    'average_price' => null,
                ],
            ]);
    }

    public function test_retrieve_non_existent_sector_fails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/sectors/999');

        $response->assertStatus(404);
    }

    public function test_update_sector_fails_with_invalid_name()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sector = Sector::factory()->create([
            'name' => 'Trading',
        ]);

        $response = $this->actingAs($admin, 'api')
            ->putJson("/api/v1/sectors/{$sector->id}", [
                'name' => 'invalid-sector',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['The sector name must be one of the predefined values.'],
                ],
            ]);
    }

    public function test_update_sector_fails_with_duplicate_name()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sector::factory()->create(['name' => 'Investment']);
        $sector = Sector::factory()->create(['name' => 'Microfinance']);

        $response = $this->actingAs($admin, 'api')
            ->putJson("/api/v1/sectors/{$sector->id}", [
                'name' => 'Investment',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['The sector name must be unique.'],
                ],
            ]);
    }

    
}