<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockPriceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create and authenticate a user for API requests
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function it_can_fetch_historical_stock_prices()
    {
        // Arrange: Create a stock with multiple prices
        $stock = Stock::factory()->create();
        StockPrice::factory()->count(5)->create(['stock_id' => $stock->id]);

        // Act: GET request to history endpoint
        $response = $this->getJson("/api/v1/stocks/{$stock->id}/history");

        // Assert: Verify response structure and data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'symbol',
                    'company_name',
                    'sector_id',
                    'is_listed',
                    'sector',
                    'is_watchlist',
                    'prices' => [
                        '*' => [
                            'id',
                            'stock_id',
                            'open_price',
                            'close_price',
                            'high_price',
                            'low_price',
                            'current_price',
                            'volume',
                            'date',
                        ],
                    ],
                    'open_price',
                    'close_price',
                    'high_price',
                    'low_price',
                    'current_price',
                ],
            ]);

        $this->assertCount(5, $response->json('data.prices'));
    }

   
}