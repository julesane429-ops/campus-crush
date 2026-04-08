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

    /**
     * Créer une facture générique.
     */
    private function createGenericInvoice(array $params): array
    {
        $payload = [
            'invoice' => [
                'total_amount' => $params['amount'],
                'description' => $params['description'],
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'tagline' => config('paydunya.store.tagline'),
                'phone' => config('paydunya.store.phone'),
                'website_url' => config('paydunya.store.website'),
            ],
            'items' => [
                'item_0' => [
                    'name' => $params['item_name'],
                    'quantity' => 1,
                    'unit_price' => $params['amount'],
                    'total_price' => $params['amount'],
                    'description' => $params['item_desc'],
                ],
            ],
            'actions' => [
                'return_url' => $params['return_url'],
                'cancel_url' => $params['cancel_url'],
                'callback_url' => $params['callback_url'] ?? config('paydunya.ipn_url'),
            ],
            'custom_data' => $params['custom_data'],
        ];

        // ═══ PRÉ-REMPLIR LES INFOS CLIENT ═══
        // C'est ça qui évite à l'utilisateur de tout retaper sur PayDunya !
        if (!empty($params['customer_name']) || !empty($params['customer_email']) || !empty($params['customer_phone'])) {
            $payload['customer'] = array_filter([
                'name' => $params['customer_name'] ?? null,
                'email' => $params['customer_email'] ?? null,
                'phone' => $params['customer_phone'] ?? null,
            ]);
        }

        // ═══ SÉLECTION AUTOMATIQUE DU CANAL ═══
        // Si on connaît la méthode de paiement, on ne montre que celle-là
        if (!empty($params['payment_method'])) {
            $channelMap = [
                'orange_money' => ['orange-money-senegal'],
                'wave' => ['wave-senegal'],
                'free_money' => ['free-money-senegal'],
            ];
            $payload['channels'] = $channelMap[$params['payment_method']] ?? [
                'orange-money-senegal', 'wave-senegal', 'free-money-senegal',
            ];
        } else {
            $payload['channels'] = [
                'orange-money-senegal', 'wave-senegal', 'free-money-senegal',
            ];
        }

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

            return [
                'success' => false,
                'url' => null,
                'token' => null,
                'error' => $data['response_text'] ?? 'Erreur PayDunya inconnue',
            ];

        } catch (\Exception $e) {
            Log::error('PayDunya error', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'url' => null,
                'token' => null,
                'error' => 'Erreur de connexion au service de paiement',
            ];
        }
    }

    /**
     * Facture abonnement (1000 FCFA).
     */
    public function createInvoice(int $userId, string $userName, string $userEmail, ?string $phone = null, ?string $paymentMethod = null): array
    {
        return $this->createGenericInvoice([
            'amount' => config('paydunya.amount', 1000),
            'description' => 'Abonnement Campus Crush - 1 mois',
            'item_name' => 'Abonnement Campus Crush Premium',
            'item_desc' => 'Accès illimité pour 30 jours',
            'return_url' => config('paydunya.return_url'),
            'cancel_url' => config('paydunya.cancel_url'),
            'callback_url' => config('paydunya.ipn_url'),
            'customer_name' => $userName,
            'customer_email' => $userEmail,
            'customer_phone' => $phone,
            'payment_method' => $paymentMethod,
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'plan' => 'monthly',
            ],
        ]);
    }

    /**
     * Facture boost (500 FCFA).
     */
    public function createBoostInvoice(int $userId, string $userName, string $userEmail, ?string $phone = null, ?string $paymentMethod = null): array
    {
        return $this->createGenericInvoice([
            'amount' => 500,
            'description' => 'Boost profil Campus Crush - 24h',
            'item_name' => 'Boost profil 24h',
            'item_desc' => 'Ton profil apparaît en tête du swipe pendant 24h',
            'return_url' => route('boost.success'),
            'cancel_url' => route('boost.index'),
            'callback_url' => route('webhook.paydunya'),
            'customer_name' => $userName,
            'customer_email' => $userEmail,
            'customer_phone' => $phone,
            'payment_method' => $paymentMethod,
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'type' => 'boost',
            ],
        ]);
    }

    /**
     * Facture IA Chat (500 FCFA).
     */
    public function createAiChatInvoice(int $userId, string $userName, string $userEmail, ?string $phone = null, ?string $paymentMethod = null): array
    {
        return $this->createGenericInvoice([
            'amount' => 500,
            'description' => 'Déblocage IA Campus Crush',
            'item_name' => 'IA Campus Crush — Accès illimité',
            'item_desc' => 'Aïda, Coach Profil, Entraînement Drague',
            'return_url' => route('ai.pay.success'),
            'cancel_url' => route('ai.unlock'),
            'callback_url' => route('webhook.paydunya'),
            'customer_name' => $userName,
            'customer_email' => $userEmail,
            'customer_phone' => $phone,
            'payment_method' => $paymentMethod,
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'type' => 'ai_chat',
            ],
        ]);
    }

    /**
     * Vérifier le statut d'un paiement.
     */
    public function checkPaymentStatus(string $token): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->headers)
                ->get($this->baseUrl . '/checkout-invoice/confirm/' . $token);

            $data = $response->json();

            Log::info('PayDunya status check', ['token' => $token, 'data' => $data]);

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

            return [
                'success' => false,
                'status' => 'unknown',
                'error' => $data['response_text'] ?? 'Impossible de vérifier le paiement',
            ];

        } catch (\Exception $e) {
            Log::error('PayDunya status check error', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'status' => 'error',
                'error' => 'Erreur de connexion',
            ];
        }
    }
}