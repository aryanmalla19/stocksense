<?php

namespace Tests\Feature;

use App\Enums\IpoApplicationStatus;
use App\Models\IpoApplication;
use App\Models\IpoDetail;
use App\Models\Portfolio;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_admin_can_retrieve_paginated_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(20)->create();

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'is_active',
                        'phone_number',
                        'bio',
                        'profile_image',
                        'two_factor_enabled',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(15, 'data'); 
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $routes = [
            '/api/v1/admin/users',
            '/api/v1/admin/users/1',
            '/api/v1/admin/ipo-details/1/applications',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user, 'api')
                ->getJson($route);

            $response->assertStatus(403);
        }
    }

}    