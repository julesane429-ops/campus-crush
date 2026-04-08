<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PayDunyaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BoostController extends Controller
{
    public function __construct(
        private PayDunyaService $paydunya
    ) {}

    public function index()
    {
        $user    = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->route('profile.create');
        }

        $isBoosted   = $profile->boosted_until && $profile->boosted_until->isFuture();
        $boostedUntil = $isBoosted ? $profile->boosted_until : null;

        return view('boost.index', compact('user', 'profile', 'isBoosted', 'boostedUntil'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number'   => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        $user    = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->route('profile.create');
        }

        $payment = Payment::create([
            'user_id'        => $user->id,
            'subscription_id' => $user->getOrCreateSubscription()->id,
            'amount'         => 500,
            'payment_method' => $request->payment_method,
            'phone_number'   => $request->phone_number,
            'transaction_id' => 'BOOST-' . strtoupper(Str::random(10)),
            'status'         => 'pending',
            'notes'          => 'boost_24h',
        ]);

        // ── Tenter PayDunya si configuré ──
        if (!empty(config('paydunya.master_key'))) {

            $result = $this->paydunya->createBoostInvoice(
                $user->id,
                $user->name,
                $user->email,
                $request->phone_number
            );

            if ($result['success']) {
                $payment->update([
                    'transaction_id' => $result['token'],
                    'notes'          => 'boost_24h|paydunya_redirect',
                ]);
                return redirect()->away($result['url']);
            }

            Log::warning('PayDunya boost invoice failed', ['error' => $result['error']]);
            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return back()->with('error', 'Erreur de paiement : ' . $result['error']);
        }

        // ── MODE SIMULATION ──
        $payment->update(['status' => 'completed']);
        $this->activateBoost($user->id);

        return redirect()->route('boost.success')
            ->with('success', 'Boost activé ! Ton profil est en tête de file pendant 24h. (mode simulation)');
    }

    public function success(Request $request)
    {
        $user = Auth::user();

        if ($token = $request->query('token')) {
            $result = $this->paydunya->checkPaymentStatus($token);

            if ($result['success'] && $result['status'] === 'completed') {
                $payment = Payment::where('transaction_id', $token)->first();

                if ($payment && $payment->status !== 'completed') {
                    $payment->update(['status' => 'completed']);
                    $this->activateBoost($user->id);
                }
            }
        }

        $profile      = $user->fresh()->profile;
        $isBoosted    = $profile->boosted_until && $profile->boosted_until->isFuture();
        $boostedUntil = $isBoosted ? $profile->boosted_until : null;

        return view('boost.success', compact('isBoosted', 'boostedUntil'));
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('PayDunya Boost IPN received', $data);

        $status  = $data['data']['status'] ?? null;
        $userId  = $data['data']['custom_data']['user_id'] ?? null;
        $token   = $data['data']['invoice']['token'] ?? null;
        $notes   = $data['data']['custom_data']['type'] ?? null;

        if ($status === 'completed' && $notes === 'boost') {
            $payment = Payment::where('transaction_id', $token)
                ->where('user_id', $userId)
                ->first();

            if ($payment && $payment->status !== 'completed') {
                $payment->update(['status' => 'completed']);
                $this->activateBoost($userId);
                Log::info("Boost activated for user {$userId} via IPN");
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function activateBoost(int $userId): void
    {
        $profile = \App\Models\Profile::where('user_id', $userId)->first();
        if ($profile) {
            $from = ($profile->boosted_until && $profile->boosted_until->isFuture())
                ? $profile->boosted_until
                : now();

            $profile->update(['boosted_until' => $from->addHours(24)]);
        }
    }
}
