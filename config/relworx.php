<?php

return [
    'base_url' => env('RELWORX_BASE_URL', 'https://payments.relworx.com/api'),
    'api_key' => env('RELWORX_API_KEY'),
    'account_no' => env('RELWORX_ACCOUNT_NO'),
    'timeout' => (int) env('RELWORX_TIMEOUT', 30),
    'connect_timeout' => (int) env('RELWORX_CONNECT_TIMEOUT', 10),

    'webhooks' => [
        'enabled' => (bool) env('RELWORX_WEBHOOKS_ENABLED', false),
        'path' => env('RELWORX_WEBHOOK_PATH', 'relworx/webhook'),
        // Must exactly match the callback URL configured in the Relworx dashboard.
        'url' => env('RELWORX_WEBHOOK_URL'),
        'secret' => env('RELWORX_WEBHOOK_SECRET'),
        'verify_signature' => (bool) env('RELWORX_WEBHOOK_VERIFY_SIGNATURE', true),
        'timestamp_tolerance' => (int) env('RELWORX_WEBHOOK_TIMESTAMP_TOLERANCE', 300),
        'middleware' => ['api'],
    ],
];
