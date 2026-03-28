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

    /**
     * Page d'abonnement.
     */
    public function index()
    {
        $user = Auth::user();
        $subscription = $user->getOrCreateSubscription();
        $payments = $user->payments()->latest()->take(5)->get();
        $paydenyaConfigured = !empty(config('paydunya.master_key'));

        return view('subscription.index', compact('subscription', 'payments', 'paydenyaConfigured'));
    }

    /**
     * Lancer le paiement.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number' => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        $user = Auth::user();
        $subscription = $user->getOrCreateSubscription();

        // Créer le paiement en attente
        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => 1000,
            'payment_method' => $request->payment_method,
            'phone_number' => $request->phone_number,
            'transaction_id' => 'CC-' . strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        // ── Tenter PayDunya si configuré ──
        if (!empty(config('paydunya.master_key'))) {

            $result = $this->paydunya->createInvoice(
                $user->id,
                $user->name,
                $user->email
            );

            if ($result['success']) {
                // Sauvegarder le token PayDunya pour vérification ultérieure
                $payment->update([
                    'transaction_id' => $result['token'],
                    'notes' => 'paydunya_redirect',
                ]);

                // Rediriger vers la page de paiement PayDunya
                return redirect()->away($result['url']);
            }

            // Échec de création de facture PayDunya
            Log::warning('PayDunya invoice creation failed', ['error' => $result['error']]);
            $payment->update(['status' => 'failed', 'notes' => $result['error']]);

            return back()->with('error', 'Erreur de paiement : ' . $result['error']);
        }

        // ── MODE SIMULATION (pas de clés PayDunya) ──
        $payment->update(['status' => 'completed']);
        $subscription->activate($request->payment_method, $payment->transaction_id);

        return redirect()->route('subscription.success')
            ->with('success', 'Paiement de 1 000 FCFA confirmé ! (mode simulation)');
    }

    /**
     * Retour après paiement réussi sur PayDunya.
     * PayDunya redirige ici avec ?token=xxx
     */
    public function success(Request $request)
    {
        $user = Auth::user();

        // Si c'est un retour PayDunya avec token
        if ($token = $request->query('token')) {
            $result = $this->paydunya->checkPaymentStatus($token);

            if ($result['success'] && $result['status'] === 'completed') {
                // Trouver le paiement correspondant
                $payment = Payment::where('transaction_id', $token)->first();

                if ($payment && $payment->status !== 'completed') {
                    $payment->update([
                        'status' => 'completed',
                        'transaction_id' => $result['transaction_id'] ?? $token,
                    ]);

                    $subscription = $user->getOrCreateSubscription();
                    $subscription->activate($payment->payment_method, $payment->transaction_id);
                }
            }
        }

        $subscription = $user->subscription;
        $lastPayment = $user->payments()->latest()->first();

        return view('subscription.confirm', compact('subscription', 'lastPayment'));
    }

    /**
     * Retour après annulation sur PayDunya.
     */
    public function cancel()
    {
        return redirect()->route('subscription.index')
            ->with('error', 'Paiement annulé. Vous pouvez réessayer.');
    }

    /**
     * Webhook IPN PayDunya - appelé par PayDunya après confirmation du paiement.
     * C'est le plus fiable car c'est serveur-à-serveur.
     */
    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('PayDunya IPN received', $data);

        // Vérifier les données
        if (!isset($data['data']['custom_data']['user_id'])) {
            Log::warning('PayDunya IPN: missing user_id');
            return response()->json(['status' => 'error'], 400);
        }

        $status = $data['data']['status'] ?? null;
        $userId = $data['data']['custom_data']['user_id'];
        $invoiceToken = $data['data']['invoice']['token'] ?? null;

        if ($status === 'completed') {
            // Trouver le paiement
            $payment = Payment::where('transaction_id', $invoiceToken)
                ->where('user_id', $userId)
                ->first();

            if ($payment && $payment->status !== 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'notes' => 'Confirmé par IPN PayDunya',
                ]);

                // Activer l'abonnement
                $subscription = Subscription::where('user_id', $userId)->latest()->first();
                if ($subscription) {
                    $subscription->activate(
                        $payment->payment_method,
                        $data['data']['receipt_number'] ?? $invoiceToken
                    );
                }

                Log::info("Subscription activated for user {$userId} via IPN");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
