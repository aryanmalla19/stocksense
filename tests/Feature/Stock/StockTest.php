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

    