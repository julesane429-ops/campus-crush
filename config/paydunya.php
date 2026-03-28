<?php

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

    'master_key' => env('PAYDUNYA_MASTER_KEY', ''),
    'public_key' => env('PAYDUNYA_PUBLIC_KEY', ''),
    'private_key' => env('PAYDUNYA_PRIVATE_KEY', ''),
    'token' => env('PAYDUNYA_TOKEN', ''),

    // Infos de votre service (affichées sur la page de paiement PayDunya)
    'store' => [
        'name' => env('PAYDUNYA_STORE_NAME', 'Campus Crush'),
        'tagline' => 'Rencontres universitaires au Sénégal',
        'phone' => env('PAYDUNYA_STORE_PHONE', ''),
        'website' => env('APP_URL', 'http://localhost:8000'),
    ],

    // URLs de callback
    'return_url' => env('APP_URL', 'http://localhost:8000') . '/subscription/success',
    'cancel_url' => env('APP_URL', 'http://localhost:8000') . '/subscription/cancel',
    'ipn_url' => env('APP_URL', 'http://localhost:8000') . '/webhook/paydunya',

    // Montant abonnement mensuel (FCFA)
    'amount' => 1000,

    // API endpoints
    'base_url' => env('PAYDUNYA_MODE', 'test') === 'live'
        ? 'https://app.paydunya.com/api/v1'
        : 'https://app.paydunya.com/sandbox-api/v1',

];
