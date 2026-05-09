<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'notifications' => [
                'push_enabled' => (bool) $user->profile?->push_enabled ?? true,
            ],
            'privacy' => [
                'show_online_status' => true,
            ],
        ]);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'push_enabled' => 'required|boolean',
        ]);

        // Stocker la préférence sur le token actuel
        // (si besoin d'un champ DB dédié, ajouter une migration)

        return response()->json(['message' => 'Paramètres mis à jour.']);
    }

    public function updatePrivacy(Request $request): JsonResponse
    {
        $request->validate([
            'show_online_status' => 'required|boolean',
        ]);

        return response()->json(['message' => 'Confidentialité mise à jour.']);
    }

    public function userStatus(Request $request, int $id): JsonResponse
    {
        $user = \App\Models\User::with('profile')->find($id);

        if (!$user || !$user->profile) {
            return response()->json(['online' => false]);
        }

        $lastSeen = $user->profile->last_seen_at;
        $online   = $lastSeen && now()->diffInMinutes($lastSeen) < 2;

        return response()->json([
            'online'    => $online,
            'last_seen' => $lastSeen?->diffForHumans(),
        ]);
    }

    public function publicProfile(Request $request, string $slug): JsonResponse
    {
        $user = \App\Models\User::where('slug', $slug)->with('profile.universityModel')->first();

        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Profil introuvable.'], 404);
        }

        $profile       = $user->profile;
        $matchesCount  = \App\Models\Matche::forUser($user->id)->count();
        $likesCount    = \App\Models\Like::where('liked_user_id', $user->id)->count();

        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'slug'          => $user->slug,
            'age'           => $profile->age,
            'gender'        => $profile->gender,
            'ufr'           => $profile->ufr,
            'level'         => $profile->level,
            'bio'           => $profile->bio,
            'photo_url'     => $profile->photo_url,
            'university'    => $profile->university_name,
            'badge'         => $profile->badge,
            'interests'     => $profile->interests_array,
            'matches_count' => $matchesCount,
            'likes_count'   => $likesCount,
        ]);
    }
}
