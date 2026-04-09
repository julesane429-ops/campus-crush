<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayDunyaService
{
    private string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('paydunya.base_url');

        $this->headers = [
            'PAYDUNYA-MASTER-KEY' => config('paydunya.master_key'),
            'PAYDUNYA-PUBLIC-KEY' => config('paydunya.public_key'),
            'PAYDUNYA-PRIVATE-KEY' => config('paydunya.private_key'),
            'PAYDUNYA-TOKEN' => config('paydunya.token'),
            'Content-Type' => 'application/json',
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // SOFTPAY — Paiement direct sans redirection PayDunya
    // ══════════════════════════════════════════════════════════════

    /**
     * Créer la facture PUIS lancer le Softpay vers le bon opérateur.
     *
     * @return array [
     *   'success' => bool,
     *   'method' => 'wave_redirect'|'om_redirect'|'free_ussd'|'fallback_redirect',
     *   'url' => string|null (URL de redirection Wave/OM),
     *   'message' => string|null (message pour Free Money),
     *   'token' => string|null,
     *   'error' => string|null,
     * ]
     */
    public function payDirect(
        int $userId,
        string $userName,
        string $userEmail,
        string $phoneNumber,
        string $paymentMethod,
        int $amount,
        string $description,
        string $returnUrl,
        string $cancelUrl,
        string $type = 'subscription'
    ): array {
        // Étape 1 : Créer la facture
        $invoice = $this->createGenericInvoice(
            $userId, $userName, $userEmail, $amount, $description, $returnUrl, $cancelUrl, $type
        );

        if (!$invoice['success']) {
            return [
                'success' => false,
                'method' => null,
                'url' => null,
                'message' => null,
                'token' => null,
                'error' => $invoice['error'],
            ];
        }

        $token = $invoice['token'];
        $phone = $this->formatPhone($phoneNumber);

        // Étape 2 : Appeler le Softpay selon l'opérateur
        $softpayResult = match ($paymentMethod) {
            'wave' => $this->softpayWave($token, $userName, $userEmail, $phone),
            'orange_money' => $this->softpayOrangeMoney($token, $userName, $userEmail, $phone),
            'free_money' => $this->softpayFreeMoney($token, $userName, $userEmail, $phone),
            default => ['success' => false, 'error' => 'Méthode inconnue'],
        };

        if ($softpayResult['success']) {
            return array_merge($softpayResult, ['token' => $token, 'error' => null]);
        }

        // Fallback : redirection vers la page PayDunya classique
        Log::warning('Softpay failed, fallback to redirect', [
            'method' => $paymentMethod,
            'error' => $softpayResult['error'] ?? 'unknown',
        ]);

        return [
            'success' => true,
            'method' => 'fallback_redirect',
            'url' => $invoice['url'],
            'message' => null,
            'token' => $token,
            'error' => null,
        ];
    }

    /**
     * Softpay WAVE Sénégal.
     * Retourne une URL pay.wave.com qui ouvre l'app Wave directement.
     */
    private function softpayWave(string $token, string $name, string $email, string $phone): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->headers)
                ->post($this->baseUrl . '/softpay/wave-senegal', [
                    'wave_senegal_fullName' => $name,
                    'wave_senegal_email' => $email,
                    'wave_senegal_phone' => $phone,
                    'wave_senegal_payment_token' => $token,
                ]);

            $data = $response->json();
            Log::info('Softpay Wave response', ['data' => $data]);

            if ($response->successful() && ($data['success'] ?? false)) {
                return [
                    'success' => true,
                    'method' => 'wave_redirect',
                    'url' => $data['url'], // https://pay.wave.com/...
                    'message' => null,
                ];
            }

            return ['success' => false, 'error' => $data['message'] ?? 'Wave Softpay échoué'];

        } catch (\Exception $e) {
            Log::error('Softpay Wave error', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Softpay ORANGE MONEY Sénégal (nouvelle API QR Code).
     * Retourne om_url qui ouvre l'app Orange Money directement.
     */
    private function softpayOrangeMoney(string $token, string $name, string $email, string $phone): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->headers)
                ->post($this->baseUrl . '/softpay/new-orange-money-senegal', [
                    'customer_name' => $name,
                    'customer_email' => $email,
                    'phone_number' => $phone,
                    'invoice_token' => $token,
                ]);

            $data = $response->json();
            Log::info('Softpay Orange Money response', ['data' => $data]);

            if ($response->successful() && ($data['success'] ?? false)) {
                // Deep link Android brut : orangemoney://qrcode.orange.sn/mp/TOKEN
                $omDeepLink = $data['other_url']['om_url'] ?? null;

                // Universal Link : https://qrcode.orange.sn/mp/TOKEN
                // Même path que le deep link mais en HTTPS → iOS ouvre Orange Money
                // ou Maxit automatiquement si installé, sinon Safari (page utilisable)
                $universalLink = null;
                if ($omDeepLink && preg_match('/^orangemoney:\/\/(.+)$/', $omDeepLink, $m)) {
                    $universalLink = 'https://' . $m[1];
                }

                return [
                    'success'     => true,
                    'method'      => 'om_redirect',
                    'url'         => $universalLink ?? $data['url'] ?? null,
                    'om_deeplink' => $omDeepLink,
                    'message'     => null,
                ];
            }

            return ['success' => false, 'error' => $data['message'] ?? 'Orange Money Softpay échoué'];

        } catch (\Exception $e) {
            Log::error('Softpay Orange Money error', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Softpay FREE MONEY Sénégal.
     * Envoie la demande au téléphone. L'utilisateur doit taper #150# pour confirmer.
     */
    private function softpayFreeMoney(string $token, string $name, string $email, string $phone): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->headers)
                ->post($this->baseUrl . '/softpay/free-money-senegal', [
                    'customer_name' => $name,
                    'customer_email' => $email,
                    'phone_number' => $phone,
                    'payment_token' => $token,
                ]);

            $data = $response->json();
            Log::info('Softpay Free Money response', ['data' => $data]);

            if ($response->successful() && ($data['success'] ?? false)) {
                return [
                    'success' => true,
                    'method' => 'free_ussd',
                    'url' => null,
                    'message' => $data['message'] ?? 'Tapez #150# pour finaliser le paiement.',
                ];
            }

            return ['success' => false, 'error' => $data['message'] ?? 'Free Money Softpay échoué'];

        } catch (\Exception $e) {
            Log::error('Softpay Free Money error', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════════════
    // CRÉATION DE FACTURE
    // ══════════════════════════════════════════════════════════════

    private function createGenericInvoice(
        int $userId, string $userName, string $userEmail,
        int $amount, string $description, string $returnUrl, string $cancelUrl, string $type
    ): array {
        $payload = [
            'invoice' => [
                'total_amount' => $amount,
                'description' => $description,
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'tagline' => config('paydunya.store.tagline'),
                'phone' => config('paydunya.store.phone'),
                'website_url' => config('paydunya.store.website'),
            ],
            'items' => [
                'item_0' => [
                    'name' => $description,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                ],
            ],
            'actions' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'callback_url' => config('paydunya.ipn_url'),
            ],
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'type' => $type,
            ],
            'channels' => [
                'orange-money-senegal',
                'wave-senegal',
                'free-money-senegal',
            ],
        ];

        return $this->sendInvoice($payload);
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES LEGACY (gardées pour compatibilité webhook)
    // ══════════════════════════════════════════════════════════════

    public function createInvoice(int $userId, string $userName, string $userEmail, string $phoneNumber = ''): array
    {
        return $this->createGenericInvoice(
            $userId, $userName, $userEmail,
            config('paydunya.amount', 1000),
            'Abonnement Campus Crush - 1 mois',
            config('paydunya.return_url'),
            config('paydunya.cancel_url'),
            'subscription'
        );
    }

    public function createBoostInvoice(int $userId, string $userName, string $userEmail, string $phoneNumber = ''): array
    {
        return $this->createGenericInvoice(
            $userId, $userName, $userEmail, 500,
            'Boost profil Campus Crush - 24h',
            route('boost.success'), route('boost.index'), 'boost'
        );
    }

    public function createAiChatInvoice(int $userId, string $userName, string $userEmail, string $phoneNumber = ''): array
    {
        return $this->createGenericInvoice(
            $userId, $userName, $userEmail, 500,
            'Déblocage IA Campus Crush',
            route('ai.pay.success'), route('ai.unlock'), 'ai_chat'
        );
    }

    public function checkPaymentStatus(string $token): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->headers)
                ->get($this->baseUrl . '/checkout-invoice/confirm/' . $token);

            $data = $response->json();

            if ($response->successful() && isset($data['status'])) {
                return [
                    'success' => true,
                    'status' => $data['status'],
                    'custom_data' => $data['custom_data'] ?? [],
                    'receipt_url' => $data['receipt_url'] ?? null,
                    'customer' => $data['customer'] ?? [],
                    'transaction_id' => $data['receipt_number'] ?? null,
                ];
            }

            return ['success' => false, 'status' => 'unknown', 'error' => $data['response_text'] ?? 'Erreur'];

        } catch (\Exception $e) {
            return ['success' => false, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    private function sendInvoice(array $payload): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->headers)
                ->post($this->baseUrl . '/checkout-invoice/create', $payload);

            $data = $response->json();
            Log::info('PayDunya invoice response', ['data' => $data]);

            if ($response->successful() && isset($data['response_code']) && $data['response_code'] === '00') {
                return [
                    'success' => true,
                    'url' => $data['response_text'],
                    'token' => $data['token'] ?? null,
                    'error' => null,
                ];
            }

            return ['success' => false, 'url' => null, 'token' => null, 'error' => $data['response_text'] ?? 'Erreur'];

        } catch (\Exception $e) {
            Log::error('PayDunya error', ['message' => $e->getMessage()]);
            return ['success' => false, 'url' => null, 'token' => null, 'error' => 'Erreur de connexion'];
        }
    }

    private function formatPhone(string $phone): string
{
    // Retirer tout sauf les chiffres
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Si commence par 221, retirer le préfixe (Softpay veut le format local)
    if (str_starts_with($phone, '221') && strlen($phone) === 12) {
        $phone = substr($phone, 3);
    }

    return $phone; // Format local : 771234567
}
}