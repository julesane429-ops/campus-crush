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
     * Créer une facture d'abonnement (1000 FCFA).
     */
    public function createInvoice(int $userId, string $userName, string $userEmail, string $phoneNumber = ''): array
    {
        $amount = config('paydunya.amount', 1000);

        $payload = [
            'invoice' => [
                'total_amount' => $amount,
                'description' => 'Abonnement Campus Crush - 1 mois',
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'tagline' => config('paydunya.store.tagline'),
                'phone' => config('paydunya.store.phone'),
                'website_url' => config('paydunya.store.website'),
            ],
            'items' => [
                'item_0' => [
                    'name' => 'Abonnement Campus Crush Premium',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'description' => 'Accès illimité pour 30 jours',
                ],
            ],
            'actions' => [
                'return_url' => config('paydunya.return_url'),
                'cancel_url' => config('paydunya.cancel_url'),
                'callback_url' => config('paydunya.ipn_url'),
            ],
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'plan' => 'monthly',
                'type' => 'subscription',
            ],
            'channels' => [
                'orange-money-senegal',
                'wave-senegal',
                'free-money-senegal',
            ],
        ];

        // Pré-remplir le formulaire PayDunya
        if ($userName || $userEmail || $phoneNumber) {
            $payload['customer'] = [
                'name' => $userName,
                'email' => $userEmail,
                'phone' => $phoneNumber ? $this->formatPhone($phoneNumber) : '',
            ];
        }

        return $this->sendInvoice($payload);
    }

    /**
     * Créer une facture de boost (500 FCFA).
     */
    public function createBoostInvoice(int $userId, string $userName, string $userEmail, string $phoneNumber = ''): array
    {
        $amount = 500;

        $payload = [
            'invoice' => [
                'total_amount' => $amount,
                'description' => 'Boost profil Campus Crush - 24h',
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'tagline' => config('paydunya.store.tagline'),
                'phone' => config('paydunya.store.phone'),
                'website_url' => config('paydunya.store.website'),
            ],
            'items' => [
                'item_0' => [
                    'name' => 'Boost profil 24h',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'description' => 'Ton profil apparaît en tête du swipe pendant 24h',
                ],
            ],
            'actions' => [
                'return_url' => route('boost.success'),
                'cancel_url' => route('boost.index'),
                'callback_url' => route('webhook.paydunya'),
            ],
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'type' => 'boost',
            ],
            'channels' => [
                'orange-money-senegal',
                'wave-senegal',
                'free-money-senegal',
            ],
        ];

        if ($userName || $userEmail || $phoneNumber) {
            $payload['customer'] = [
                'name' => $userName,
                'email' => $userEmail,
                'phone' => $phoneNumber ? $this->formatPhone($phoneNumber) : '',
            ];
        }

        return $this->sendInvoice($payload);
    }

    /**
     * Créer une facture IA Chat (500 FCFA).
     */
    public function createAiChatInvoice(int $userId, string $userName, string $userEmail, string $phoneNumber = ''): array
    {
        $amount = 500;

        $payload = [
            'invoice' => [
                'total_amount' => $amount,
                'description' => 'Déblocage IA Campus Crush',
            ],
            'store' => [
                'name' => config('paydunya.store.name'),
                'tagline' => config('paydunya.store.tagline'),
                'phone' => config('paydunya.store.phone'),
                'website_url' => config('paydunya.store.website'),
            ],
            'items' => [
                'item_0' => [
                    'name' => 'IA Campus Crush — Accès illimité',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'description' => 'Aïda, Coach Profil, Entraînement Drague',
                ],
            ],
            'actions' => [
                'return_url' => route('ai.pay.success'),
                'cancel_url' => route('ai.unlock'),
                'callback_url' => route('webhook.paydunya'),
            ],
            'custom_data' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'type' => 'ai_chat',
            ],
            'channels' => [
                'orange-money-senegal',
                'wave-senegal',
                'free-money-senegal',
            ],
        ];

        if ($userName || $userEmail || $phoneNumber) {
            $payload['customer'] = [
                'name' => $userName,
                'email' => $userEmail,
                'phone' => $phoneNumber ? $this->formatPhone($phoneNumber) : '',
            ];
        }

        return $this->sendInvoice($payload);
    }

    /**
     * Vérifier le statut d'un paiement via le token de facture.
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

    /**
     * Envoyer la facture à PayDunya.
     */
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
     * Formater le numéro au format international sénégalais.
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '221')) return $phone;
        if (str_starts_with($phone, '7')) return '221' . $phone;
        return '221' . $phone;
    }
}
