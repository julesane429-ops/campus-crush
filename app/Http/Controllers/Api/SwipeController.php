<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SwipeController as BaseSwipeController;
use App\Models\Like;
use App\Models\Matche;
use App\Models\Message;
use App\Models\Pass;
use App\Models\University;
use App\Models\User;
use App\Notifications\NewMatchNotification;
use App\Services\CompatibilityService;
use App\Services\WebPushService;
use App\Events\NewMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SwipeController extends Controller
{
    private const PROFILES_TTL   = 300;
    private const NAV_COUNTS_TTL = 60;

    public function __construct(
        private CompatibilityService $compatibility
    ) {}

    public function profiles(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->profile) {
            return response()->json(['message' => 'Profil requis.'], 403);
        }

        $filters = $request->only(['ufr', 'promotion', 'age_min', 'age_max', 'university_id', 'university_only', 'levels']);

        if ($request->boolean('university_only') && $user->profile->university_id) {
            $filters['university_id'] = $user->profile->university_id;
        }

        if (!empty($filters['levels']) && is_string($filters['levels'])) {
            $filters['levels'] = array_filter(array_map('trim', explode(',', $filters['levels'])));
        }

        $hasFilters = !empty(array_filter($filters));

        $profiles = $hasFilters
            ? $this->fetchProfiles($user, $filters)
            : Cache::remember(
                "profiles_for_{$user->id}",
                self::PROFILES_TTL,
                fn() => $this->fetchProfiles($user, $filters)
            );

        $universities = Cache::remember('universities_active', 3600, fn() =>
            University::where('is_active', true)->orderBy('short_name')->get(['id', 'short_name'])
        );

        return response()->json([
            'profiles'     => $profiles,
            'universities' => $universities,
            'my_photo'     => $user->profile->photo_url,
        ]);
    }

    public function like(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->id === $id) {
            return response()->json(['error' => 'Action invalide.'], 400);
        }

        $targetUser = User::with('profile')->find($id);
        if (!$targetUser || !$targetUser->profile) {
            return response()->json(['error' => 'Utilisateur introuvable.'], 404);
        }
        if ($user->profile->gender === $targetUser->profile->gender) {
            return response()->json(['error' => 'Profil incompatible.'], 400);
        }

        Like::firstOrCreate(['user_id' => $user->id, 'liked_user_id' => $id]);

        BaseSwipeController::invalidateUserCaches($user->id);
        $this->compatibility->invalidateFavoriteUfrCache($user->id);

        $mutualLike = Like::where('user_id', $id)->where('liked_user_id', $user->id)->exists();

        if ($mutualLike) {
            $match = Matche::firstOrCreate([
                'user1_id' => min($user->id, $id),
                'user2_id' => max($user->id, $id),
            ]);

            BaseSwipeController::invalidateUserCaches($id);

            app(WebPushService::class)->notifyNewMatch($targetUser, $user->name);
            $user->notify(new NewMatchNotification($match, $targetUser));
            $targetUser->notify(new NewMatchNotification($match, $user));

            try {
                broadcast(new NewMatch($match, $user));
                broadcast(new NewMatch($match, $targetUser));
            } catch (\Exception) {}

            return response()->json([
                'match'    => true,
                'match_id' => $match->id,
                'other_user' => [
                    'id'        => $targetUser->id,
                    'name'      => $targetUser->name,
                    'photo_url' => $targetUser->profile->photo_url,
                ],
            ]);
        }

        return response()->json(['match' => false]);
    }

    public function pass(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->id === $id) {
            return response()->json(['error' => 'Action invalide.'], 400);
        }

        Pass::firstOrCreate(['user_id' => $user->id, 'passed_user_id' => $id]);
        BaseSwipeController::invalidateUserCaches($user->id);

        return response()->json(['pass' => true]);
    }

    public function undo(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        Like::where('user_id', $user->id)->where('liked_user_id', $id)->delete();
        Pass::where('user_id', $user->id)->where('passed_user_id', $id)->delete();
        BaseSwipeController::invalidateUserCaches($user->id);
        BaseSwipeController::invalidateUserCaches($id); // target's nav_counts.likes changes too

        return response()->json(['undone' => true]);
    }

    public function navCounts(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = Cache::remember("nav_counts_{$user->id}", self::NAV_COUNTS_TTL, function () use ($user) {
            $matchIds    = Matche::forUser($user->id)->pluck('id');
            $matchedIds  = Matche::forUser($user->id)->get()
                ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);
            $unread      = $matchIds->isEmpty() ? 0
                : Message::whereIn('match_id', $matchIds)
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();
            $likesCount  = Like::where('liked_user_id', $user->id)
                ->whereNotIn('user_id', $matchedIds)
                ->count();

            return [
                'matches'       => $matchIds->count(),
                'messages'      => $unread,
                'notifications' => $user->unreadNotifications()->count(),
                'likes'         => $likesCount,
            ];
        });

        return response()->json($data);
    }

    private function fetchProfiles(User $user, array $filters): array
    {
        $targetGender = $user->profile->gender === 'homme' ? 'femme' : 'homme';

        $liked   = Like::where('user_id', $user->id)->pluck('liked_user_id');
        $passed  = Pass::where('user_id', $user->id)->pluck('passed_user_id');
        $matched = Matche::forUser($user->id)->get()
            ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);

        $excluded = $liked->merge($passed)->merge($matched)->unique();

        $query = User::where('id', '!=', $user->id)
            ->where('is_admin', false)
            ->whereNotIn('id', $excluded)
            ->whereHas('profile', function ($q) use ($targetGender, $filters) {
                $q->where('gender', $targetGender);
                if (!empty($filters['ufr']))           $q->where('ufr', $filters['ufr']);
                if (!empty($filters['promotion']))     $q->where('promotion', $filters['promotion']);
                if (!empty($filters['age_min']))       $q->where('age', '>=', (int) $filters['age_min']);
                if (!empty($filters['age_max']))       $q->where('age', '<=', (int) $filters['age_max']);
                if (!empty($filters['university_id'])) $q->where('university_id', (int) $filters['university_id']);
                if (!empty($filters['levels'])) {
                    $levels = collect($filters['levels'])
                        ->flatMap(fn($level) => $level === 'Doctorat' ? ['D1', 'D2', 'D3'] : [$level])
                        ->unique()
                        ->values()
                        ->all();
                    $q->whereIn('level', $levels);
                }
            })
            ->with('profile.universityModel')
            ->limit(30);

        return $query->get()
            ->filter(fn($c) => $c->profile !== null)
            ->sortByDesc(fn($c) => [
                $c->profile->isBoosted() ? 1 : 0,
                $this->compatibility->calculate($user, $c->profile),
            ])
            ->take(10)
            ->map(function ($candidate) use ($user) {
                $profile = $candidate->profile;
                return [
                    'id'            => $candidate->id,
                    'name'          => $candidate->name ?? '',
                    'age'           => $profile->age ?? '',
                    'ufr'           => $profile->ufr ?? '',
                    'promotion'     => $profile->promotion ?? '',
                    'level'         => $profile->level ?? '',
                    'bio'           => $profile->bio ?? '',
                    'compatibility' => $this->compatibility->calculate($user, $profile),
                    'interests'     => $profile->interests_array,
                    'photo_url'     => $profile->photo_url,
                    'university'    => $profile->university_name,
                    'badge'         => $profile->badge,
                    'is_boosted'    => $profile->isBoosted(),
                ];
            })->values()->toArray();
    }
}
