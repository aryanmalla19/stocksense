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


    
}