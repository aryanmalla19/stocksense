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

} 