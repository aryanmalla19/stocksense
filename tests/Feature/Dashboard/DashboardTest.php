<?php

namespace Tests\Feature;

use App\Models\Holding;
use App\Models\Portfolio;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();
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
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'type' => 'buy', // Use lowercase
            'price' => 1500.25,
            'quantity' => 5,
            'transaction_fee' => 0.00,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'current_amount' => 50000.75,
                'total_investment' => 1500.25,
                'current_holdings' => 2500.50, // 10 * 250.05
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/v1/dashboard');
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_user_with_no_portfolio_returns_zeros()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'current_amount' => 0,
                'total_investment' => 0,
                'current_holdings' => 0,
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_user_with_multiple_transactions_and_holdings()
    {
        $user = User::factory()->create();
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
        $transaction1 = Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock1->id,
            'type' => 'buy', // Use lowercase
            'price' => 2000.00,
            'quantity' => 10,
            'transaction_fee' => 0.00,
        ]);
        $transaction2 = Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock2->id,
            'type' => 'buy', // Use lowercase
            'price' => 3000.00,
            'quantity' => 15,
            'transaction_fee' => 0.00,
        ]);
        $transaction3 = Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock2->id,
            'type' => 'sell', // Use lowercase
            'price' => 1000.00,
            'quantity' => 5,
            'transaction_fee' => 0.00,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'current_amount' => 75000.00,
                'total_investment' => 5000.00, // 2000 + 3000
                'current_holdings' => 7500.00, // 3000 + 4500
            ]);
    }
}
