<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Récupérer les notifications (JSON pour AJAX).
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->take(20)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'read' => $n->read_at !== null,
                'time' => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}
