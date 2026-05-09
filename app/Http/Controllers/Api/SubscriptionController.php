<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PayDunyaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function __construct(
        private PayDunyaService $paydunya
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user         = $request->user();
        $subscription = $user->getOrCreateSubscription();
        $payments     = $user->payments()->latest()->take(5)->get(['id', 'amount', 'payment_method', 'status', 'created_at']);

        return response()->json([
            'subscription' => [
                'status'         => $subscription->status,
                'is_active'      => $subscription->isActive(),
                'is_trial'       => $subscription->isTrial(),
                'days_remaining' => $subscription->daysRemaining(),
                'ends_at'        => ($subscription->status === 'trial' ? $subscription->trial_ends_at : $subscription->ends_at)?->toISOString(),
            ],
            'payments' => $payments,
        ]);
    }

    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number'   => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        if (!$this->paydunya->isPaymentMethodEnabled($request->payment_method)) {
            return response()->json(['message' => 'Wave est temporairement indisponible via PayDunya. Utilise Orange Money pour finaliser ton paiement.'], 422);
        }

        $user         = $request->user();
        $subscription = $user->getOrCreateSubscription();

        $payment = Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'amount'          => 1000,
            'payment_method'  => $request->payment_method,
            'phone_number'    => $request->phone_number,
            'transaction_id'  => 'CC-' . strtoupper(Str::random(10)),
            'status'          => 'pending',
        ]);

        if (!empty(config('paydunya.master_key'))) {
            $result = $this->paydunya->payDirect(
                $user->id, $user->name, $user->email,
                $request->phone_number, $request->payment_method,
                1000, 'Abonnement Campus Crush - 1 mois',
                config('app.url') . '/api/subscription/success',
                config('app.url') . '/api/subscription/cancel',
                'subscription'
            );

            if ($result['success']) {
                $payment->update(['transaction_id' => $result['token'], 'notes' => 'softpay_' . $result['method']]);

                return response()->json([
                    'token'  => $result['token'],
                    'method' => $result['method'],
                    'url'    => $result['url'] ?? null,
                    'om_url' => $result['om_url'] ?? null,
                    'maxit_url' => $result['maxit_url'] ?? null,
                    'message' => $result['message'] ?? null,
                ]);
            }

            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return response()->json(['message' => 'Erreur de paiement : ' . $result['error']], 422);
        }

        // Mode simulation
        $payment->update(['status' => 'completed']);
        $subscription->activate($request->payment_method, $payment->transaction_id);

        return response()->json([
            'method'  => 'simulation',
            'message' => 'Abonnement activé (mode simulation)',
            'token'   => $payment->transaction_id,
        ]);
    }

    public function check(Request $request, string $token): JsonResponse
    {
        $result = $this->paydunya->checkPaymentStatus($token);

        if ($result['success'] && $result['status'] === 'completed') {
            $payment = Payment::where('transaction_id', $token)->first();
            if ($payment && $payment->status !== 'completed') {
                $payment->update(['status' => 'completed']);
                $userId = $payment->user_id;
                $notes  = $payment->notes ?? '';

                if (str_contains($notes, 'ai_chat')) {
                    \App\Models\User::find($userId)?->update(['ai_chat_unlocked' => true, 'ai_chat_unlocked_at' => now()]);
                } elseif (str_contains($notes, 'boost')) {
                    $profile = \App\Models\Profile::where('user_id', $userId)->first();
                    if ($profile) {
                        $from = ($profile->boosted_until && $profile->boosted_until->isFuture()) ? $profile->boosted_until : now();
                        $profile->update(['boosted_until' => $from->addHours(24)]);
                    }
                } else {
                    $sub = \App\Models\Subscription::where('user_id', $userId)->latest()->first();
                    $sub?->activate($payment->payment_method, $token);
                }
            }
            return response()->json(['status' => 'completed']);
        }

        return response()->json(['status' => $result['status'] ?? 'pending']);
    }
}
