<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Return only notifications that still need attention.
     */
    public function index()
    {
        return response()->json($this->buildResponsePayload(Auth::user()));
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllRead()
    {
        $user = Auth::user();

        DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->whereNull('read_at')
            ->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            ...$this->buildResponsePayload($user),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(string $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    private function buildResponsePayload($user): array
    {
        $notifications = $user->unreadNotifications()
            ->take(20)
            ->get()
            ->map(fn($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read' => false,
                'time' => $notification->created_at->diffForHumans(),
            ])
            ->values();

        return [
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }
}
