<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Project Slug & Environment Identifier
    |--------------------------------------------------------------------------
    */
    'project_slug' => env('DIAGNOSTICS_PROJECT_SLUG', 'default-app'),
    'environment'  => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | API Key Authentication Token
    |--------------------------------------------------------------------------
    */
    'api_key' => env('DIAGNOSTICS_API_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Event Aggregation Buffer Configuration
    |--------------------------------------------------------------------------
    */
    'buffer' => [
        'enabled'              => env('DIAGNOSTICS_BUFFER_ENABLED', true),
        'driver'               => env('DIAGNOSTICS_BUFFER_DRIVER', 'array'),
        'max_items'            => (int) env('DIAGNOSTICS_BUFFER_MAX_ITEMS', 100),
        'max_lifetime_seconds' => (int) env('DIAGNOSTICS_BUFFER_MAX_LIFETIME', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Masking Patterns
    |--------------------------------------------------------------------------
    */
    'masking' => [
        'enabled'        => true,
        'redaction_text' => '***REDACTED***',
        'keys'           => [
            'password', 'pass', 'secret', 'bearer', 'token',
            'api_key', 'authorization', 'credit_card', 'ssn',
            'private_key', 'cookie', 'db_password',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Webhook Dispatcher
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'enabled' => env('DIAGNOSTICS_WEBHOOK_ENABLED', false),
        'urls'    => array_filter(explode(',', env('DIAGNOSTICS_WEBHOOK_URLS', ''))),
        'timeout' => (int) env('DIAGNOSTICS_WEBHOOK_TIMEOUT', 5),
    ],
];
