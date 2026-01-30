<?php

use Illuminate\Support\Facades\Route;

// Debug routes - Remove in production
Route::middleware('auth')->group(function () {
    Route::get('/debug/mail', function () {
        return response()->json([
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'APP_ENV' => env('APP_ENV'),
            'has_password' => !empty(env('MAIL_PASSWORD')),
        ]);
    });
});
