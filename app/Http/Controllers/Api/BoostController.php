<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Profile;
use App\Services\PayDunyaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BoostController extends Controller
{
    public function __construct(
        private PayDunyaService $paydunya
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profil requis.'], 403);
        }

        return response()->json([
            'is_boosted'    => $profile->isBoosted(),
            'boosted_until' => $profile->boosted_until?->toISOString(),
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

        $user = $request->user();

        if (!$user->profile) {
            return response()->json(['message' => 'Profil requis.'], 403);
        }

        $payment = Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $user->getOrCreateSubscription()->id,
            'amount'          => 500,
            'payment_method'  => $request->payment_method,
            'phone_number'    => $request->phone_number,
            'transaction_id'  => 'BOOST-' . strtoupper(Str::random(10)),
            'status'          => 'pending',
            'notes'           => 'boost_24h',
        ]);

        if (!empty(config('paydunya.master_key'))) {
            $result = $this->paydunya->payDirect(
                $user->id, $user->name, $user->email,
                $request->phone_number, $request->payment_method,
                500, 'Boost profil Campus Crush - 24h',
                config('app.url') . '/api/boost/success',
                config('app.url') . '/api/boost',
                'boost'
            );

            if ($result['success']) {
                $payment->update(['transaction_id' => $result['token'], 'notes' => 'boost_24h|' . $result['method']]);

                return response()->json([
                    'token'     => $result['token'],
                    'method'    => $result['method'],
                    'url'       => $result['url'] ?? null,
                    'om_url'    => $result['om_url'] ?? null,
                    'maxit_url' => $result['maxit_url'] ?? null,
                    'message'   => $result['message'] ?? null,
                ]);
            }

            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return response()->json(['message' => 'Erreur de paiement : ' . $result['error']], 422);
        }

        // Mode simulation
        $payment->update(['status' => 'completed']);
        $this->activateBoost($user->id);
        $profile = $user->fresh()->profile;

        return response()->json([
            'method'        => 'simulation',
            'message'       => 'Boost activé (mode simulation)',
            'is_boosted'    => true,
            'boosted_until' => $profile->boosted_until?->toISOString(),
        ]);
    }

    private function activateBoost(int $userId): void
    {
        $profile = Profile::where('user_id', $userId)->first();
        if ($profile) {
            $from = ($profile->boosted_until && $profile->boosted_until->isFuture()) ? $profile->boosted_until : now();
            $profile->update(['boosted_until' => $from->addHours(24)]);
        }
    }
}
