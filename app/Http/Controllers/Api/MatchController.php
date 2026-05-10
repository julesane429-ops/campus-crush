<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SwipeController;
use App\Models\Attachment;
use App\Models\Matche;
use App\Models\Message;
use App\Models\Report;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Notifications\NewMessageNotification;
use App\Services\ImageCompressor;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MatchController extends Controller
{
    private const MESSAGES_PER_PAGE = 30;
    private const MATCHES_TTL       = 120;

    // ── Liste des matchs ────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $matchData = Cache::remember("matches_for_{$user->id}", self::MATCHES_TTL, function () use ($user) {
            $matches = Matche::with(['user1.profile', 'user2.profile'])
                ->notBlockedFor($user->id)
                ->get();

            if ($matches->isEmpty()) return [];

            $matchIds = $matches->pluck('id');

            $lastMessages = DB::table('messages')
                ->select('match_id', 'message', 'created_at', 'sender_id')
                ->whereIn('match_id', $matchIds)
                ->whereIn('id', function ($sub) use ($matchIds) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('messages')
                        ->whereIn('match_id', $matchIds)
                        ->groupBy('match_id');
                })
                ->get()
                ->keyBy('match_id');

            $unreadCounts = DB::table('messages')
                ->select('match_id', DB::raw('COUNT(*) as count'))
                ->whereIn('match_id', $matchIds)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->groupBy('match_id')
                ->pluck('count', 'match_id');

            return $matches->map(function ($match) use ($user, $lastMessages, $unreadCounts) {
                $otherUser = $match->getOtherUser($user->id);
                $lastMsg   = $lastMessages->get($match->id);

                return [
                    'match_id'     => $match->id,
                    'id'           => $otherUser->id,
                    'name'         => $otherUser->name,
                    'photo_url'    => $otherUser->profile?->photo_url
                        ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($otherUser->name, 0, 2)),
                    'last_message' => $lastMsg?->message ?: ($lastMsg ? '📷 Photo' : null),
                    'last_time'    => $lastMsg ? \Carbon\Carbon::parse($lastMsg->created_at)->diffForHumans() : null,
                    'unread'       => $unreadCounts->get($match->id, 0),
                    'sort_date'    => $lastMsg?->created_at ?? $match->created_at,
                    'is_blocked'   => $match->isBlocked(),
                ];
            })->sortByDesc('sort_date')->values()->toArray();
        });

        return response()->json($matchData);
    }

    // ── Messages d'un match ─────────────────────────────────────────────
    public function messages(Request $request, int $matchId): JsonResponse
    {
        $user  = $request->user();
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

        $hasMore = $beforeId === PHP_INT_MAX
            ? Message::where('match_id', $matchId)->count() > self::MESSAGES_PER_PAGE
            : Message::where('match_id', $matchId)->where('id', '<', $messages->first()?->id ?? 0)->exists();

        // Marquer comme lus
        $unreadCount = Message::where('match_id', $matchId)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->count();

        if ($unreadCount > 0) {
            Message::where('match_id', $matchId)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->update(['read_at' => now()]);

            SwipeController::invalidateUserCaches($user->id);
        }

        $otherUser = $match->getOtherUser($user->id);

        return response()->json([
            'messages' => $messages->map(fn($msg) => $this->formatMessage($msg, $user->id)),
            'has_more' => $hasMore,
            'match'    => [
                'id'            => $match->id,
                'is_blocked'    => $match->isBlocked(),
                'other_user_id' => $otherUser->id,
                'other_name'    => $otherUser->name,
                'other_photo'   => $otherUser->profile?->photo_url,
            ],
        ]);
    }

    // ── Envoyer un message ──────────────────────────────────────────────
    public function send(Request $request, int $matchId): JsonResponse
    {
        $user  = $request->user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->isBlocked()) {
            return response()->json(['message' => 'Cette conversation a été bloquée.'], 403);
        }

        $request->validate([
            'message'      => 'nullable|string|max:2000',
            'attachment'   => 'nullable|array|max:5',
            'attachment.*' => 'image|max:5120',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Message ou pièce jointe requis.'], 422);
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

        SwipeController::invalidateUserCaches($otherUser->id);
        Cache::forget("matches_for_{$user->id}");
        Cache::forget("matches_for_{$otherUser->id}");

        app(WebPushService::class)->notifyNewMessage(
            $otherUser, $user->name, Str::limit($message->message, 50), $match->id
        );

        return response()->json([
            'message' => $this->formatMessage($message, $user->id),
        ], 201);
    }

    // ── Indicateur de frappe ────────────────────────────────────────────
    public function typing(Request $request, int $matchId): JsonResponse
    {
        $user = $request->user();
        $this->getAuthorizedMatch($matchId, $user->id);
        broadcast(new UserTyping($matchId, $user->id, $user->name))->toOthers();
        return response()->json(['status' => 'ok']);
    }

    // ── Bloquer ─────────────────────────────────────────────────────────
    public function block(Request $request, int $matchId): JsonResponse
    {
        $user  = $request->user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        if ($match->user1_id === $user->id) {
            $match->blocked_by_user1 = true;
        } else {
            $match->blocked_by_user2 = true;
        }
        $match->save();

        SwipeController::invalidateUserCaches($user->id);

        return response()->json(['message' => 'Utilisateur bloqué.']);
    }

    // ── Signaler ────────────────────────────────────────────────────────
    public function report(Request $request, int $matchId): JsonResponse
    {
        $user  = $request->user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);

        $reportedUserId = $match->user1_id === $user->id ? $match->user2_id : $match->user1_id;

        Report::create([
            'reporter_id'      => $user->id,
            'reported_user_id' => $reportedUserId,
            'reason'           => $request->reason ?? 'Signalé depuis le chat',
        ]);

        return response()->json(['message' => 'Signalement envoyé. Merci.']);
    }

    // ── Supprimer la conversation ───────────────────────────────────────
    public function destroy(Request $request, int $matchId): JsonResponse
    {
        $user  = $request->user();
        $match = $this->getAuthorizedMatch($matchId, $user->id);
        $match->messages()->delete();
        $match->delete();

        SwipeController::invalidateUserCaches($user->id);

        return response()->json(['message' => 'Conversation supprimée.']);
    }

    private function getAuthorizedMatch(int $matchId, int $userId): Matche
    {
        $match = Matche::findOrFail($matchId);
        if ($match->user1_id !== $userId && $match->user2_id !== $userId) {
            abort(403, 'Accès non autorisé.');
        }
        return $match;
    }

    private function formatMessage(Message $msg, int $currentUserId): array
    {
        return [
            'id'          => $msg->id,
            'message'     => $msg->message,
            'sender_id'   => $msg->sender_id,
            'is_me'       => $msg->sender_id === $currentUserId,
            'time'        => $msg->created_at->format('H:i'),
            'created_at'  => $msg->created_at->toISOString(),
            'read_at'     => $msg->read_at?->toISOString(),
            'attachments' => $msg->relationLoaded('attachments')
                ? $msg->attachments->map(fn($a) => ['url' => $a->url])->toArray()
                : [],
        ];
    }
}
