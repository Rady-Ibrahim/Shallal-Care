<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile API — Feature flags (disable, don't delete)
    |--------------------------------------------------------------------------
    | Simple production flow: directory browse + guest mode.
    | Enable auth/booking in .env for Postman or when product decides.
    */

    'enabled' => env('MOBILE_API_ENABLED', true),

    'features' => [
        'directory' => env('MOBILE_DIRECTORY_ENABLED', true),
        'guest' => env('MOBILE_GUEST_ENABLED', true),
        'auth' => env('MOBILE_AUTH_ENABLED', true),
        'booking' => env('MOBILE_BOOKING_ENABLED', false),
        'reviews' => env('MOBILE_REVIEWS_ENABLED', false),
        'medical_history' => env('MOBILE_MEDICAL_HISTORY_ENABLED', false),
        'push' => env('MOBILE_PUSH_ENABLED', false),
        'nearby' => env('MOBILE_NEARBY_ENABLED', true),
    ],

    'guest' => [
        'name' => 'زائر',
        'token_name' => 'guest_token',
    ],

];
