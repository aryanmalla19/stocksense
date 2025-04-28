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


    #[Test]
    public function it_fetches_ipo_details_by_status()
    {
        IpoDetail::factory()->create([
            'stock_id' => $this->stock->id,
            'ipo_status' => IpoDetailStatus::Opened,
            'close_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/ipo-details?status=' . IpoDetailStatus::Opened->value);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully fetched all ipo details',
                 ])
                 ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_returns_404_for_non_existent_ipo_detail()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/ipo-details/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'IPO detail not found for 999',
                 ]);
    }

    
    // public function it_stores_a_new_ipo_detail_and_notifies_users()
    // {
    //     Notification::fake();

    //     $data = [
    //         'stock_id' => $this->stock->id,
    //         'issue_price' => 100,
    //         'open_date' => Carbon::now()->addDays(1)->toDateString(),
    //         'close_date' => Carbon::now()->addDays(5)->toDateString(),
    //         'ipo_status' => IpoDetailStatus::Upcoming->value,
    //     ];

    //     $response = $this->actingAs($this->user, 'api')
    //         ->postJson('/api/v1/ipo-details', $data);

    //     $response->assertStatus(200)
    //              ->assertJson([
    //                  'message' => 'Successfully created new IPO detail',
    //              ])
    //              ->assertJsonStructure([
    //                  'message',
    //                  'data' => [
    //                      'id',
    //                      'stock_id',
    //                      'issue_price',
    //                      'open_date',
    //                      'close_date',
    //                      'ipo_status',
    //                  ],
    //              ]);

    //     $this->assertDatabaseHas('ipo_details', [
    //         'stock_id' => $this->stock->id,
    //         'issue_price' => 100,
    //         'ipo_status' => IpoDetailStatus::Upcoming,
    //     ]);

    //     Notification::assertSentTo(
    //         User::all(),
    //         IpoCreated::class,
    //         function ($notification, $channels) use ($data) {
    //             return $notification->ipoDetail->stock_id === $data['stock_id'];
    //         }
    //     );
    // }


    #[Test]
    public function it_fails_to_store_ipo_detail_with_invalid_data()
    {
        $data = [
            'stock_id' => 'invalid',
            'issue_price' => -100,
            'open_date' => 'invalid-date',
            'close_date' => 'invalid-date',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/ipo-details', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors' => [
                         'stock_id',
                         'issue_price',
                         'open_date',
                         'close_date',
                     ],
                 ]);
    }




}