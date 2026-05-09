<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Like;
use App\Models\Matche;
use App\Models\Pass;
use App\Models\Message;
use App\Models\University;
use App\Services\CompatibilityService;
use App\Events\NewMatch;
use App\Notifications\NewMatchNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\WebPushService;

class SwipeController extends Controller
{
    // TTL du cache profils : 5 minutes
    // Invalidé dès qu'on like ou passe quelqu'un
    private const PROFILES_TTL   = 300;  // 5 min
    private const NAV_COUNTS_TTL = 60;   // 60 s

    public function __construct(
        private CompatibilityService $compatibility
    ) {}

    public function index()
    {
        $user = Auth::user();

        if (!$user->profile) {
            return redirect()->route('profile.create');
        }

        $profilesForJs = $this->getProfiles();

        // ── Compteurs du header — mis en cache 60 s ──────────────────────
        $counts = $this->getCachedNavCounts($user->id);

        $lastMatch = Matche::forUser($user->id)->orderByDesc('created_at')->first();

        $universities = Cache::remember('universities_active', 3600, function () {
            return University::where('is_active', true)->orderBy('short_name')->get();
        });

        return view('swipe.index', [
            'profilesForJs' => $profilesForJs,
            'user'          => $user,
            'matchesCount'  => $counts['matches'],
            'messagesCount' => $counts['messages'],
            'lastMatch'     => $lastMatch,
            'universities'  => $universities,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // PROFILES — avec cache 5 min, invalidé sur like/pass
    // ────────────────────────────────────────────────────────────────────
    private function getProfiles(array $filters = [])
    {
        $user = Auth::user();
        if (!$user || !$user->profile || !$user->profile->gender) {
            return collect([]);
        }

        // Si des filtres sont actifs, on ne met pas en cache
        // (les filtres sont rares et changent les résultats)
        $hasFilters = !empty(array_filter($filters));

        if ($hasFilters) {
            return $this->fetchProfiles($user, $filters);
        }

        return Cache::remember(
            "profiles_for_{$user->id}",
            self::PROFILES_TTL,
            fn () => $this->fetchProfiles($user, $filters)
        );
    }

    private function fetchProfiles(User $user, array $filters): \Illuminate\Support\Collection
    {
        $targetGender = $user->profile->gender === 'homme' ? 'femme' : 'homme';

        // Récupérer les IDs exclus en une seule requête groupée
        $liked   = Like::where('user_id', $user->id)->pluck('liked_user_id');
        $passed  = Pass::where('user_id', $user->id)->pluck('passed_user_id');
        $matched = Matche::forUser($user->id)->get()
            ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);

        $excluded = $liked->merge($passed)->merge($matched)->unique();

        $query = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $excluded)
            ->whereHas('profile', function ($q) use ($targetGender, $filters) {
                $q->where('gender', $targetGender);
                if (!empty($filters['ufr']))           $q->where('ufr', $filters['ufr']);
                if (!empty($filters['promotion']))     $q->where('promotion', $filters['promotion']);
                if (!empty($filters['age_min']))       $q->where('age', '>=', (int) $filters['age_min']);
                if (!empty($filters['age_max']))       $q->where('age', '<=', (int) $filters['age_max']);
                if (!empty($filters['university_id'])) $q->where('university_id', (int) $filters['university_id']);
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
                    'major'         => $profile->ufr ?? '',
                    'promotion'     => $profile->promotion ?? '',
                    'year'          => $profile->level ?? '',
                    'bio'           => $profile->bio ?? '',
                    'compatibility' => $this->compatibility->calculate($user, $profile),
                    'tags'          => $profile->interests_array,
                    'photo'         => $profile->photo_url,
                    'university'    => $profile->university_name,
                    'gradient'      => 'from-rose-300 to-purple-400',
                    'badge'         => $profile->badge,
                    'boosted'       => $profile->isBoosted(),
                ];
            })->values();
    }

    public function loadMoreProfiles(Request $request)
    {
        $filters = $request->only(['ufr', 'promotion', 'age_min', 'age_max', 'university_id']);
        return response()->json($this->getProfiles($filters));
    }

    // ────────────────────────────────────────────────────────────────────
    // LIKE — invalide le cache profils + nav badges
    // ────────────────────────────────────────────────────────────────────
    public function like(int $id)
    {
        $user = Auth::user();

        if ($user->id == $id) return response()->json(['error' => 'Action invalide'], 400);

        $targetUser = User::with('profile')->find($id);
        if (!$targetUser || !$targetUser->profile) return response()->json(['error' => 'Introuvable'], 404);
        if ($user->profile->gender === $targetUser->profile->gender) return response()->json(['error' => 'Invalide'], 400);

        Like::firstOrCreate(['user_id' => $user->id, 'liked_user_id' => $id]);

        // Invalider tous les caches concernés
        $this->invalidateUserCaches($user->id);
        $this->compatibility->invalidateFavoriteUfrCache($user->id);

        $mutualLike = Like::where('user_id', $id)->where('liked_user_id', $user->id)->exists();

        if ($mutualLike) {
            $match = Matche::firstOrCreate([
                'user1_id' => min($user->id, $id),
                'user2_id' => max($user->id, $id),
            ]);

            // Invalider aussi le cache de l'autre utilisateur
            $this->invalidateUserCaches($id);

            app(WebPushService::class)->notifyNewMatch($targetUser, Auth::user()->name);
            $user->notify(new NewMatchNotification($match, $targetUser));
            $targetUser->notify(new NewMatchNotification($match, $user));

            try {
                broadcast(new NewMatch($match, $user));
                broadcast(new NewMatch($match, $targetUser));
            } catch (\Exception $e) {
                // Reverb pas lancé
            }

            return response()->json(['match' => true, 'match_id' => $match->id]);
        }

        return response()->json(['match' => false]);
    }

    // ────────────────────────────────────────────────────────────────────
    // PASS — invalide le cache profils
    // ────────────────────────────────────────────────────────────────────
    public function pass(int $id)
    {
        $user = Auth::user();
        if ($user->id == $id) return response()->json(['error' => 'Invalide'], 400);
        Pass::firstOrCreate(['user_id' => $user->id, 'passed_user_id' => $id]);
        $this->invalidateUserCaches($user->id);
        return response()->json(['pass' => true]);
    }

    // ────────────────────────────────────────────────────────────────────
    // UNDO — annule le dernier like ou pass sur un profil
    // ────────────────────────────────────────────────────────────────────
    public function undo(int $id)
    {
        $user = Auth::user();
        Like::where('user_id', $user->id)->where('liked_user_id', $id)->delete();
        Pass::where('user_id', $user->id)->where('passed_user_id', $id)->delete();
        $this->invalidateUserCaches($user->id);
        $this->invalidateUserCaches($id);
        return response()->json(['undone' => true]);
    }

    // ────────────────────────────────────────────────────────────────────
    // NAV COUNTS — mis en cache 60 s, évite 3 requêtes / page
    // ────────────────────────────────────────────────────────────────────
    public function navCounts()
    {
        $user = Auth::user();
        $data = $this->getCachedNavCounts($user->id);
        return response()->json($data);
    }

    // ────────────────────────────────────────────────────────────────────
    // HELPERS
    // ────────────────────────────────────────────────────────────────────
    private function getCachedNavCounts(int $userId): array
    {
        return Cache::remember("nav_counts_{$userId}", self::NAV_COUNTS_TTL, function () use ($userId) {
            $matchIds  = Matche::forUser($userId)->pluck('id');
            $unread    = $matchIds->isEmpty() ? 0
                : Message::whereIn('match_id', $matchIds)
                    ->where('sender_id', '!=', $userId)
                    ->whereNull('read_at')
                    ->count();

            return [
                'matches'  => $matchIds->count(),
                'messages' => $unread,
            ];
        });
    }

    /**
     * Invalide tous les caches liés à un utilisateur.
     * À appeler après like, pass, nouveau match ou message lu.
     */
    public static function invalidateUserCaches(int $userId): void
    {
        Cache::forget("profiles_for_{$userId}");
        Cache::forget("nav_counts_{$userId}");
        Cache::forget("nav_badges_{$userId}");
        Cache::forget("likes_for_{$userId}");
        Cache::forget("matches_for_{$userId}");
    }
}
