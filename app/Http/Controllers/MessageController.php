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
use Illuminate\Support\Facades\Cache;
use App\Services\ImageCompressor;
use App\Services\WebPushService;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    // Nombre de messages chargés initialement (les plus récents)
    private const MESSAGES_PER_PAGE = 50;

    public function show(int $matchId)
    {
        $user  = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->isBlocked()) {
            return redirect()->route('matches')->with('error', 'Cette conversation a été bloquée.');
        }

        // ── PAGINATION : on charge seulement les N derniers messages ────
        // Au lieu de charger tout l'historique, on prend les 50 derniers.
        // L'utilisateur peut charger plus via le bouton "Voir plus".
        $messages = Message::where('match_id', $matchId)
            ->with(['sender.profile', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->limit(self::MESSAGES_PER_PAGE)
            ->get()
            ->reverse()
            ->values();

        $hasMore = Message::where('match_id', $matchId)->count() > self::MESSAGES_PER_PAGE;

        // Charger les profils pour l'en-tête du chat
        $match->load(['user1.profile', 'user2.profile']);

        // Marquer comme lus en une seule requête (UPDATE batch)
        $unreadCount = Message::where('match_id', $matchId)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->count();

        if ($unreadCount > 0) {
            Message::where('match_id', $matchId)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->update(['read_at' => now()]);

            // Invalider le cache nav badges (les messages sont lus)
            SwipeController::invalidateUserCaches($user->id);
        }

        // Marquer les notifications de messages de ce match comme lues
        $user->unreadNotifications->filter(function ($notif) use ($matchId) {
            $data = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
            return ($data['type'] ?? '') === 'new_message' && ($data['match_id'] ?? 0) == $matchId;
        })->each->markAsRead();

        return view('messages.chat', [
            'match'    => $match,
            'messages' => $messages,
            'hasMore'  => $hasMore,
        ]);
    }

    /**
     * Charger les messages plus anciens (scroll infini vers le haut).
     */
    public function loadMore(Request $request, int $matchId)
    {
        $user  = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        $beforeId = (int) $request->input('before_id', PHP_INT_MAX);

        $messages = Message::where('match_id', $matchId)
            ->where('id', '<', $beforeId)
            ->with(['sender.profile', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->limit(self::MESSAGES_PER_PAGE)
            ->get()
            ->reverse()
            ->values();

        $hasMore = Message::where('match_id', $matchId)
            ->where('id', '<', $messages->first()?->id ?? 0)
            ->exists();

        return response()->json([
            'messages' => $messages->map(function ($msg) use ($user) {
                return [
                    'id'           => $msg->id,
                    'message'      => $msg->message,
                    'sender_id'    => $msg->sender_id,
                    'is_me'        => $msg->sender_id === $user->id,
                    'time'         => $msg->created_at->format('H:i'),
                    'attachments'  => $msg->attachments->map(fn($a) => ['url' => $a->url]),
                    'read_at'      => $msg->read_at,
                ];
            }),
            'has_more' => $hasMore,
        ]);
    }

    public function send(Request $request, int $matchId)
    {
        $user  = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->isBlocked()) {
            return back()->with('error', 'Cette conversation a été bloquée.');
        }

        $request->validate([
            'message'      => 'nullable|string|max:2000',
            'attachment'   => 'nullable|array|max:5',
            'attachment.*' => 'image|max:5120',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return back()->with('error', 'Veuillez écrire un message ou joindre une image.');
        }

        $message = Message::create([
            'match_id'  => $matchId,
            'sender_id' => $user->id,
            'message'   => $request->message,
        ]);

        if ($request->hasFile('attachment')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            foreach ($request->file('attachment') as $file) {
                Attachment::create([
                    'message_id' => $message->id,
                    'file_path'  => app(ImageCompressor::class)->storeAttachment($file, $disk),
                    'file_type'  => $file->getMimeType(),
                ]);
            }
        }

        $message->load(['sender.profile', 'attachments']);

        broadcast(new MessageSent($message))->toOthers();

        $otherUser = $match->getOtherUser($user->id);
        $otherUser->notify(new NewMessageNotification($message));

        // Invalider le cache nav de l'autre utilisateur (nouveau message non lu)
        SwipeController::invalidateUserCaches($otherUser->id);
        // Invalider le cache de la liste des matchs (last_message à jour)
        Cache::forget("matches_for_{$user->id}");
        Cache::forget("matches_for_{$otherUser->id}");

        app(WebPushService::class)->notifyNewMessage(
            $otherUser,
            Auth::user()->name,
            Str::limit($message->message, 50),
            $match->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message_id' => $message->id]);
        }

        return back();
    }

    public function block(int $matchId)
    {
        $user  = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->user1_id === $user->id) {
            $match->blocked_by_user1 = true;
        } else {
            $match->blocked_by_user2 = true;
        }
        $match->save();

        SwipeController::invalidateUserCaches($user->id);

        return redirect()->route('matches')->with('success', 'Utilisateur bloqué.');
    }

    public function report(Request $request, int $matchId)
    {
        $user  = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        $reportedUserId = $match->user1_id === $user->id ? $match->user2_id : $match->user1_id;

        Report::create([
            'reporter_id'      => $user->id,
            'reported_user_id' => $reportedUserId,
            'reason'           => $request->reason ?? 'Signalé depuis le chat',
        ]);

        return back()->with('success', 'Signalement envoyé. Merci.');
    }

    public function delete(int $matchId)
    {
        $user  = Auth::user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);
        $match->messages()->delete();
        $match->delete();

        SwipeController::invalidateUserCaches($user->id);

        return redirect()->route('matches')->with('success', 'Conversation supprimée.');
    }

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
