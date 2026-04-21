<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Matche;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LikeController extends Controller
{
    // TTL 90 s : assez frais pour l'UX, évite les requêtes répétées
    private const TTL = 90;

    public function whoLikedMe()
    {
        $user = Auth::user();

        // ── Cache : résultat mis en cache 90 s ──────────────────────────
        // Invalidé quand quelqu'un like/passe (SwipeController::like)
        $likers = Cache::remember("likes_for_{$user->id}", self::TTL, function () use ($user) {
            // IDs des utilisateurs avec qui on a déjà matché
            $matchedIds = Matche::forUser($user->id)->get()
                ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);

            // IDs des utilisateurs que j'ai déjà likés
            $myLikedIds = Like::where('user_id', $user->id)->pluck('liked_user_id');

            return Like::where('liked_user_id', $user->id)
                ->whereNotIn('user_id', $matchedIds)
                ->whereNotIn('user_id', $myLikedIds)
                ->with('user.profile')
                ->latest()
                ->get()
                ->map(function ($like) {
                    $u = $like->user;
                    $p = $u->profile;
                    if (!$p) return null;

                    return [
                        'id'         => $u->id,
                        'name'       => $u->name,
                        'age'        => $p->age,
                        'photo'      => $p->photo_url,
                        'university' => $p->university_name ?? $p->university ?? 'UGB',
                        'ufr'        => $p->ufr,
                        'bio'        => $p->bio,
                        'liked_at'   => $like->created_at->diffForHumans(),
                    ];
                })
                ->filter()
                ->values();
        });

        // Total de likes (mis en cache séparément — TTL plus long car moins critique)
        $totalLikes = Cache::remember("total_likes_{$user->id}", 300, function () use ($user) {
            return Like::where('liked_user_id', $user->id)->count();
        });

        return view('likes.index', compact('likers', 'totalLikes'));
    }
}
