<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Guardian Portal Payments
    |--------------------------------------------------------------------------
    |
    | Feature toggle for enabling payments from the Guardian portal.
    | Default false in .env.example to avoid accidental activation in dev.
    |
    */
    'guardian_portal_enabled' => env('GUARDIAN_PAYMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Supported: sslcommerz, bkash, nagad (stubs). Extend as needed.
    |
    */
    'default' => env('PAYMENT_GATEWAY', 'sslcommerz'),

    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Configuration
    |--------------------------------------------------------------------------
    */
    'sslcommerz' => [
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
        'sandbox' => filter_var(env('SSLCOMMERZ_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | bKash Configuration
    |--------------------------------------------------------------------------
    */
    'bkash' => [
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
        'sandbox' => filter_var(env('BKASH_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nagad Configuration
    |--------------------------------------------------------------------------
    */
    'nagad' => [
        'merchant_id' => env('NAGAD_MERCHANT_ID'),
        'merchant_number' => env('NAGAD_MERCHANT_NUMBER'),
        'public_key' => env('NAGAD_PUBLIC_KEY'),
        'private_key' => env('NAGAD_PRIVATE_KEY'),
        'sandbox' => filter_var(env('NAGAD_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),
    ],
];
