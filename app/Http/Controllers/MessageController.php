<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Matche;
use App\Models\Attachment;
use App\Models\Report;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WebPushService;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function show(int $matchId)
    {
        $user = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->isBlocked()) {
            return redirect()->route('matches')
                ->with('error', 'Cette conversation a été bloquée.');
        }

        $match->load([
            'messages' => fn($q) => $q->orderBy('created_at', 'asc'),
            'messages.sender.profile',
            'messages.attachments',
            'user1.profile',
            'user2.profile',
        ]);

        // Marquer comme lus
        $match->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);

        // Marquer les notifications de messages de ce match comme lues
        $user->unreadNotifications->filter(function ($notif) use ($matchId) {
            $data = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
            return ($data['type'] ?? '') === 'new_message' && ($data['match_id'] ?? 0) == $matchId;
        })->each->markAsRead();

        return view('messages.chat', [
            'match' => $match,
            'messages' => $match->messages,
        ]);
    }

    public function send(Request $request, int $matchId)
    {
        $user = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->isBlocked()) {
            return back()->with('error', 'Cette conversation a été bloquée.');
        }

        $request->validate([
            'message' => 'nullable|string|max:2000',
            'attachment' => 'nullable|array|max:5',
            'attachment.*' => 'image|max:5120',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return back()->with('error', 'Veuillez écrire un message ou joindre une image.');
        }

        $message = Message::create([
            'match_id' => $matchId,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        // Pièces jointes
        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                Attachment::create([
                    'message_id' => $message->id,
                    'file_path' => $file->store('attachments', 'public'),
                    'file_type' => $file->getMimeType(),
                ]);
            }
        }

        // Recharger avec les relations pour le broadcast
        $message->load(['sender.profile', 'attachments']);

        // 🔥 Broadcast en temps réel
        broadcast(new MessageSent($message))->toOthers();

        // 📩 Notification pour l'autre utilisateur
        $otherUser = $match->getOtherUser($user->id);
        $otherUser->notify(new NewMessageNotification($message));

        // Après le message :
        app(WebPushService::class)->notifyNewMessage(
            $otherUser,
            Auth::user()->name,
            Str::limit($message->message, 50),
            $match->id
        );

        // Broadcast notification sur le canal privé
        broadcast(new \App\Events\NewMatch($match, $user))->toOthers();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message_id' => $message->id,
            ]);
        }

        return back();
    }

    public function block(int $matchId)
    {
        $user = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->user1_id === $user->id) {
            $match->blocked_by_user1 = true;
        } else {
            $match->blocked_by_user2 = true;
        }
        $match->save();

        return redirect()->route('matches')->with('success', 'Utilisateur bloqué.');
    }

    public function report(Request $request, int $matchId)
    {
        $user = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        $reportedUserId = $match->user1_id === $user->id
            ? $match->user2_id : $match->user1_id;

        Report::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $reportedUserId,
            'reason' => $request->reason ?? 'Signalé depuis le chat',
        ]);

        return back()->with('success', 'Signalement envoyé. Merci.');
    }

    public function delete(int $matchId)
    {
        $user = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);
        $match->messages()->delete();
        $match->delete();

        return redirect()->route('matches')->with('success', 'Conversation supprimée.');
    }

    /**
     * Typing indicator - broadcast en temps réel.
     */
    public function typing(int $matchId)
    {
        $user = Auth::user();
        $this->getAuthorizedMatch($matchId, $user->id);

        broadcast(new UserTyping($matchId, $user->id, $user->name))->toOthers();

        return response()->json(['status' => 'ok']);
    }

    private function getAuthorizedMatch(int $matchId, int $userId): Matche
    {
        $match = Matche::findOrFail($matchId);
        if ($match->user1_id !== $userId && $match->user2_id !== $userId) {
            abort(403, 'Accès non autorisé.');
        }
        return $match;
    }
}
