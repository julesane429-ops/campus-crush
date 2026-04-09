<?php

namespace App\Http\Controllers;

use App\Models\Matche;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Si connecté → redirection
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return Auth::user()->hasProfile()
                ? redirect()->route('swipe')
                : redirect()->route('profile.create');
        }

        // Première visite → onboarding
        if (!$request->cookie('cc_visited')) {
            return response()
                ->view('onboarding')
                ->cookie('cc_visited', '1', 60 * 24 * 365);
        }

        // Stats mises en cache 10 minutes pour ne pas requêter à chaque page vue
        $stats = Cache::remember('landing_stats', 600, function () {
            $userCount   = User::count();
            $matchCount  = Matche::count();
            $univCount   = University::where('is_active', true)->count();

            return [
                'users'   => $this->formatCount($userCount),
                'matches' => $this->formatCount($matchCount),
                'univs'   => $univCount,
            ];
        });

        $universities = Cache::remember('landing_univs', 3600, function () {
            return University::where('is_active', true)
                ->orderBy('short_name')
                ->get(['id', 'name', 'short_name', 'city']);
        });

        $featuredReviews = \App\Models\Review::featured()
            ->with('user.profile')
            ->latest()
            ->take(6)
            ->get();

        return view('home', compact('stats', 'universities', 'featuredReviews'));
    }

    private function formatCount(int $n): string
    {
        if ($n >= 1000) return number_format(floor($n / 100) * 100, 0, ',', ' ') . '+';
        if ($n >= 100)  return floor($n / 10) * 10 . '+';
        if ($n >= 10)   return floor($n / 5) * 5 . '+';
        return $n . '+';
    }
}