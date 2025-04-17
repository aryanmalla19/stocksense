<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Portfolio;
use App\Models\Holding;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_authenticated_user_can_view_portfolio()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000.75,
        ]);
        $stock = Stock::factory()->create();
        $holding = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => 10,
            'average_price' => 250.05, // 10 * 250.05 = 2500.50
        ]);

        $response = $this->actingAs($user, 'api')
                        ->getJson('/api/v1/portfolio');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully fetched all portfolios data',
                     'data' => [
                         [
                             'id' => $portfolio->id,
                             'user_id' => $user->id,
                             'amount' => 50000.75,
                             'user' => [
                                 'id' => $user->id,
                                 'name' => 'John Doe',
                                 'email' => 'john@example.com',
                             ],
                             'holdings' => [
                                 [
                                     'id' => $holding->id,
                                     'stock_id' => $stock->id,
                                     'quantity' => 10,
                                     'average_price' => 250.05,
                                     'value' => 2500.50, // Calculated as quantity * average_price
                                 ],
                             ],
                         ],
                     ],
                 ]);
    }

    
    public function test_unauthenticated_user_cannot_view_portfolio()
    {
        $response = $this->getJson('/api/v1/portfolio');
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_user_with_no_portfolio_returns_empty_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                        ->getJson('/api/v1/portfolio');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully fetched all portfolios data',
                     'data' => [],
                 ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_user_with_multiple_holdings_in_portfolio()
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'amount' => 75000.00,
        ]);
        $stock1 = Stock::factory()->create();
        $stock2 = Stock::factory()->create();
        $holding1 = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock1->id,
            'quantity' => 15,
            'average_price' => 200.00, // 15 * 200.00 = 3000.00
        ]);
        $holding2 = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock2->id,
            'quantity' => 20,
            'average_price' => 225.00, // 20 * 225.00 = 4500.00
        ]);

        $response = $this->actingAs($user, 'api')
                        ->getJson('/api/v1/portfolio');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully fetched all portfolios data',
                     'data' => [
                         [
                             'id' => $portfolio->id,
                             'user_id' => $user->id,
                             'amount' => 75000.00,
                             'user' => [
                                 'id' => $user->id,
                                 'name' => 'Jane Doe',
                                 'email' => 'jane@example.com',
                             ],
                             'holdings' => [
                                 [
                                     'id' => $holding1->id,
                                     'stock_id' => $holding1->stock_id,
                                     'quantity' => 15,
                                     'average_price' => 200.00,
                                     'value' => 3000.00, // 15 * 200.00
                                 ],
                                 [
                                     'id' => $holding2->id,
                                     'stock_id' => $holding2->stock_id,
                                     'quantity' => 20,
                                     'average_price' => 225.00,
                                     'value' => 4500.00, // 20 * 225.00
                                 ],
                             ],
                         ],
                     ],
                 ]);
    }
}