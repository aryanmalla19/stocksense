<?php

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test; // <-- For new PHPUnit attribute syntax

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_fetches_notifications()
    {
        DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\GenericNotification',
            'notifiable_type' => get_class($this->user),
            'notifiable_id' => $this->user->id,
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/users/notifications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         '*' => ['id', 'time', 'notification', 'read_at'],
                     ],
                 ]);
    }



   
    // #[Test]
    // public function it_marks_all_notifications_as_read()
    // {
    //     DatabaseNotification::create([
    //         'id' => Str::uuid()->toString(),
    //         'type' => 'App\Notifications\GenericNotification',
    //         'notifiable_type' => get_class($this->user),
    //         'notifiable_id' => $this->user->id,
    //         'data' => ['message' => 'Test notification'],
    //         'read_at' => null,
    //     ]);

    //     $response = $this->actingAs($this->user, 'api')
    //         ->putJson('/api/v1/users/markasread-notifications');

    //     $response->assertStatus(200)
    //             ->assertJson([
    //                 'success' => true,
    //                 'message' => "Successfully marked all user's notifications as read",
    //             ]);
    // }
    // #[Test]
    // public function it_returns_error_when_fetching_non_existing_notification()
    // {
    //     $fakeId = Str::uuid()->toString();

    //     $response = $this->actingAs($this->user, 'api')
    //         ->getJson('/api/v1/users/notifications/' . $fakeId);

    //     $response->assertStatus(404)
    //              ->assertJson([
    //                  'success' => false,
    //                  'message' => 'Notification does not belong to the user.',
    //              ]);
    // }
}
