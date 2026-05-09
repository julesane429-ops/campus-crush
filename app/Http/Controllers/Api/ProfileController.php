<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SwipeController;
use App\Models\Like;
use App\Models\Matche;
use App\Models\Profile;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile.universityModel', 'subscription');
        $profile = $user->profile;

        $stats = Cache::remember("profile_stats_{$user->id}", 300, function () use ($user) {
            return [
                'likes_count'   => Like::where('liked_user_id', $user->id)->count(),
                'matches_count' => Matche::forUser($user->id)->count(),
            ];
        });

        return response()->json([
            'user'    => $this->formatUser($user),
            'stats'   => $stats,
            'universities' => Cache::remember('universities_active', 3600, fn() =>
                University::where('is_active', true)->orderBy('short_name')->get(['id', 'short_name', 'full_name'])
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->profile) {
            return response()->json(['message' => 'Profil déjà créé.'], 409);
        }

        $validated = $request->validate([
            'age'           => 'required|integer|min:18|max:60',
            'gender'        => 'required|in:homme,femme',
            'ufr'           => 'required|string|max:20',
            'level'         => 'required|string|in:L1,L2,L3,M1,M2,D1,D2,D3',
            'bio'           => 'required|string|max:200',
            'promotion'     => 'nullable|string|max:10',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'interests'     => 'nullable|string|max:500',
            'university_id' => 'nullable|exists:universities,id',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $photoPath = $request->file('photo')->store('profiles', $disk);
        }

        $universityId   = $validated['university_id'] ?? null;
        $universityName = 'UGB';
        if ($universityId) {
            $uni = University::find($universityId);
            $universityName = $uni ? $uni->short_name : 'UGB';
        }

        $profile = Profile::create([
            'user_id'        => $user->id,
            'age'            => $validated['age'],
            'gender'         => $validated['gender'],
            'ufr'            => $validated['ufr'],
            'level'          => $validated['level'],
            'bio'            => $validated['bio'],
            'promotion'      => $validated['promotion'] ?? null,
            'photo'          => $photoPath,
            'interests'      => $validated['interests'] ?? null,
            'field_of_study' => 'Non précisé',
            'university'     => $universityName,
            'university_id'  => $universityId,
        ]);

        // Badges Campus Queen / King
        if ($profile->gender === 'femme') {
            $realFemales = Profile::where('gender', 'femme')
                ->where(fn($q) => $q->whereNull('photo')->orWhere('photo', 'NOT LIKE', 'avatars/F%'))
                ->count();
            if ($realFemales <= 100) $profile->update(['badge' => 'queen']);
        } elseif ($profile->gender === 'homme') {
            $realMales = Profile::where('gender', 'homme')
                ->where(fn($q) => $q->whereNull('photo')->orWhere('photo', 'NOT LIKE', 'avatars/H%'))
                ->count();
            if ($realMales <= 50) $profile->update(['badge' => 'king']);
        }

        // Récompenser le parrain
        $referral = \App\Models\Referral::where('referred_id', $user->id)->where('rewarded', false)->first();
        if ($referral) {
            $referrerSub = \App\Models\Subscription::where('user_id', $referral->referrer_id)->latest()->first();
            if ($referrerSub) {
                $endDate = $referrerSub->ends_at && $referrerSub->ends_at->isFuture()
                    ? $referrerSub->ends_at : now();
                $referrerSub->update([
                    'ends_at'       => $endDate->addDays(7),
                    'trial_ends_at' => $referrerSub->status === 'trial'
                        ? ($referrerSub->trial_ends_at?->isFuture() ? $referrerSub->trial_ends_at->addDays(7) : now()->addDays(7))
                        : $referrerSub->trial_ends_at,
                ]);
            }
            $referral->update(['rewarded' => true, 'rewarded_at' => now()]);
        }

        return response()->json([
            'message' => 'Profil créé avec succès.',
            'profile' => $this->formatProfile($profile),
        ], 201);
    }

    public function update(Request $request): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profil introuvable.'], 404);
        }

        if ($request->filled('name')) {
            $request->validate(['name' => 'required|string|max:255']);
            $user->update(['name' => $request->name]);

            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $i = 1;
            while (\App\Models\User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $user->update(['slug' => $slug]);
        }

        $validated = $request->validate([
            'age'           => 'required|integer|min:18|max:60',
            'gender'        => 'required|in:homme,femme',
            'ufr'           => 'required|string|max:20',
            'level'         => 'required|string|in:L1,L2,L3,M1,M2,D1,D2,D3',
            'bio'           => 'nullable|string|max:200',
            'promotion'     => 'nullable|string|max:10',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'interests'     => 'nullable|string|max:500',
            'university_id' => 'nullable|exists:universities,id',
        ]);

        $data = [
            'age'       => $validated['age'],
            'gender'    => $validated['gender'],
            'ufr'       => $validated['ufr'],
            'level'     => $validated['level'],
            'bio'       => $validated['bio'],
            'promotion' => $validated['promotion'] ?? $profile->promotion,
            'interests' => $validated['interests'] ?? $profile->interests,
        ];

        if (isset($validated['university_id'])) {
            $data['university_id'] = $validated['university_id'];
            $uni = University::find($validated['university_id']);
            if ($uni) $data['university'] = $uni->short_name;
        }

        if ($request->hasFile('photo')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            if ($profile->photo) Storage::disk($disk)->delete($profile->photo);
            $data['photo'] = $request->file('photo')->store('profiles', $disk);
        }

        $profile->update($data);

        Cache::forget("profile_stats_{$user->id}");
        SwipeController::invalidateUserCaches($user->id);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'profile' => $this->formatProfile($profile->fresh()),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|current_password']);

        $user = $request->user();
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        if ($user->profile?->photo) Storage::disk($disk)->delete($user->profile->photo);

        SwipeController::invalidateUserCaches($user->id);
        Cache::forget("profile_stats_{$user->id}");
        Cache::forget("favorite_ufr_{$user->id}");

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Compte supprimé avec succès.']);
    }

    public function universities(): JsonResponse
    {
        $universities = Cache::remember('universities_active', 3600, fn() =>
            University::where('is_active', true)->orderBy('short_name')->get(['id', 'short_name', 'full_name'])
        );

        return response()->json($universities);
    }

    public function showUser(Request $request, int $id): JsonResponse
    {
        $currentUserId = $request->user()->id;

        $isSelf = $currentUserId === $id;
        $isMatched = Matche::forUser($currentUserId)
            ->where(function ($query) use ($currentUserId, $id) {
                $query->where(function ($sub) use ($currentUserId, $id) {
                    $sub->where('user1_id', $currentUserId)->where('user2_id', $id);
                })->orWhere(function ($sub) use ($currentUserId, $id) {
                    $sub->where('user1_id', $id)->where('user2_id', $currentUserId);
                });
            })
            ->exists();

        if (!$isSelf && !$isMatched) {
            return response()->json(['message' => 'Profil non accessible.'], 403);
        }

        $user = \App\Models\User::with('profile.universityModel')->find($id);

        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Profil introuvable.'], 404);
        }

        $profile = $user->profile;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'age' => $profile->age,
            'gender' => $profile->gender,
            'ufr' => $profile->ufr,
            'level' => $profile->level,
            'bio' => $profile->bio,
            'photo_url' => $profile->photo_url,
            'university' => $profile->university_name,
            'badge' => $profile->badge,
            'interests' => $profile->interests_array,
        ]);
    }

    private function formatProfile(Profile $profile): array
    {
        return [
            'id'            => $profile->id,
            'age'           => $profile->age,
            'gender'        => $profile->gender,
            'ufr'           => $profile->ufr,
            'promotion'     => $profile->promotion,
            'level'         => $profile->level,
            'bio'           => $profile->bio,
            'interests'     => $profile->interests_array,
            'photo_url'     => $profile->photo_url,
            'university'    => $profile->university_name,
            'university_id' => $profile->university_id,
            'badge'         => $profile->badge,
            'is_boosted'    => $profile->isBoosted(),
            'boosted_until' => $profile->boosted_until?->toISOString(),
            'last_seen_at'  => $profile->last_seen_at?->toISOString(),
        ];
    }

    private function formatUser($user): array
    {
        $profile = $user->profile;
        $sub     = $user->subscription;

        return [
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'slug'             => $user->slug,
            'referral_code'    => $user->referral_code,
            'streak_days'      => $user->streak_days,
            'streak_badge'     => $user->streak_badge,
            'ai_chat_unlocked' => $user->ai_chat_unlocked,
            'profile'          => $profile ? $this->formatProfile($profile) : null,
            'subscription'     => $sub ? [
                'status'        => $sub->status,
                'is_active'     => $sub->isActive(),
                'is_trial'      => $sub->isTrial(),
                'days_remaining' => $sub->daysRemaining(),
                'ends_at'       => ($sub->status === 'trial' ? $sub->trial_ends_at : $sub->ends_at)?->toISOString(),
            ] : null,
        ];
    }
}
