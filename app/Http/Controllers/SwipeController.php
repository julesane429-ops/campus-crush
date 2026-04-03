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
use App\Services\WebPushService;

class SwipeController extends Controller
{
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

        $matches = Matche::forUser($user->id)->orderByDesc('created_at')->get();
        $matchesCount = $matches->count();
        $messagesCount = Message::whereIn('match_id', $matches->pluck('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')->count();
        $lastMatch = $matches->first();

        // Universités actives (pour le filtre)
        $universities = University::where('is_active', true)->orderBy('short_name')->get();

        return view('swipe.index', [
            'profilesForJs' => $profilesForJs,
            'user' => $user,
            'matchesCount' => $matchesCount,
            'messagesCount' => $messagesCount,
            'lastMatch' => $lastMatch,
            'universities' => $universities,
        ]);
    }

    private function getProfiles(array $filters = [])
    {
        $user = Auth::user();
        if (!$user || !$user->profile || !$user->profile->gender) {
            return collect([]);
        }

        $targetGender = $user->profile->gender === 'homme' ? 'femme' : 'homme';

        $liked = Like::where('user_id', $user->id)->pluck('liked_user_id');
        $passed = Pass::where('user_id', $user->id)->pluck('passed_user_id');
        $matched = Matche::forUser($user->id)->get()
            ->map(fn($m) => $m->user1_id === $user->id ? $m->user2_id : $m->user1_id);
        $excluded = $liked->merge($passed)->merge($matched)->unique();

        $query = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $excluded)
            ->whereHas('profile', function ($q) use ($targetGender, $filters) {
                $q->where('gender', $targetGender);
                if (!empty($filters['ufr'])) $q->where('ufr', $filters['ufr']);
                if (!empty($filters['promotion'])) $q->where('promotion', $filters['promotion']);
                if (!empty($filters['age_min'])) $q->where('age', '>=', (int) $filters['age_min']);
                if (!empty($filters['age_max'])) $q->where('age', '<=', (int) $filters['age_max']);
                // Filtre multi-université
                if (!empty($filters['university_id'])) {
                    $q->where('university_id', (int) $filters['university_id']);
                }
            })
            ->with('profile.universityModel')
            ->limit(30);

        $profiles = $query->get()
            ->filter(fn($c) => $c->profile !== null)
            ->sortByDesc(fn($c) => $this->compatibility->calculate($user, $c->profile))
            ->take(10);

        return $profiles->map(function ($candidate) use ($user) {
            $profile = $candidate->profile;
            return [
                'id' => $candidate->id,
                'name' => $candidate->name ?? '',
                'age' => $profile->age ?? '',
                'major' => $profile->ufr ?? '',
                'promotion' => $profile->promotion ?? '',
                'year' => $profile->level ?? '',
                'bio' => e($profile->bio ?? ''),
                'compatibility' => $this->compatibility->calculate($user, $profile),
                'tags' => $profile->interests_array,
                'photo' => $profile->photo_url,
                'university' => $profile->university_name,
                'gradient' => 'from-rose-300 to-purple-400',
                'badge' => $profile->badge,
            ];
        })->values();
    }

    public function loadMoreProfiles(Request $request)
    {
        $filters = $request->only(['ufr', 'promotion', 'age_min', 'age_max', 'university_id']);
        return response()->json($this->getProfiles($filters));
    }

    public function like(int $id)
    {
        $user = Auth::user();

        if ($user->id == $id) return response()->json(['error' => 'Action invalide'], 400);

        $targetUser = User::with('profile')->find($id);
        if (!$targetUser || !$targetUser->profile) return response()->json(['error' => 'Introuvable'], 404);
        if ($user->profile->gender === $targetUser->profile->gender) return response()->json(['error' => 'Invalide'], 400);

        Like::firstOrCreate(['user_id' => $user->id, 'liked_user_id' => $id]);

        $mutualLike = Like::where('user_id', $id)->where('liked_user_id', $user->id)->exists();

        if ($mutualLike) {
            $match = Matche::firstOrCreate([
                'user1_id' => min($user->id, $id),
                'user2_id' => max($user->id, $id),
            ]);

            // Push notification
            app(WebPushService::class)->notifyNewMatch($targetUser, Auth::user()->name);

            // 🔔 Notifications pour les deux utilisateurs
            $user->notify(new NewMatchNotification($match, $targetUser));
            $targetUser->notify(new NewMatchNotification($match, $user));

            // 🔥 Broadcast en temps réel
            try {
                broadcast(new NewMatch($match, $user));
                broadcast(new NewMatch($match, $targetUser));
            } catch (\Exception $e) {
                // Reverb pas lancé ? On continue quand même
            }

            return response()->json(['match' => true, 'match_id' => $match->id]);
        }

        return response()->json(['match' => false]);
    }

    public function pass(int $id)
    {
        $user = Auth::user();
        if ($user->id == $id) return response()->json(['error' => 'Invalide'], 400);
        Pass::firstOrCreate(['user_id' => $user->id, 'passed_user_id' => $id]);
        return response()->json(['pass' => true]);
    }

    public function navCounts()
    {
        $user = Auth::user();
        $matchIds = Matche::forUser($user->id)->pluck('id');
        $unread = Message::whereIn('match_id', $matchIds)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')->count();
        $notifCount = $user->unreadNotifications()->count();

        return response()->json([
            'matches' => $matchIds->count(),
            'messages' => $unread,
            'notifications' => $notifCount,
        ]);
    }
}
