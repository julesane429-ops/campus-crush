<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    /**
     * Page parrainage de l'utilisateur connecté.
     */
    public function index()
    {
        $user = Auth::user();

        // Générer un code si l'utilisateur n'en a pas encore
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => strtoupper(substr(md5($user->id . $user->email . now()), 0, 8)),
            ]);
        }

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred.profile')
            ->latest()
            ->get();

        $rewardedCount   = $referrals->where('rewarded', true)->count();
        $pendingCount    = $referrals->where('rewarded', false)->count();
        $totalDaysEarned = $rewardedCount * 7; // 7 jours par filleul

        $referralLink = url('/register?ref=' . $user->referral_code);

        return view('referral.index', compact(
            'user',
            'referrals',
            'rewardedCount',
            'pendingCount',
            'totalDaysEarned',
            'referralLink'
        ));
    }
}
