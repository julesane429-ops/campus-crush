<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Matche;
use App\Models\Message;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Review;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => $this->realUsersQuery()->count(),
            'active_today' => $this->realUsersQuery()->whereHas('profile', function ($q) {
                $q->where('last_seen_at', '>=', now()->subDay());
            })->count(),
            'total_matches' => Matche::count(),
            'total_messages' => Message::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'active_subscriptions' => Subscription::whereIn('status', ['trial', 'active'])
                ->where(function ($q) {
                    $q->where('ends_at', '>', now())
                      ->orWhere('trial_ends_at', '>', now());
                })->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'revenue_month' => Payment::where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'men_count' => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('gender', 'homme'))->count(),
            'women_count' => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('gender', 'femme'))->count(),
            'men_online' => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('gender', 'homme')->where('last_seen_at', '>=', now()->subMinutes(2)))->count(),
            'women_online' => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('gender', 'femme')->where('last_seen_at', '>=', now()->subMinutes(2)))->count(),
        ];

        $recentUsers = $this->realUsersQuery()->with('profile', 'subscription')
            ->latest()->take(10)->get();

        $pendingReports = Report::with(['reporter', 'reportedUser'])
            ->where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'pendingReports'));
    }

    public function users(Request $request)
    {
        $query = User::with('profile', 'subscription');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filter === 'banned') {
            $query->where('is_banned', true);
        } elseif ($request->filter === 'active') {
            $query->where('is_banned', false);
        } elseif ($request->filter === 'no_profile') {
            $query->doesntHave('profile');
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function banUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de bannir un administrateur.');
        }
        $user->update([
            'is_banned' => true,
            'ban_reason' => $request->reason ?? 'Banni par l\'administrateur',
        ]);
        return back()->with('success', $user->name . ' a été banni.');
    }

    public function unbanUser(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => false, 'ban_reason' => null]);
        return back()->with('success', $user->name . ' a été débanni.');
    }

    public function deleteUser(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de supprimer un administrateur.');
        }
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé.');
    }

    public function reports(Request $request)
    {
        $query = Report::with(['reporter.profile', 'reportedUser.profile']);
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $reports = $query->latest()->paginate(20);
        return view('admin.reports', compact('reports'));
    }

    public function resolveReport(Request $request, int $id)
    {
        $report = Report::findOrFail($id);
        if ($request->action === 'ban') {
            $report->reportedUser->update([
                'is_banned' => true,
                'ban_reason' => 'Banni suite au signalement #' . $report->id,
            ]);
            $report->update(['status' => 'resolved']);
            return back()->with('success', 'Utilisateur banni et signalement résolu.');
        }
        $report->update(['status' => 'reviewed']);
        return back()->with('success', 'Signalement marqué comme examiné.');
    }

    public function payments(Request $request)
    {
        $payments = Payment::with('user')->latest()->paginate(20);
        return view('admin.payments', compact('payments'));
    }

    /**
     * Page analytics avec graphiques.
     */
    public function analytics()
    {
        $days = 30; // Fenêtre d'analyse : 30 derniers jours

        // ── Inscriptions par jour (30j) ──────────────────────────────────
        $registrations = DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // ── Matchs par jour (30j) ────────────────────────────────────────
        $matchesByDay = DB::table('matches')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // ── Revenus par jour (30j) ───────────────────────────────────────
        $revenueByDay = DB::table('payments')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // ── Remplir tous les jours (même ceux sans données) ─────────────
        $labels       = [];
        $regData      = [];
        $matchData    = [];
        $revenueData  = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date       = now()->subDays($i)->toDateString();
            $labels[]   = Carbon::parse($date)->format('d/m');
            $regData[]  = $registrations->get($date)?->total ?? 0;
            $matchData[] = $matchesByDay->get($date)?->total ?? 0;
            $revenueData[] = $revenueByDay->get($date)?->total ?? 0;
        }

        // ── Répartition par université ───────────────────────────────────
        $byUniversity = DB::table('profiles')
            ->select('university', DB::raw('COUNT(*) as total'))
            ->whereNotNull('university')
            ->groupBy('university')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Répartition par UFR ──────────────────────────────────────────
        $byUfr = DB::table('profiles')
            ->select('ufr', DB::raw('COUNT(*) as total'))
            ->whereNotNull('ufr')
            ->groupBy('ufr')
            ->orderByDesc('total')
            ->get();

        // ── KPIs globaux ─────────────────────────────────────────────────
        $kpis = [
            'total_users'       => $this->realUsersQuery()->count(),
            'users_this_week'   => $this->realUsersQuery()->where('created_at', '>=', now()->subWeek())->count(),
            'users_this_month'  => $this->realUsersQuery()->where('created_at', '>=', now()->startOfMonth())->count(),
            'total_matches'     => DB::table('matches')->count(),
            'matches_this_week' => DB::table('matches')->where('created_at', '>=', now()->subWeek())->count(),
            'match_rate'        => DB::table('users')->count() > 0
                ? round((DB::table('matches')->count() / DB::table('users')->count()) * 100, 1)
                : 0,
            'total_revenue'     => DB::table('payments')->where('status', 'completed')->sum('amount'),
            'revenue_this_month' => DB::table('payments')->where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'paying_users'      => DB::table('payments')->where('status', 'completed')->distinct('user_id')->count('user_id'),
            'women_count'       => DB::table('profiles')->where('gender', 'femme')->count(),
            'men_count'         => DB::table('profiles')->where('gender', 'homme')->count(),
            'boosted_now'       => DB::table('profiles')->where('boosted_until', '>', now())->count(),
            'referrals_total'   => DB::table('referrals')->count(),
            'referrals_rewarded' => DB::table('referrals')->where('rewarded', true)->count(),
        ];

        return view('admin.analytics', compact(
            'labels',
            'regData',
            'matchData',
            'revenueData',
            'byUniversity',
            'byUfr',
            'kpis',
            'days'
        ));
    }

    public function reviews(Request $request)
    {
        $query = Review::with('user.profile');
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $reviews = $query->latest()->paginate(20);
        return view('admin.reviews', compact('reviews'));
    }

    public function approveReview(int $id)
    {
        Review::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', 'Avis approuvé.');
    }

    public function rejectReview(int $id)
    {
        Review::findOrFail($id)->update(['status' => 'rejected', 'is_featured' => false]);
        return back()->with('success', 'Avis rejeté.');
    }

    public function featureReview(int $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_featured' => !$review->is_featured]);
        return back()->with('success', $review->is_featured ? 'Mis en avant.' : 'Retiré.');
    }

    public function deleteReview(int $id)
    {
        Review::findOrFail($id)->delete();
        return back()->with('success', 'Avis supprimé.');
    }

    /**
     * Scope pour exclure les utilisateurs seedés (faux profils).
     */
    private function realUsersQuery()
    {
        return User::whereHas('profile', function ($q) {
            $q->where(function ($q2) {
                $q2->whereNull('photo')
                    ->orWhere(function ($q3) {
                        $q3->where('photo', 'NOT LIKE', 'avatars/F%')
                            ->where('photo', 'NOT LIKE', 'avatars/H%');
                    });
            });
        });
    }
}
