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

    /*
    |--------------------------------------------------------------------------
    | OAuth Services
    |--------------------------------------------------------------------------
    |
    | Configuration for social authentication providers (Google, Facebook, etc.)
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Services
    |--------------------------------------------------------------------------
    |
    | Configuration for payment gateway providers (GCash, Online Banking, etc.)
    |
    */

    'payment' => [
        'sandbox_mode' => env('PAYMENT_SANDBOX_MODE', true),
        'test_mode' => env('PAYMENT_TEST_MODE', false),
        'webhook_timeout' => env('PAYMENT_WEBHOOK_TIMEOUT', 30),
    ],

    'gcash' => [
        'api_key' => env('GCASH_API_KEY'),
        'api_secret' => env('GCASH_API_SECRET'),
        'base_url' => env('GCASH_BASE_URL', 'https://api.gcash.com'),
        'sandbox_url' => env('GCASH_SANDBOX_URL', 'https://sandbox.gcash.com'),
    ],

    'online_banking' => [
        'api_key' => env('ONLINE_BANKING_API_KEY'),
        'merchant_id' => env('ONLINE_BANKING_MERCHANT_ID'),
        'base_url' => env('ONLINE_BANKING_BASE_URL', 'https://api.onlinebanking.ph'),
        'sandbox_url' => env('ONLINE_BANKING_SANDBOX_URL', 'https://sandbox.onlinebanking.ph'),
    ],

    'bank_transfer' => [
        'api_key' => env('BANK_TRANSFER_API_KEY'),
        'base_url' => env('BANK_TRANSFER_BASE_URL', 'https://api.banktransfer.ph'),
        'sandbox_url' => env('BANK_TRANSFER_SANDBOX_URL', 'https://sandbox.banktransfer.ph'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Services
    |--------------------------------------------------------------------------
    |
    | Configuration for AI service providers (Replicate, OpenAI, etc.)
    |
    */

    'replicate' => [
        'api_key' => env('REPLICATE_API_KEY'),
        'base_url' => env('REPLICATE_BASE_URL', 'https://api.replicate.com/v1'),
        'timeout' => env('REPLICATE_TIMEOUT', 300),
    ],

];