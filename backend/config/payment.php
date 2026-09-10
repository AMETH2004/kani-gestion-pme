<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fournisseur de paiement par défaut
    |--------------------------------------------------------------------------
    |
    | Pour l'instant seul « manual » (encaissement saisi à la main : espèces,
    | chèque, virement) est réellement implémenté. Les entrées « wave » et
    | « orange_money » sont des emplacements réservés : la classe existe mais
    | lève une exception tant que l'intégration n'est pas faite.
    |
    */

    'default' => env('PAYMENT_DEFAULT_PROVIDER', 'manual'),

    'providers' => [

        'manual' => [
            'driver' => \App\Services\Payment\ManualProvider::class,
        ],

        'wave' => [
            'driver' => \App\Services\Payment\WaveProvider::class,
            'api_key' => env('WAVE_API_KEY'),
            'api_secret' => env('WAVE_API_SECRET'),
            'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
            'base_url' => env('WAVE_BASE_URL', 'https://api.wave.com'),
        ],

        'orange_money' => [
            'driver' => \App\Services\Payment\OrangeMoneyProvider::class,
            'merchant_key' => env('OM_MERCHANT_KEY'),
            'client_id' => env('OM_CLIENT_ID'),
            'client_secret' => env('OM_CLIENT_SECRET'),
            'base_url' => env('OM_BASE_URL', 'https://api.orange.com'),
        ],

    ],

];
