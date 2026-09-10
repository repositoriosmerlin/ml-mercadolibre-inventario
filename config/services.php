<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // cambio --- 10/09/2026: credenciales y endpoints de Mercado Libre. El dominio de
    // autorización es el del país del vendedor (.com.co para Colombia); usar el de otro
    // país produce errores de vinculación difíciles de diagnosticar. La API en cambio es
    // una sola para toda la región y lo que cambia es el site_id.
    'mercadolibre' => [
        'app_id' => env('MELI_APP_ID'),
        'secret_key' => env('MELI_SECRET_KEY'),

        // Debe coincidir carácter por carácter con lo registrado en el DevCenter. Se lee
        // de configuración y no de route() a propósito: detrás del proxy inverso Laravel
        // generaría http:// y MELI respondería redirect_uri mismatch.
        'redirect_uri' => env('MELI_REDIRECT_URI'),

        'site_id' => env('MELI_SITE_ID', 'MCO'),
        'auth_url' => env('MELI_AUTH_URL', 'https://auth.mercadolibre.com.co/authorization'),
        'api_url' => env('MELI_API_URL', 'https://api.mercadolibre.com'),
    ],

];
