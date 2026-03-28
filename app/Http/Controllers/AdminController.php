<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Matche;
use App\Models\Message;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_today' => User::whereHas('profile', function ($q) {
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
        ];

        $recentUsers = User::with('profile', 'subscription')
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
}