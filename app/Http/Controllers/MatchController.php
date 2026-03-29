<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Matche;

class MatchController extends Controller
{
    /**
     * Liste de tous les matchs de l'utilisateur connecté.
     */
    public function index()
{
    $user = Auth::user();

    $matches = Matche::with([
            'user1.profile',
            'user2.profile',
            'messages' => fn($q) => $q->orderByDesc('created_at')->limit(1),
        ])
        ->notBlockedFor($user->id)
        ->get()
        ->sortByDesc(function ($match) {
            return $match->messages->first()?->created_at;
        })
        ->map(function ($match) use ($user) {
            $otherUser = $match->getOtherUser($user->id);
            $lastMsg = $match->messages->first();

            $unreadCount = $match->messages()
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->count();

            return [
                'match_id'     => $match->id,
                'id'           => $otherUser->id,
                'name'         => $otherUser->name,
                'photo'        => $otherUser->profile?->photo_url
                    ?? 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($otherUser->name, 0, 2)),
                'last_message' => $lastMsg?->message ?: ($lastMsg ? '📷 Photo' : null),
                'last_time'    => $lastMsg?->created_at?->diffForHumans(),
                'unread'       => $unreadCount,
            ];
        });

    return view('matches.index', compact('matches'));
}

    /**
     * Afficher un match spécifique.
     */
    public function show(int $id)
    {
        $user = Auth::user();

        $match = Matche::with(['user1.profile', 'user2.profile'])
            ->findOrFail($id);

        // SÉCURITÉ: vérifier que l'utilisateur fait partie du match
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
