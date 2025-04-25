<?php

namespace Tests\Feature;

use App\Models\Holding;
use App\Models\Portfolio;
use App\Models\Sector;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoldingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_authenticated_user_can_view_holdings()
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        $holding = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => 10,
            'average_price' => 250.05, // 10 * 250.05 = 2500.50
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/users/holdings');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched user holdings',
                'data' => [
                    [
                        'stock_id' => $stock->id,
                        'quantity' => 10,
                        'average_price' => '250.05', // String due to decimal:2 cast
                        'stock' => [
                            'id' => $stock->id,
                            'symbol' => $stock->symbol,
                            'company_name' => $stock->company_name,
                        ],
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_authenticated_user_can_view_specific_holding()
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        $holding = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => 20,
            'average_price' => 225.00, // 20 * 225.00 = 4500.00
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/users/holdings/{$holding->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Holding details fetched successfully',
                'data' => [
                    'stock_id' => $stock->id,
                    'quantity' => 20,
                    'average_price' => '225.00', // String due to decimal:2 cast
                    'stock' => [
                        'id' => $stock->id,
                        'symbol' => $stock->symbol,
                        'company_name' => $stock->company_name,
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_unauthenticated_user_cannot_access_holdings()
    {
        $response = $this->getJson('/api/v1/users/holdings');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/users/holdings/1');
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_user_with_no_portfolio_cannot_view_holdings()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/users/holdings');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched user holdings',
                'data' => [],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_user_cannot_view_another_users_holding()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user1->id]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        $holding = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => 10,
            'average_price' => 250.05,
        ]);

        $response = $this->actingAs($user2, 'api')
            ->getJson("/api/v1/users/holdings/{$holding->id}");

        $response->assertStatus(403); // Policy denies access to other user's holding
    }
}
