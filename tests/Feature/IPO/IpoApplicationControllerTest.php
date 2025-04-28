<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\IpoDetail;
use App\Models\IpoApplication;
use App\Models\Portfolio;
use PHPUnit\Framework\Attributes\Test;

class IpoApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $ipoDetail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->ipoDetail = IpoDetail::factory()->create([
            'issue_price' => 100,
        ]);
        Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 10000,
        ]);
    }

    #[Test]
    public function it_fetches_all_user_ipo_applications()
    {
        IpoApplication::factory()->create([
            'user_id' => $this->user->id,
            'ipo_id' => $this->ipoDetail->id,
            'applied_shares' => 10,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/ipo-applications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'ipo_id',
                             'applied_shares',
                         ],
                     ],
                 ])
                 ->assertJson([
                     'message' => 'Successfully fetched all user ipo applications',
                 ]);
    }

    #[Test]
    public function it_stores_a_new_ipo_application()
    {
        $data = [
            'ipo_id' => $this->ipoDetail->id,
            'applied_shares' => 50,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/ipo-applications', $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully IPO applied',
                 ])
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'ipo_id',
                         'applied_shares',
                     ],
                 ]);

        $this->assertDatabaseHas('ipo_applications', [
            'user_id' => $this->user->id,
            'ipo_id' => $this->ipoDetail->id,
            'applied_shares' => 50,
        ]);

        $this->assertDatabaseHas('portfolios', [
            'user_id' => $this->user->id,
            'amount' => 10000 - (50 * 100), // 100 is issue_price
        ]);
    }

    #[Test]
    public function it_prevents_duplicate_ipo_application()
    {
        IpoApplication::factory()->create([
            'user_id' => $this->user->id,
            'ipo_id' => $this->ipoDetail->id,
            'applied_shares' => 10,
        ]);

        $data = [
            'ipo_id' => $this->ipoDetail->id,
            'applied_shares' => 20,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/ipo-applications', $data);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => 'You have already applied for this IPO.',
                 ]);
    }

    #[Test]
    public function it_fails_ipo_application_with_insufficient_balance()
    {
        $data = [
            'ipo_id' => $this->ipoDetail->id,
            'applied_shares' => 200, // 200 * 100 = 20000 > 10000 balance
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/ipo-applications', $data);

        $response->assertStatus(400)
                 ->assertJson([
                     'message' => 'Insufficient balance',
                 ]);

        $this->assertDatabaseMissing('ipo_applications', [
            'user_id' => $this->user->id,
            'ipo_id' => $this->ipoDetail->id,
        ]);
    }


   
}