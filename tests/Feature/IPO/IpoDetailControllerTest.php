<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Stock;
use App\Models\Sector;
use App\Models\IpoDetail;
use App\Enums\IpoDetailStatus;
use App\Notifications\IpoCreated;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class IpoDetailControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $stock;
    protected $sector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->sector = Sector::factory()->create();
        $this->stock = Stock::factory()->create([
            'sector_id' => $this->sector->id,
        ]);
    }

    #[Test]
    public function it_fetches_all_ipo_details()
    {
        IpoDetail::factory()->create([
            'stock_id' => $this->stock->id,
            'ipo_status' => IpoDetailStatus::Opened,
            'close_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/ipo-details');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'stock_id',
                             'ipo_status',
                             'open_date',
                             'close_date',
                             'stock',
                             'applications',
                         ],
                     ],
                 ])
                 ->assertJson([
                     'message' => 'Successfully fetched all ipo details',
                 ]);
    }

    #[Test]
    public function it_fetches_ipo_details_by_stock_id()
    {
        IpoDetail::factory()->create([
            'stock_id' => $this->stock->id,
            'ipo_status' => IpoDetailStatus::Opened,
            'close_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/ipo-details?stock_id=' . $this->stock->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully fetched all ipo details',
                 ])
                 ->assertJsonCount(1, 'data');
    }

}