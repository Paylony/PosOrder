<?php

return [
    /*
    |--------------------------------------------------------------------------
    | POS Order API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for POS Order API.
    | You can obtain your API credentials from your POS dashboard.
    |
    */

    'api_url' => env('POS_ORDER_API_URL', 'https://abs.paylony.com/api'),

    'api_key' => env('POS_ORDER_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Terminal ID
    |--------------------------------------------------------------------------
    |
    | The default terminal ID to use for transactions if not specified.
    |
    */
    'default_terminal_id' => env('POS_ORDER_DEFAULT_TERMINAL_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment Types
    |--------------------------------------------------------------------------
    |
    | Available payment types for POS transactions.
    |
    */
    'payment_types' => [
        'card_purchase' => 'Card Purchase',
        'pay_with_phone' => 'Pay with Phone',
        'ussd' => 'USSD',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */
    'timeout' => env('POS_ORDER_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging of API requests and responses.
    |
    */
    'logging' => env('POS_ORDER_LOGGING', false),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic retry of failed orders.
    |
    */
    'retry' => [
        'enabled' => env('POS_ORDER_RETRY_ENABLED', true),
        'max_attempts' => env('POS_ORDER_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('POS_ORDER_RETRY_DELAY', 5), // seconds
    ],
];