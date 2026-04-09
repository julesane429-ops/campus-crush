<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Payment;
use App\Services\PayDunyaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function __construct(
        private PayDunyaService $paydunya
    ) {}

    public function index()
    {
        $user = Auth::user();
        $subscription = $user->getOrCreateSubscription();
        $payments = $user->payments()->latest()->take(5)->get();
        $paydenyaConfigured = !empty(config('paydunya.master_key'));

        return view('subscription.index', compact('subscription', 'payments', 'paydenyaConfigured'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number' => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        $user = Auth::user();
        $subscription = $user->getOrCreateSubscription();

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => 1000,
            'payment_method' => $request->payment_method,
            'phone_number' => $request->phone_number,
            'transaction_id' => 'CC-' . strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        if (!empty(config('paydunya.master_key'))) {

            $result = $this->paydunya->payDirect(
                $user->id, $user->name, $user->email,
                $request->phone_number, $request->payment_method,
                1000, 'Abonnement Campus Crush - 1 mois',
                route('subscription.success'), route('subscription.cancel'),
                'subscription'
            );

            if ($result['success']) {
                $payment->update(['transaction_id' => $result['token'], 'notes' => 'softpay_' . $result['method']]);

                // Wave / Orange Money : rediriger vers l'app mobile
                if (in_array($result['method'], ['wave_redirect', 'om_redirect']) && $result['url']) {
                    return view('payment.waiting', [
                        'token'          => $result['token'],
                        'paymentMethod'  => $request->payment_method,
                        'amount'         => 1000,
                        'method'         => $result['method'],
                        'softpayMessage' => null,
                        'redirectUrl'    => $result['url'],
                        'omUrl'          => $result['om_url']    ?? null,
                        'maxitUrl'       => $result['maxit_url'] ?? null,
                        'successUrl'     => route('subscription.success', ['token' => $result['token']]),
                        'cancelUrl'      => route('subscription.index'),
                    ]);
                }

                // Free Money : afficher "tapez #150#"
                if ($result['method'] === 'free_ussd') {
                    return view('payment.waiting', [
                        'token' => $result['token'],
                        'paymentMethod' => $request->payment_method,
                        'amount' => 1000,
                        'method' => 'free_ussd',
                        'softpayMessage' => $result['message'],
                        'redirectUrl' => null,
                        'successUrl' => route('subscription.success', ['token' => $result['token']]),
                        'cancelUrl' => route('subscription.index'),
                    ]);
                }

                // Fallback : redirection page PayDunya classique
                if ($result['method'] === 'fallback_redirect' && $result['url']) {
                    return redirect()->away($result['url']);
                }
            }

            Log::warning('PayDunya payment failed', ['error' => $result['error']]);
            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return back()->with('error', 'Erreur de paiement : ' . $result['error']);
        }

        // MODE SIMULATION
        $payment->update(['status' => 'completed']);
        $subscription->activate($request->payment_method, $payment->transaction_id);
        return redirect()->route('subscription.success')->with('success', 'Paiement confirmé ! (simulation)');
    }

    public function success(Request $request)
    {
        $user = Auth::user();

        if ($token = $request->query('token')) {
            $result = $this->paydunya->checkPaymentStatus($token);
            if ($result['success'] && $result['status'] === 'completed') {
                $payment = Payment::where('transaction_id', $token)->first();
                if ($payment && $payment->status !== 'completed') {
                    $payment->update(['status' => 'completed', 'transaction_id' => $result['transaction_id'] ?? $token]);
                    $subscription = $user->getOrCreateSubscription();
                    $subscription->activate($payment->payment_method, $payment->transaction_id);
                }
            }
        }

        $subscription = $user->subscription;
        $lastPayment = $user->payments()->latest()->first();
        return view('subscription.confirm', compact('subscription', 'lastPayment'));
    }

    public function cancel()
    {
        return redirect()->route('subscription.index')->with('error', 'Paiement annulé. Vous pouvez réessayer.');
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('PayDunya IPN received', $data);

        if (!isset($data['data']['custom_data']['user_id'])) {
            return response()->json(['status' => 'error'], 400);
        }

        $status = $data['data']['status'] ?? null;
        $userId = $data['data']['custom_data']['user_id'];
        $invoiceToken = $data['data']['invoice']['token'] ?? null;

        if ($status === 'completed') {
            $payment = Payment::where('transaction_id', $invoiceToken)->where('user_id', $userId)->first();

            if ($payment && $payment->status !== 'completed') {
                $payment->update(['status' => 'completed', 'notes' => 'Confirmé par IPN']);

                $paymentType = $data['data']['custom_data']['type'] ?? 'subscription';

                if ($paymentType === 'ai_chat') {
                    \App\Models\User::where('id', $userId)->update(['ai_chat_unlocked' => true, 'ai_chat_unlocked_at' => now()]);
                } elseif ($paymentType === 'boost') {
                    $profile = \App\Models\Profile::where('user_id', $userId)->first();
                    if ($profile) {
                        $from = ($profile->boosted_until && $profile->boosted_until->isFuture()) ? $profile->boosted_until : now();
                        $profile->update(['boosted_until' => $from->addHours(24)]);
                    }
                } else {
                    $subscription = Subscription::where('user_id', $userId)->latest()->first();
                    $subscription?->activate($payment->payment_method, $data['data']['receipt_number'] ?? $invoiceToken);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}