<?php

return [

    'paths' => [
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'admin/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('APP_URL', 'http://localhost'),
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
