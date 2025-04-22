<?php

namespace Tests\Feature\Portfolio;

use App\Events\UserRegistered;
use App\Listeners\CreateUserPortfolio;
use App\Models\Holding;
use App\Models\Portfolio;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_portfolio()
    {
    // Create a user
    $user = User::factory()->create();

    // Create a portfolio manually for specific amount
    $portfolio = new Portfolio();
    $portfolio->user_id = $user->id;
    $portfolio->amount = 50000.75;
    $portfolio->save();

    // Create a holding with a stock and latest price
    $stock = Stock::factory()->create();
    $holding = Holding::factory()->create([
        'portfolio_id' => $portfolio->id,
        'stock_id' => $stock->id,
        'quantity' => 100,
        'average_price' => 25.01, // Adjusted to 2 decimal places
    ]);
    $stock->latestPrice()->create([
        'current_price' => 26.00,
    ]);

    $response = $this->actingAs($user, 'api')
                    ->getJson('/api/v1/portfolios');

    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'Successfully fetched all portfolios data',
                 'data' => [
                     'id' => $portfolio->id,
                     'amount' => 50000.75,
                     'investment' => 2501.00, // 100 * 25.01
                     'net_worth' => 2600.00,  // 100 * 26.00
                     'gain_loss' => 99.00,    // 2600.00 - 2501.00
                 ],
             ]);
    }
    public function test_unauthenticated_user_cannot_view_portfolio()
    {
        $response = $this->getJson('/api/v1/portfolios');

        $response->assertStatus(401);
    }

    public function test_new_user_has_default_portfolio()
    {
    // Simulate user registration via API
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(201);

    // Get the created user
    $user = User::where('email', 'test@example.com')->first();

    // // Debug: Check if portfolio was created
    // $portfolio = \App\Models\Portfolio::where('user_id', $user->id)->first();
    // dump($portfolio ? $portfolio->toArray() : 'No portfolio created');

    // Assert the portfolio exists in the database
    $this->assertDatabaseHas('portfolios', [
        'user_id' => $user->id,
        'amount' => 100000.00,
    ]);

    // Assert the portfolio was created
    $response = $this->actingAs($user, 'api')
                    ->getJson('/api/v1/portfolios');

    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'Successfully fetched all portfolios data',
                 'data' => [
                     'amount' => 100000.00,
                     'investment' => 0.00,
                     'net_worth' => 0.00,
                     'gain_loss' => 0.00,
                 ],
             ]);
    }   
    public function test_user_with_multiple_holdings_in_portfolio()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a portfolio manually for specific amount
        $portfolio = new Portfolio();
        $portfolio->user_id = $user->id;
        $portfolio->amount = 75000.00;
        $portfolio->save();

        // Create two holdings with stocks and latest prices
        $stock1 = Stock::factory()->create();
        $holding1 = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock1->id,
            'quantity' => 100,
            'average_price' => 50.00,
        ]);
        $stock1->latestPrice()->create([
            'current_price' => 52.50,
        ]);

        $stock2 = Stock::factory()->create();
        $holding2 = Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock2->id,
            'quantity' => 50,
            'average_price' => 50.00,
        ]);
        $stock2->latestPrice()->create([
            'current_price' => 50.00,
        ]);

        $response = $this->actingAs($user, 'api')
                        ->getJson('/api/v1/portfolios');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully fetched all portfolios data',
                     'data' => [
                         'id' => $portfolio->id,
                         'amount' => 75000.00,
                         'investment' => 7500.00, // (100 * 50.00) + (50 * 50.00)
                         'net_worth' => 7750.00,  // (100 * 52.50) + (50 * 50.00)
                         'gain_loss' => 250.00,   // 7750.00 - 7500.00
                     ],
                 ]);
    }
}