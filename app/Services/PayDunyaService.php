<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
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
     *   'method' => 'wave_redirect'|'om_redirect'|'free_ussd'|'fallback_redirect'|'*_softpay_failed',
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
        if (!$this->isPaymentMethodEnabled($paymentMethod)) {
            return [
                'success' => false,
                'method' => $paymentMethod . '_disabled',
                'url' => null,
                'message' => null,
                'token' => null,
                'error' => $this->disabledPaymentMessage($paymentMethod),
            ];
        }

        // Étape 1 : Créer la facture
        $invoice = $this->createGenericInvoice(
            $userId,
            $userName,
            $userEmail,
            $amount,
            $description,
            $returnUrl,
            $cancelUrl,
            $type
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

        Log::warning('Softpay failed', [
            'method' => $paymentMethod,
            'error' => $softpayResult['error'] ?? 'unknown',
            'phone_prefix' => substr($phone, 0, 2),
            'phone_length' => strlen($phone),
        ]);

        if ((bool) config('paydunya.allow_checkout_fallback', false)) {
            return [
                'success' => true,
                'method' => 'fallback_redirect',
                'url' => $invoice['url'],
                'message' => null,
                'token' => $token,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'method' => $paymentMethod . '_softpay_failed',
            'url' => null,
            'message' => null,
            'token' => $token,
            'error' => $softpayResult['error'] ?? 'SoftPay indisponible. Reessaie plus tard ou contacte le support.',
        ];
    }

    /**
     * Softpay WAVE Sénégal.
     * Retourne une URL pay.wave.com qui ouvre l'app Wave directement.
     */
    private function softpayWave(string $token, string $name, string $email, string $phone): array
    {
        try {
            $response = $this->http()
                ->post($this->baseUrl . '/softpay/wave-senegal', [
                    'wave_senegal_fullName' => $name,
                    'wave_senegal_email' => $email,
                    'wave_senegal_phone' => $phone,
                    'wave_senegal_payment_token' => $token,
                ]);

            $data = $response->json() ?? [];
            $this->logPayDunyaResponse('Softpay Wave response', $data ?? [], $response->status());
            $url = $this->firstUrl($data, ['url', 'redirect_url', 'payment_url', 'launch_url', 'response_text']);

            if ($response->successful() && ($data['success'] ?? false) && $url) {
                return [
                    'success' => true,
                    'method' => 'wave_redirect',
                    'url' => $url, // https://pay.wave.com/...
                    'message' => null,
                ];
            }

            if ($response->successful() && ($data['success'] ?? false)) {
                return ['success' => false, 'error' => 'Wave SoftPay n\'a pas renvoye de lien de paiement.'];
            }

            return ['success' => false, 'error' => $this->waveSoftpayError($data)];

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
            $response = $this->http()
                ->post($this->baseUrl . '/softpay/new-orange-money-senegal', [
                    'customer_name' => $name,
                    'customer_email' => $email,
                    'phone_number' => $phone,
                    'invoice_token' => $token,
                ]);

            $data = $response->json() ?? [];
            $this->logPayDunyaResponse('Softpay Orange Money response', $data ?? [], $response->status());
            $omUrl    = data_get($data, 'other_url.om_url');
            $maxitUrl = data_get($data, 'other_url.maxit_url');
            $qrUrl    = $this->firstUrl($data, ['url', 'redirect_url', 'payment_url', 'launch_url', 'response_text']);
            $url      = $omUrl ?? $maxitUrl ?? $qrUrl;

            if ($response->successful() && ($data['success'] ?? false) && $url) {
                // PayDunya retourne deux URLs HTTPS directes (pas de deep link) :
                // om_url    = Firebase Dynamic Link → ouvre l'app Orange Money (iOS + Android)
                // maxit_url = lien direct Sugu/Maxit → ouvre Maxit (iOS + Android)
                return [
                    'success'     => true,
                    'method'      => 'om_redirect',
                    'url'         => $url,
                    'om_url'      => $omUrl,
                    'maxit_url'   => $maxitUrl,
                    'message'     => null,
                ];
            }

            if ($response->successful() && ($data['success'] ?? false)) {
                return ['success' => false, 'error' => 'Orange Money SoftPay n\'a pas renvoye de lien de paiement.'];
            }

            return ['success' => false, 'error' => $this->softpayError($data, 'Orange Money SoftPay echoue')];

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
            $response = $this->http()
                ->post($this->baseUrl . '/softpay/free-money-senegal', [
                    'customer_name' => $name,
                    'customer_email' => $email,
                    'phone_number' => $phone,
                    'payment_token' => $token,
                ]);

            $data = $response->json() ?? [];
            $this->logPayDunyaResponse('Softpay Free Money response', $data ?? [], $response->status());

            if ($response->successful() && ($data['success'] ?? false)) {
                return [
                    'success' => true,
                    'method' => 'free_ussd',
                    'url' => null,
                    'message' => $data['message'] ?? 'Tapez #150# pour finaliser le paiement.',
                ];
            }

            return ['success' => false, 'error' => $this->softpayError($data, 'Free Money SoftPay echoue')];

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
        int $amount,
        string $description,
        string $returnUrl,
        string $cancelUrl,
        string $type,
        ?array $channels = null
    ): array {
        $payload = [
            'invoice' => [
                'total_amount' => $amount,
                'description' => $description,
                'items' => [
                    'item_0' => [
                        'name' => $description,
                        'quantity' => 1,
                        'unit_price' => $amount,
                        'total_price' => $amount,
                    ],
                ],
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'tagline' => config('paydunya.store.tagline'),
                'phone' => config('paydunya.store.phone'),
                'website_url' => config('paydunya.store.website'),
            ],
            'actions' => [
                'return_url' => $this->paydunyaActionUrl($returnUrl),
                'cancel_url' => $this->paydunyaActionUrl($cancelUrl),
                'callback_url' => config('paydunya.ipn_url'),
            ],
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'type' => $type,
            ],
        ];

        $invoiceChannels = $channels ?? $this->availableChannels();
        if (count(array_filter($invoiceChannels)) > 0) {
            $payload['channels'] = array_values(array_filter($invoiceChannels));
        }

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
            $response = $this->http()
                ->get($this->baseUrl . '/checkout-invoice/confirm/' . $token);

            $data = $response->json() ?? [];

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

    public function availablePaymentMethods(): array
    {
        $methods = ['orange_money'];

        if ((bool) config('paydunya.wave_enabled', true)) {
            $methods[] = 'wave';
        }

        $methods[] = 'free_money';

        return $methods;
    }

    public function isPaymentMethodEnabled(string $paymentMethod): bool
    {
        return in_array($paymentMethod, $this->availablePaymentMethods(), true);
    }

    private function availableChannels(): array
    {
        return array_values(array_filter(array_map(
            fn(string $method) => $this->channelForMethod($method),
            $this->availablePaymentMethods()
        )));
    }

    private function sendInvoice(array $payload): array
    {
        try {
            $response = $this->http()
                ->post($this->baseUrl . '/checkout-invoice/create', $payload);

            $data = $response->json() ?? [];
            $this->logPayDunyaResponse('PayDunya invoice response', $data ?? []);

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

    private function http(): PendingRequest
    {
        $request = Http::acceptJson()
            ->withOptions(['proxy' => ''])
            ->withHeaders($this->headers)
            ->timeout(20)
            ->retry(2, 300);

        if (app()->environment('local') && config('services.http.verify_ssl') === false) {
            return $request->withoutVerifying();
        }

        return $request;
    }

    private function channelForMethod(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'wave' => 'wave-senegal',
            'orange_money' => 'orange-money-senegal',
            'free_money' => 'free-money-senegal',
            default => '',
        };
    }

    private function paydunyaActionUrl(string $url): string
    {
        $publicUrl = rtrim((string) config('paydunya.public_url', ''), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');

        if ($publicUrl === '' || $publicUrl === $appUrl) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        return $publicUrl
            . $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
    }

    private function firstUrl(array $data, array $paths): ?string
    {
        foreach ($paths as $path) {
            $value = data_get($data, $path);

            if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
        }

        return null;
    }

    private function softpayError(array $data, string $fallback): string
    {
        foreach (['message', 'response_text', 'errors.message', 'errors.description'] as $path) {
            $value = data_get($data, $path);

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    private function waveSoftpayError(array $data): string
    {
        $error = $this->softpayError($data, 'Wave SoftPay echoue');

        if (str_contains(mb_strtolower($error), 'serveur')) {
            return 'Wave est temporairement indisponible via PayDunya. Utilise Orange Money pour finaliser ton paiement.';
        }

        return $error;
    }

    private function disabledPaymentMessage(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'wave' => 'Wave est temporairement indisponible via PayDunya. Utilise Orange Money pour finaliser ton paiement.',
            default => 'Ce moyen de paiement est temporairement indisponible.',
        };
    }

    private function logPayDunyaResponse(string $event, array $data, ?int $httpStatus = null): void
    {
        Log::info($event, [
            'http_status' => $httpStatus,
            'success' => $data['success'] ?? null,
            'response_code' => $data['response_code'] ?? null,
            'status' => $data['status'] ?? null,
            'message' => $data['message'] ?? $data['response_text'] ?? null,
            'token_present' => isset($data['token']),
            'url_present' => is_string(data_get($data, 'url')) || is_string(data_get($data, 'response_text')),
            'om_url_present' => is_string(data_get($data, 'other_url.om_url')),
            'maxit_url_present' => is_string(data_get($data, 'other_url.maxit_url')),
        ]);
    }
}
