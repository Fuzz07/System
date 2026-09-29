<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        // Relative paths are taken from the project root, not the working directory.
        'service_account_key_path' => env('FIREBASE_SERVICE_ACCOUNT_KEY_PATH', 'storage/firebase-key.json'),
        // Fallback for hosts where the key can't be uploaded as a file.
        'service_account_key_b64' => env('FIREBASE_SERVICE_ACCOUNT_KEY_B64'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
        'timeout' => env('OPENAI_TIMEOUT', 15),
    ],

    'paymongo' => [
        'enabled' => env('PAYMONGO_ENABLED', true),
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'api_url' => env('PAYMONGO_API_URL', 'https://api.paymongo.com'),
        'payment_methods' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('PAYMONGO_PAYMENT_METHODS', 'gcash,qrph,card'))
        ))),
        'webhook_tolerance' => (int) env('PAYMONGO_WEBHOOK_TOLERANCE', 300),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

];
