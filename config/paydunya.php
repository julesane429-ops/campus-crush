<?php

$appUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
$publicUrl = rtrim(env('PAYDUNYA_PUBLIC_URL') ?: $appUrl, '/');

return [

    /*
    |--------------------------------------------------------------------------
    | PayDunya Configuration
    |--------------------------------------------------------------------------
    |
    | Mode 'test' pour les tests, 'live' pour la production.
    | Créez un compte Business sur https://paydunya.com
    | puis récupérez vos clés API dans Integration API > Détails.
    |
    */

    'mode' => env('PAYDUNYA_MODE', 'test'),
    'public_url' => $publicUrl,

    'master_key' => env('PAYDUNYA_MASTER_KEY', ''),
    'public_key' => env('PAYDUNYA_PUBLIC_KEY', ''),
    'private_key' => env('PAYDUNYA_PRIVATE_KEY', ''),
    'token' => env('PAYDUNYA_TOKEN', ''),

    // Infos de votre service (affichées sur la page de paiement PayDunya)
    'store' => [
        'name' => env('PAYDUNYA_STORE_NAME', 'Campus Crush'),
        'tagline' => 'Rencontres universitaires au Sénégal',
        'phone' => env('PAYDUNYA_STORE_PHONE', ''),
        'website' => $publicUrl,
    ],

    // URLs de callback
    'return_url' => $publicUrl . '/subscription/success',
    'cancel_url' => $publicUrl . '/subscription/cancel',
    'ipn_url' => $publicUrl . '/webhook/paydunya',

    // Montant abonnement mensuel (FCFA)
    'amount' => 1000,

    // SoftPay doit ouvrir Wave/Orange Money directement. Activez ceci seulement
    // si vous voulez accepter la page checkout PayDunya comme secours explicite.
    'allow_checkout_fallback' => env('PAYDUNYA_ALLOW_CHECKOUT_FALLBACK', false),
    'wave_enabled' => env('PAYDUNYA_WAVE_ENABLED', true),

    // API endpoints
    'base_url' => env('PAYDUNYA_MODE', 'test') === 'live'
        ? 'https://app.paydunya.com/api/v1'
        : 'https://app.paydunya.com/sandbox-api/v1',

];
