<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->referral_code) {
            $user->update([
                'referral_code' => strtoupper(substr(md5($user->id . $user->email . now()), 0, 8)),
            ]);
        }

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred.profile')
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'name'        => $r->referred->name,
                'photo_url'   => $r->referred->profile?->photo_url,
                'rewarded'    => $r->rewarded,
                'rewarded_at' => $r->rewarded_at?->toISOString(),
                'joined_at'   => $r->created_at->toISOString(),
            ]);

        $rewardedCount   = $referrals->where('rewarded', true)->count();
        $totalDaysEarned = $rewardedCount * 7;
        $referralLink    = url('/register?ref=' . $user->referral_code);

        return response()->json([
            'referral_code'    => $user->referral_code,
            'referral_link'    => $referralLink,
            'referrals'        => $referrals,
            'rewarded_count'   => $rewardedCount,
            'pending_count'    => $referrals->where('rewarded', false)->count(),
            'total_days_earned' => $totalDaysEarned,
        ]);
    }
}
