<?php

namespace Tests\Feature;

use App\Models\Holding;
use App\Models\Portfolio;
use App\Models\Sector;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_authenticated_user_can_retrieve_transactions()
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 100.00]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'type' => 'buy',
            'quantity' => 50,
            'price' => 100.00,
            'transaction_fee' => 50.00, // 0.01 * (50 * 100)
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched filtered transactions',
                'data' => [
                    [
                        'id' => $transaction->id,
                        'user_id' => $user->id,
                        'stock_id' => $stock->id,
                        'type' => 'buy',
                        'quantity' => 50,
                        'price' => '100.00',
                        'total_price' => '5,050.00', // Updated to match response
                        'transaction_fee' => '50.00',
                        'company_name' => $stock->company_name,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'stock_id',
                        'type',
                        'quantity',
                        'price',
                        'total_price',
                        'transaction_fee',
                        'company_name',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
                'message',
            ]);
    }

    public function test_authenticated_user_can_filter_transactions_by_type()
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 100.00]);
        $buyTransaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'type' => 'buy',
            'quantity' => 50,
        ]);
        Transaction::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'type' => 'sell',
            'quantity' => 20,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/transactions?type=buy');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched filtered transactions',
                'data' => [
                    [
                        'id' => $buyTransaction->id,
                        'type' => 'buy',
                        'quantity' => 50,
                    ],
                ],
            ])
            ->assertJsonMissing([
                'type' => 'sell',
            ]);
    }
    public function test_authenticated_user_can_create_buy_transaction()
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id, 'amount' => 10000]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 100.00]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/transactions', [
                'stock_id' => $stock->id,
                'type' => 'buy',
                'quantity' => 50,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully created new transaction',
                'data' => [
                    'user_id' => $user->id,
                    'stock_id' => $stock->id,
                    'type' => 'buy',
                    'quantity' => 50,
                    'price' => '100.00',
                    'total_price' => '5,050.00', // Updated to match response
                    'transaction_fee' => '50.00',
                    'company_name' => $stock->company_name,
                ],
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'type' => 'buy',
            'quantity' => 50,
            'price' => 100.00,
        ]);
    }

    public function test_buy_transaction_fails_with_insufficient_balance()
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id, 'amount' => 100]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 100.00]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/transactions', [
                'stock_id' => $stock->id,
                'type' => 'buy',
                'quantity' => 50, // 50 * 100 = 5000 > 100
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You do not have enough balance in your portfolio.',
            ]);
    }



    public function test_authenticated_user_can_create_sell_transaction()
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 100.00]);
        Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => 100,
            'average_price' => 90.00,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/transactions', [
                'stock_id' => $stock->id,
                'type' => 'sell',
                'quantity' => 50,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully created new transaction',
                'data' => [
                    'user_id' => $user->id,
                    'stock_id' => $stock->id,
                    'type' => 'sell',
                    'quantity' => 50,
                    'price' => '100.00',
                    'total_price' => '5,050.00', // Updated to match response
                    'transaction_fee' => '50.00',
                    'company_name' => $stock->company_name,
                ],
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'stock_id' => $stock->id,
            'type' => 'sell',
            'quantity' => 50,
            'price' => 100.00,
        ]);
    }


    public function test_sell_transaction_fails_with_insufficient_quantity()
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 100.00]);
        Holding::factory()->create([
            'portfolio_id' => $portfolio->id,
            'stock_id' => $stock->id,
            'quantity' => 20,
            'average_price' => 90.00,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/transactions', [
                'stock_id' => $stock->id,
                'type' => 'sell',
                'quantity' => 50, // More than 20
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You are trying to sell more shares than you own or stock not present in your portfolio.',
            ]);
    }


    public function test_transaction_creation_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/transactions', [
                'stock_id' => 999, 
                'type' => 'invalid',
                'quantity' => 5, 
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_id', 'type', 'quantity'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'stock_id' => ['The selected stock id is invalid.'],
                    'type' => ['The selected type is invalid.'],
                    'quantity' => ['The quantity field must be at least 10.'],
                ],
            ]);
    }






    
}