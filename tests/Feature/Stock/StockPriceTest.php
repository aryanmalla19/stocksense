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
    // tests/Feature/Stock/StockPriceTest.php, in it_can_fetch_all_stocks_with_prices
    public function it_can_fetch_all_stocks_with_prices()
    {
        // Arrange: Create stocks with associated prices
        $stocks = Stock::factory()->count(3)->create()->each(function ($stock) {
            StockPrice::factory()->count(2)->create(['stock_id' => $stock->id]);
        });

        // Act: GET request to index endpoint
        $response = $this->getJson('/api/v1/stock-prices');

        // Assert: Verify response structure and data
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched all stock with its prices',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name', // Change from 'company_name' to 'name'
                        'symbol',
                        'prices' => [
                            '*' => [
                                'id',
                                'stock_id',
                                'open_price',
                                'close_price',
                                'high_price',
                                'low_price',
                                'current_price',
                                'date',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_can_store_a_new_stock_price()
    {
        $stock = Stock::factory()->create();

        $data = [
            'stock_id' => $stock->id,
            'current_price' => 150.75,
            'open_price' => 150.00,
            'close_price' => 151.00,
            'high_price' => 152.00,
            'low_price' => 149.50,
            'volume' => 100000,
            'date' => now()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/v1/stock-prices', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Successfully created new stock price',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'stock_id',
                    'open_price',
                    'close_price',
                    'high_price',
                    'low_price',
                    'current_price',
                    'date',
                ],
            ]);

        $this->assertDatabaseHas('stock_prices', [
            'stock_id' => $stock->id,
            'current_price' => 150.75,
        ]);
    }

    #[Test]
    public function it_fails_to_store_stock_price_with_invalid_stock_id()
    {
        // Arrange: Invalid stock ID
        $data = [
            'stock_id' => 999,
            'current_price' => 150.75,
        ];

        // Act: POST request
        $response = $this->postJson('/api/v1/stock-prices', $data);

        // Assert: Verify 422 response due to validation failure
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'stock_id' => ['The selected stock does not exist.'],
                ],
            ]);
    }

    #[Test]
    public function it_fails_to_store_stock_price_with_invalid_data()
    {
        // Arrange: Create a stock, provide invalid current_price
        $stock = Stock::factory()->create();
        $data = [
            'stock_id' => $stock->id,
            'current_price' => 'invalid', // Non-numeric
        ];

        // Act: POST request
        $response = $this->postJson('/api/v1/stock-prices', $data);

        // Assert: Verify 422 validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_price'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'current_price' => ['Current price must be a numeric value.'],
                ],
            ]);
    }

    #[Test]
    public function it_can_show_a_stock_price()
    {
        // Arrange: Create a stock price
        $stock = Stock::factory()->create();
        $stockPrice = StockPrice::factory()->create(['stock_id' => $stock->id]);

        // Act: GET request to show endpoint
        $response = $this->getJson("/api/v1/stock-prices/{$stockPrice->id}");

        // Assert: Verify response structure
        $response->assertStatus(200)
            ->assertJson([
                'message' => "Successfully fetched stock price data with ID {$stockPrice->id}",
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'stock_id',
                    'open_price',
                    'close_price',
                    'high_price',
                    'low_price',
                    'current_price',
                    'date',
                ],
            ]);
    }

    #[Test]
    public function it_fails_to_show_non_existent_stock_price()
    {
        // Act: GET request for non-existent stock price
        $response = $this->getJson('/api/v1/stock-prices/999');

        // Assert: Verify 404 response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Could not find stock price data with ID 999',
            ]);
    }

    #[Test]
    public function it_prevents_updating_stock_price()
    {
        // Arrange: Create a stock price
        $stockPrice = StockPrice::factory()->create();

        // Act: PUT request to update endpoint
        $response = $this->putJson("/api/v1/stock-prices/{$stockPrice->id}", [
            'current_price' => 200.00,
        ]);

        // Assert: Verify 400 response
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You cannot change stock previous price',
            ]);
    }

    #[Test]
    public function it_prevents_deleting_stock_price()
    {
        // Arrange: Create a stock price
        $stockPrice = StockPrice::factory()->create();

        // Act: DELETE request to destroy endpoint
        $response = $this->deleteJson("/api/v1/stock-prices/{$stockPrice->id}");

        // Assert: Verify 400 response
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You cannot delete stock previous price',
            ]);
    }

    #[Test]
    public function it_can_fetch_historical_stock_prices()
    {
        // Arrange: Create a stock with multiple prices
        $stock = Stock::factory()->create();
        $stockPrices = StockPrice::factory()->count(5)->create(['stock_id' => $stock->id]);

        // Act: GET request to historyStockPrices endpoint
        $response = $this->getJson("/api/v1/stocks/{$stock->id}/history");

        // Assert: Verify response structure and data
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully rendered stock all historically data',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'stock' => [
                        'id',
                        'company_name', // Adjusted from 'name'
                        'symbol',
                    ],
                    'historic' => [
                        '*' => [
                            'id',
                            'stock_id',
                            'open_price',
                            'close_price',
                            'high_price',
                            'low_price',
                            'current_price',
                            'date',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(5, $response->json('data.historic'));
    }

    #[Test]
    public function it_fails_to_fetch_history_for_non_existent_stock()
    {
        // Act: GET request for non-existent stock
        $response = $this->getJson('/api/v1/stocks/999/history');

        // Assert: Verify 404 response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Stock not found',
                'data' => null,
            ]);
    }
}
