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
}
