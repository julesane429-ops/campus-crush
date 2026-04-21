<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Matche;

class MatchController extends Controller
{
    private const TTL = 120; // 2 min

    public function index()
    {
        $user = Auth::user();

        // ── Cache 2 min — invalidé à chaque nouveau message / match ─────
        $matchData = Cache::remember("matches_for_{$user->id}", self::TTL, function () use ($user) {
            $matches = Matche::with(['user1.profile', 'user2.profile'])
                ->notBlockedFor($user->id)
                ->get();

            if ($matches->isEmpty()) return collect();

            $matchIds = $matches->pluck('id');

            // 1 requête pour tous les derniers messages
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

            // 1 requête pour tous les non-lus
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
                    'photo'        => $otherUser->profile?->photo_url
                        ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($otherUser->name, 0, 2)),
                    'last_message' => $lastMsg?->message ?: ($lastMsg ? '📷 Photo' : null),
                    'last_time'    => $lastMsg ? \Carbon\Carbon::parse($lastMsg->created_at)->diffForHumans() : null,
                    'unread'       => $unreadCounts->get($match->id, 0),
                    'sort_date'    => $lastMsg?->created_at ?? $match->created_at,
                ];
            })->sortByDesc('sort_date')->values();
        });

        return view('matches.index', ['matches' => $matchData]);
    }

    public function show(int $id)
    {
        $user = Auth::user();

        $match = Matche::with(['user1.profile', 'user2.profile'])
            ->findOrFail($id);

        if ($match->user1_id !== $user->id && $match->user2_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        $otherUser = $match->getOtherUser($user->id);

        return view('matches.show', [
            'match'     => $match,
            'otherUser' => $otherUser,
        ]);
    }
}
