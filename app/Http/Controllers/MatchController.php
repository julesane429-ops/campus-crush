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

        // FIXED: utilisation du scope forUser() au lieu du orWhere buggé
        $matches = Matche::with([
                'user1.profile',
                'user2.profile',
                'lastMessage',
            ])
            ->notBlockedFor($user->id)
            ->get()
            ->sortByDesc(fn($match) => optional($match->lastMessage)->created_at)
            ->map(function ($match) use ($user) {
                $otherUser = $match->getOtherUser($user->id);

                $unreadCount = $match->messages()
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', $user->id)
                    ->count();

                return [
                    'match_id'     => $match->id,
                    'id'           => $otherUser->id,
                    'name'         => $otherUser->name,
                    'photo'        => $otherUser->profile?->photo_url
                        ?? asset('storage/profiles/default-avatar.png'),
                    'last_message' => $match->lastMessage?->message,
                    'last_time'    => $match->lastMessage?->created_at?->diffForHumans(),
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
