<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_endpoint_only_returns_unread_notifications(): void
    {
        $user = User::factory()->create();

        $unreadNotification = $this->createNotification($user, [
            'type' => 'new_message',
            'match_id' => 12,
            'message' => 'Message non lu',
        ]);

        $this->createNotification($user, [
            'type' => 'new_match',
            'match_id' => 34,
            'message' => 'Notification deja lue',
        ], now());

        $response = $this
            ->actingAs($user)
            ->getJson('/notifications');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.id', $unreadNotification->id)
            ->assertJsonPath('notifications.0.read', false)
            ->assertJsonPath('unread_count', 1);
    }

    public function test_mark_all_read_marks_every_unread_notification_as_read(): void
    {
        $user = User::factory()->create();

        $firstNotification = $this->createNotification($user, [
            'type' => 'new_message',
            'match_id' => 12,
            'message' => 'Premier message',
        ]);

        $secondNotification = $this->createNotification($user, [
            'type' => 'new_match',
            'match_id' => 34,
            'message' => 'Second message',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson('/notifications/read-all');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0)
            ->assertJsonCount(0, 'notifications');

        $this->assertNotNull($firstNotification->fresh()->read_at);
        $this->assertNotNull($secondNotification->fresh()->read_at);
    }

    private function createNotification(User $user, array $data, $readAt = null): DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => $data,
            'read_at' => $readAt,
        ]);
    }
}
