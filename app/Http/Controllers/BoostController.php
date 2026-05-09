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
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) return redirect()->route('profile.create');

        $isBoosted = $profile->boosted_until && $profile->boosted_until->isFuture();
        $boostedUntil = $isBoosted ? $profile->boosted_until : null;
        return view('boost.index', compact('user', 'profile', 'isBoosted', 'boostedUntil'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number' => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        if (!$this->paydunya->isPaymentMethodEnabled($request->payment_method)) {
            return back()->withInput()->with('error', 'Wave est temporairement indisponible via PayDunya. Utilise Orange Money pour finaliser ton paiement.');
        }

        $user = Auth::user();
        if (!$user->profile) return redirect()->route('profile.create');

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $user->getOrCreateSubscription()->id,
            'amount' => 500,
            'payment_method' => $request->payment_method,
            'phone_number' => $request->phone_number,
            'transaction_id' => 'BOOST-' . strtoupper(Str::random(10)),
            'status' => 'pending',
            'notes' => 'boost_24h',
        ]);

        if (!empty(config('paydunya.master_key'))) {
            $result = $this->paydunya->payDirect(
                $user->id, $user->name, $user->email,
                $request->phone_number, $request->payment_method,
                500, 'Boost profil Campus Crush - 24h',
                route('boost.success'), route('boost.index'), 'boost'
            );

            if ($result['success']) {
                $payment->update(['transaction_id' => $result['token'], 'notes' => 'boost_24h|' . $result['method']]);

                if (in_array($result['method'], ['wave_redirect', 'om_redirect']) && $result['url']) {
                    return view('payment.waiting', [
                        'token'          => $result['token'],
                        'paymentMethod'  => $request->payment_method,
                        'amount'         => 500,
                        'method'         => $result['method'],
                        'softpayMessage' => null,
                        'redirectUrl'    => $result['url'],
                        'omUrl'          => $result['om_url']    ?? null,
                        'maxitUrl'       => $result['maxit_url'] ?? null,
                        'successUrl'     => route('boost.success', ['token' => $result['token']]),
                        'cancelUrl'      => route('boost.index'),
                    ]);
                }

                if ($result['method'] === 'free_ussd') {
                    return view('payment.waiting', [
                        'token' => $result['token'],
                        'paymentMethod' => $request->payment_method,
                        'amount' => 500,
                        'method' => 'free_ussd',
                        'softpayMessage' => $result['message'],
                        'redirectUrl' => null,
                        'successUrl' => route('boost.success', ['token' => $result['token']]),
                        'cancelUrl' => route('boost.index'),
                    ]);
                }

                if ($result['method'] === 'fallback_redirect' && $result['url']) {
                    return redirect()->away($result['url']);
                }
            }

            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return back()->with('error', 'Erreur de paiement : ' . $result['error']);
        }

        $payment->update(['status' => 'completed']);
        $this->activateBoost($user->id);
        return redirect()->route('boost.success')->with('success', 'Boost activé ! (simulation)');
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
        $profile = $user->fresh()->profile;
        $isBoosted = $profile->boosted_until && $profile->boosted_until->isFuture();
        $boostedUntil = $isBoosted ? $profile->boosted_until : null;
        return view('boost.success', compact('isBoosted', 'boostedUntil'));
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        $status = $data['data']['status'] ?? null;
        $userId = $data['data']['custom_data']['user_id'] ?? null;
        $token = $data['data']['invoice']['token'] ?? null;

        if ($status === 'completed') {
            $payment = Payment::where('transaction_id', $token)->where('user_id', $userId)->first();
            if ($payment && $payment->status !== 'completed') {
                $payment->update(['status' => 'completed']);
                $this->activateBoost($userId);
            }
        }
        return response()->json(['status' => 'ok']);
    }

    private function activateBoost(int $userId): void
    {
        $profile = \App\Models\Profile::where('user_id', $userId)->first();
        if ($profile) {
            $from = ($profile->boosted_until && $profile->boosted_until->isFuture()) ? $profile->boosted_until : now();
            $profile->update(['boosted_until' => $from->addHours(24)]);
        }
    }
}
