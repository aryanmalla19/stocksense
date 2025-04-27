<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    // === Authentication Tests ===

    /**
     * Test that unauthenticated users cannot access stock endpoints.
     */
    #[Test]
    public function test_unauthenticated_user_cannot_access_endpoints(): void
    {
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id, 'is_listed' => true]);

        $this->getJson('/api/v1/stocks')->assertStatus(401);
        $this->getJson("/api/v1/stocks/{$stock->id}")->assertStatus(401);
        $this->postJson('/api/v1/stocks', [])->assertStatus(401);
        $this->putJson("/api/v1/stocks/{$stock->id}", [])->assertStatus(401);
        $this->deleteJson("/api/v1/stocks/{$stock->id}")->assertStatus(401);
    }

    // === Stock Viewing Tests ===

    /**
     * Test that an authenticated user can view all listed stocks.
     */
    #[Test]
    public function test_authenticated_user_can_view_stocks(): void
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id, 'is_listed' => true]);
        $price = StockPrice::factory()->create(['stock_id' => $stock->id]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/stocks');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched all stocks',
                'data' => [
                    [
                        'id' => $stock->id,
                        'symbol' => $stock->symbol,
                        'company_name' => $stock->company_name,
                        'sector_id' => $sector->id,
                        'is_listed' => true,
                        'sector' => $sector->name,
                        'is_watchlist' => false,
                        'open_price' => (float) $price->open_price,
                        'close_price' => (float) $price->close_price,
                        'high_price' => (float) $price->high_price,
                        'low_price' => (float) $price->low_price,
                        'current_price' => (float) $price->current_price,
                    ],
                ],
            ]);
    }

    /**
     * Test that an authenticated user can filter stocks by symbol.
     */
    #[Test]
    public function test_authenticated_user_can_filter_stocks_by_symbol(): void
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['symbol' => 'AAPL', 'sector_id' => $sector->id, 'is_listed' => true]);
        Stock::factory()->create(['symbol' => 'GOOGL', 'sector_id' => $sector->id, 'is_listed' => true]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/stocks?symbol=AAPL');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched all stocks',
                'data' => [
                    [
                        'symbol' => 'AAPL',
                        'sector_id' => $sector->id,
                        'is_listed' => true,
                        'sector' => $sector->name,
                        'is_watchlist' => false,
                    ],
                ],
            ])
            ->assertJsonMissing(['symbol' => 'GOOGL']);
    }
     /**
     * Test that an authenticated user can view a specific listed stock.
     */
    #[Test]
    public function test_authenticated_user_can_view_specific_listed_stock(): void
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id, 'is_listed' => true]);
        $price = StockPrice::factory()->create(['stock_id' => $stock->id]);

        $response = $this->actingAs($user, 'api')->getJson("/api/v1/stocks/{$stock->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched stock data',
                'data' => [
                    'id' => $stock->id,
                    'symbol' => $stock->symbol,
                    'company_name' => $stock->company_name,
                    'sector_id' => $sector->id,
                    'is_listed' => true,
                    'sector' => $sector->name,
                    'is_watchlist' => false,
                    'open_price' => (float) $price->open_price,
                    'close_price' => (float) $price->close_price,
                    'high_price' => (float) $price->high_price,
                    'low_price' => (float) $price->low_price,
                    'current_price' => (float) $price->current_price,
                ],
            ]);
    }
     /**
     * Test that viewing an unlisted stock returns a 404 error.
     */
    #[Test]
    public function test_viewing_unlisted_stock_returns_404(): void
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id, 'is_listed' => false]);

        $response = $this->actingAs($user, 'api')->getJson("/api/v1/stocks/{$stock->id}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No listed stock found with ID '.$stock->id,
            ]);
    }

    // === Watchlist Tests ===

    /**
     * Test that the is_watchlist field works correctly for a stock.
     */
    #[Test]
    public function test_is_watchlist_field_works_correctly(): void
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id, 'is_listed' => true]);
        Watchlist::factory()->create(['user_id' => $user->id, 'stock_id' => $stock->id]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/stocks');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully fetched all stocks',
                'data' => [
                    [
                        'id' => $stock->id,
                        'symbol' => $stock->symbol,
                        'is_watchlist' => true,
                    ],
                ],
            ]);
    }
    // === Admin Stock Management Tests ===

    /**
     * Test that an admin can create a stock.
     */
    #[Test]
    public function test_admin_can_create_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $sector = Sector::factory()->create();

        $response = $this->actingAs($admin, 'api')->postJson('/api/v1/stocks', [
            'symbol' => 'AAPL',
            'company_name' => 'Apple Inc.',
            'sector_id' => $sector->id,
            'description' => 'Technology company',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Successfully registered stock',
                'data' => [
                    'symbol' => 'AAPL',
                    'company_name' => 'Apple Inc.',
                    'sector_id' => $sector->id,
                    'is_listed' => false,
                    'sector' => $sector->name,
                    'is_watchlist' => false,
                ],
            ]);

        $this->assertDatabaseHas('stocks', [
            'symbol' => 'AAPL',
            'company_name' => 'Apple Inc.',
            'sector_id' => $sector->id,
            'is_listed' => false,
        ]);
    }
    /**
     * Test that an admin can update a stock.
     */
    #[Test]
    public function test_admin_can_update_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin, 'api')->putJson("/api/v1/stocks/{$stock->id}", [
            'symbol' => 'GOOGL',
            'company_name' => 'Alphabet Inc.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Stock successfully updated',
                'data' => [
                    'id' => $stock->id,
                    'symbol' => 'GOOGL',
                    'company_name' => 'Alphabet Inc.',
                    'sector_id' => $sector->id,
                    'is_listed' => 1, // TODO: Fix cast issue
                    'is_watchlist' => false,
                ],
            ]);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'symbol' => 'GOOGL',
            'company_name' => 'Alphabet Inc.',
        ]);
    }
    /**
     * Test that an admin can delete a stock.
     */
    #[Test]
    public function test_admin_can_delete_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin, 'api')->deleteJson("/api/v1/stocks/{$stock->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully deleted stock with ID '.$stock->id,
            ]);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    