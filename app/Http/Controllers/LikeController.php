<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Matche;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Afficher les utilisateurs qui t'ont liké (et que tu n'as pas encore liké).
     */
    public function whoLikedMe()
    {
        $user = Auth::user();

        // IDs des utilisateurs avec qui on a déjà matché
        $matchedIds = Matche::forUser($user->id)->get()
            ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);

        // IDs des utilisateurs que j'ai déjà likés
        $myLikedIds = Like::where('user_id', $user->id)->pluck('liked_user_id');

        // Utilisateurs qui m'ont liké mais pas encore matché et que je n'ai pas liké
        $likers = Like::where('liked_user_id', $user->id)
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
                    'id' => $u->id,
                    'name' => $u->name,
                    'age' => $p->age,
                    'photo' => $p->photo_url,
                    'university' => $p->university_name ?? $p->university ?? 'UGB',
                    'ufr' => $p->ufr,
                    'bio' => $p->bio,
                    'liked_at' => $like->created_at->diffForHumans(),
                ];
            })
            ->filter()
            ->values();

        $totalLikes = Like::where('liked_user_id', $user->id)->count();

        return view('likes.index', compact('likers', 'totalLikes'));
    }
}
