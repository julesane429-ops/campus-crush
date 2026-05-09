<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Matche;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LikeController extends Controller
{
    private const TTL = 90;

    public function whoLikedMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $likers = Cache::remember("likes_for_{$user->id}", self::TTL, function () use ($user) {
            $matchedIds = Matche::forUser($user->id)->get()
                ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);

            $myLikedIds = Like::where('user_id', $user->id)->pluck('liked_user_id');

            return Like::where('liked_user_id', $user->id)
                ->whereNotIn('user_id', $matchedIds)
                ->whereNotIn('user_id', $myLikedIds)
                ->with('user.profile')
                ->latest()
                ->get()
                ->map(function ($like) use ($user) {
                    $u = $like->user;
                    $p = $u->profile;
                    if (!$p) return null;

                    $hasSubscription = $user->hasActiveSubscription();

                    return [
                        'id'          => $u->id,
                        'name'        => $hasSubscription ? $u->name : '???',
                        'age'         => $p->age,
                        'photo_url'   => $p->photo_url,
                        'photo_blurred' => !$hasSubscription,
                        'university'  => $p->university_name ?? $p->university ?? 'UGB',
                        'ufr'         => $p->ufr,
                        'bio'         => $hasSubscription ? $p->bio : null,
                        'liked_at'    => $like->created_at->diffForHumans(),
                    ];
                })
                ->filter()
                ->values()
                ->toArray();
        });

        $totalLikes = Cache::remember("total_likes_{$user->id}", 300, fn() =>
            Like::where('liked_user_id', $user->id)->count()
        );

        return response()->json([
            'likers'      => $likers,
            'total_likes' => $totalLikes,
        ]);
    }
}
