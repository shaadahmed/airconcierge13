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

    'hostaway' => [
        'base_url' => env('HOSTAWAY_BASE_URL', 'https://api.hostaway.com/v1/'),
        'account_id' => env('HOSTAWAY_ACCOUNT_ID'),
        'api_key' => env('HOSTAWAY_API_KEY'),
        'timeout' => (int) env('HOSTAWAY_HTTP_TIMEOUT', 15),
        'connect_timeout' => (int) env('HOSTAWAY_HTTP_CONNECT_TIMEOUT', 5),
        'webhook' => [
            'username' => env('HOSTAWAY_WEBHOOK_USERNAME'),
            'password' => env('HOSTAWAY_WEBHOOK_PASSWORD'),
        ],
    ],

    'zoho' => [
        'client_id' => env('ZOHO_CLIENT_ID'),
        'client_secret' => env('ZOHO_CLIENT_SECRET_KEY'),
        'redirect_url' => env('ZOHO_REDIRECT_URL'),
        'account_url' => env('ZOHO_ACCOUNT_URL', 'https://accounts.zoho.com'),
        'api_base_url' => env('ZOHO_API_BASE_URL', 'https://sign.zoho.com/api/v1'),
        'refresh_grace_minutes' => (int) env('ZOHO_REFRESH_GRACE_MINUTES', 50),
        'timeout' => (int) env('ZOHO_HTTP_TIMEOUT', 15),
    ],

];
