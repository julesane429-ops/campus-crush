<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matche;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_users'           => $this->realUsersQuery()->count(),
            'active_today'          => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('last_seen_at', '>=', now()->subDay()))->count(),
            'total_matches'         => Matche::count(),
            'total_messages'        => Message::count(),
            'pending_reports'       => Report::where('status', 'pending')->count(),
            'banned_users'          => User::where('is_banned', true)->count(),
            'active_subscriptions'  => Subscription::whereIn('status', ['trial', 'active'])
                ->where(fn($q) => $q->where('ends_at', '>', now())->orWhere('trial_ends_at', '>', now()))
                ->count(),
            'total_revenue'         => Payment::where('status', 'completed')->sum('amount'),
            'revenue_month'         => Payment::where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'men_count'             => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('gender', 'homme'))->count(),
            'women_count'           => $this->realUsersQuery()->whereHas('profile', fn($q) => $q->where('gender', 'femme'))->count(),
        ];

        $recentUsers = $this->realUsersQuery()->with('profile', 'subscription')->latest()->take(10)->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'photo_url' => $u->profile?->photo_url,
                'is_banned' => $u->is_banned,
                'sub_status' => $u->subscription?->status,
                'joined_at' => $u->created_at->toISOString(),
            ]);

        $pendingReports = Report::with(['reporter', 'reportedUser'])->where('status', 'pending')->latest()->take(5)->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'reporter_name'   => $r->reporter?->name,
                'reported_name'   => $r->reportedUser?->name,
                'reason'          => $r->reason,
                'created_at'      => $r->created_at->toISOString(),
            ]);

        return response()->json(compact('stats', 'recentUsers', 'pendingReports'));
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::with('profile', 'subscription');

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filter === 'banned') $query->where('is_banned', true);
        elseif ($request->filter === 'active') $query->where('is_banned', false);
        elseif ($request->filter === 'no_profile') $query->doesntHave('profile');

        $users = $query->latest()->paginate(20);

        return response()->json([
            'data'       => $users->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'photo_url' => $u->profile?->photo_url,
                'is_banned' => $u->is_banned,
                'is_admin'  => $u->is_admin,
                'sub_status' => $u->subscription?->status,
                'gender'    => $u->profile?->gender,
                'university' => $u->profile?->university_name,
                'joined_at' => $u->created_at->toISOString(),
                'last_seen' => $u->profile?->last_seen_at?->toISOString(),
            ]),
            'total'      => $users->total(),
            'per_page'   => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page'  => $users->lastPage(),
        ]);
    }

    public function banUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) return response()->json(['message' => 'Impossible de bannir un administrateur.'], 403);
        $user->update(['is_banned' => true, 'ban_reason' => $request->reason ?? 'Violation des CGU']);
        return response()->json(['message' => 'Utilisateur banni.']);
    }

    public function unbanUser(int $id): JsonResponse
    {
        User::findOrFail($id)->update(['is_banned' => false, 'ban_reason' => null]);
        return response()->json(['message' => 'Utilisateur débanni.']);
    }

    public function deleteUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) return response()->json(['message' => 'Impossible de supprimer un administrateur.'], 403);
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    public function reports(Request $request): JsonResponse
    {
        $reports = Report::with(['reporter.profile', 'reportedUser.profile'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20);

        return response()->json([
            'data' => $reports->map(fn($r) => [
                'id'            => $r->id,
                'reporter'      => ['id' => $r->reporter?->id, 'name' => $r->reporter?->name, 'photo_url' => $r->reporter?->profile?->photo_url],
                'reported_user' => ['id' => $r->reportedUser?->id, 'name' => $r->reportedUser?->name, 'photo_url' => $r->reportedUser?->profile?->photo_url],
                'reason'        => $r->reason,
                'status'        => $r->status,
                'created_at'    => $r->created_at->toISOString(),
            ]),
            'total' => $reports->total(),
        ]);
    }

    public function resolveReport(int $id): JsonResponse
    {
        $report = Report::findOrFail($id);
        $report->update(['status' => 'resolved', 'resolved_at' => now()]);
        return response()->json(['message' => 'Signalement résolu.']);
    }

    public function payments(Request $request): JsonResponse
    {
        $payments = Payment::with('user')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20);

        return response()->json([
            'data' => $payments->map(fn($p) => [
                'id'             => $p->id,
                'user_name'      => $p->user?->name,
                'amount'         => $p->amount,
                'payment_method' => $p->payment_method,
                'status'         => $p->status,
                'created_at'     => $p->created_at->toISOString(),
            ]),
            'total' => $payments->total(),
        ]);
    }

    public function analytics(): JsonResponse
    {
        $days = 30;
        $labels = collect(range($days - 1, 0))->map(fn($i) => now()->subDays($i)->format('d/m'));

        $signups = DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $matchesByDay = DB::table('matches')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $revenueByDay = DB::table('payments')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $dateRange = collect(range($days - 1, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));

        return response()->json([
            'labels'        => $labels,
            'signups'       => $dateRange->map(fn($d) => $signups->get($d, 0))->values(),
            'matches'       => $dateRange->map(fn($d) => $matchesByDay->get($d, 0))->values(),
            'revenue'       => $dateRange->map(fn($d) => $revenueByDay->get($d, 0))->values(),
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $reviews = Review::with('user.profile')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20);

        return response()->json([
            'data' => $reviews->map(fn($r) => [
                'id'         => $r->id,
                'user_name'  => $r->user?->name,
                'photo_url'  => $r->user?->profile?->photo_url,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'status'     => $r->status,
                'is_featured' => $r->is_featured ?? false,
                'created_at' => $r->created_at->toISOString(),
            ]),
            'total' => $reviews->total(),
        ]);
    }

    public function approveReview(int $id): JsonResponse
    {
        Review::findOrFail($id)->update(['status' => 'approved']);
        return response()->json(['message' => 'Avis approuvé.']);
    }

    public function rejectReview(int $id): JsonResponse
    {
        Review::findOrFail($id)->update(['status' => 'rejected']);
        return response()->json(['message' => 'Avis rejeté.']);
    }

    public function featureReview(int $id): JsonResponse
    {
        Review::findOrFail($id)->update(['is_featured' => true, 'status' => 'approved']);
        return response()->json(['message' => 'Avis mis en avant.']);
    }

    public function deleteReview(int $id): JsonResponse
    {
        Review::findOrFail($id)->delete();
        return response()->json(['message' => 'Avis supprimé.']);
    }

    private function realUsersQuery()
    {
        return User::where('is_admin', false);
    }
}
