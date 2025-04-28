<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_authenticated_user_can_retrieve_watchlists()
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 150.00]);
        $watchlist = Watchlist::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/watchlists');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'stock_id' => $stock->id,
                        'user_id' => $user->id,
                        'stock' => [
                            'id' => $stock->id,
                            'symbol' => $stock->symbol,
                            'company_name' => $stock->company_name,
                            'sector_id' => $sector->id,
                            'is_listed' => true,
                            'sector' => $sector->name,
                            'is_watchlist' => true,
                            'current_price' => '150.00',
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'stock_id',
                        'user_id',
                        'stock' => [
                            'id',
                            'symbol',
                            'company_name',
                            'sector_id',
                            'is_listed',
                            'sector',
                            'is_watchlist',
                            'open_price',
                            'close_price',
                            'high_price',
                            'low_price',
                            'current_price',
                        ],
                    ],
                ],
                'links',
                'meta',
            ]);
    }


    public function test_unauthenticated_user_cannot_access_watchlists()
    {
        $response = $this->getJson('/api/v1/watchlists');
        $response->assertStatus(401);
    }


    public function test_authenticated_user_can_add_watchlist()
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        StockPrice::factory()->create(['stock_id' => $stock->id, 'current_price' => 200.00]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/watchlists', [
                'stock_id' => $stock->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully added watchlist',
                'data' => [
                    'stock_id' => $stock->id,
                    'user_id' => $user->id,
                    'stock' => [
                        'id' => $stock->id,
                        'symbol' => $stock->symbol,
                        'company_name' => $stock->company_name,
                        'sector_id' => $sector->id,
                        'is_listed' => true,
                        'sector' => $sector->name,
                        'is_watchlist' => true,
                        'current_price' => '200.00',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('watchlists', [
            'user_id' => $user->id,
            'stock_id' => $stock->id,
        ]);
    }



    public function test_adding_existing_watchlist_fails()
    {
        $user = User::factory()->create();
        $sector = Sector::factory()->create();
        $stock = Stock::factory()->create(['sector_id' => $sector->id]);
        Watchlist::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/watchlists', [
                'stock_id' => $stock->id,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Same watchlist already exists',
            ]);
    }



    public function test_adding_watchlist_with_invalid_data_fails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/watchlists', [
                'stock_id' => 999, // Non-existent stock
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_id'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'stock_id' => ['The selected stock does not exist.'],
                ],
            ]);
    }


    public function test_authenticated_user_can_delete_watchlist()
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create();
        Watchlist::factory()->create([
            'user_id' => $user->id,
            'stock_id' => $stock->id,
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/v1/watchlists/{$stock->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully removed watchlist',
            ]);

        $this->assertDatabaseMissing('watchlists', [
            'user_id' => $user->id,
            'stock_id' => $stock->id,
        ]);
    }


    public function test_deleting_non_existent_watchlist_fails()
    {
        $user = User::factory()->create();
        $stockId = 999; // Non-existent stock ID

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/v1/watchlists/{$stockId}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No watchlist found with Stock ID ' . $stockId,
            ]);
    }



    public function test_multiple_delete_with_no_matching_watchlists_fails()
    {
        $user = User::factory()->create();
        $stockIds = [999, 1000]; // Non-existent stock IDs

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/watchlists/multiple-delete', [
                'stock_ids' => $stockIds,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_ids.0', 'stock_ids.1'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'stock_ids.0' => ['The selected stock_ids.0 is invalid.'],
                    'stock_ids.1' => ['The selected stock_ids.1 is invalid.'],
                ],
            ]);
    }



    public function test_multiple_delete_with_empty_stock_ids_fails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/watchlists/multiple-delete', [
                'stock_ids' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_ids'])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'stock_ids' => ['The stock ids field is required.'],
                ],
            ]);
    }



    
}
