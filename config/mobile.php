<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile API — Feature flags
    |--------------------------------------------------------------------------
    | browse_only: directory (doctors/clinics/branches) + nearby.
    | Booking is clinic-only (reception). Enable MOBILE_BOOKING_ENABLED later
    | when patient mobile booking on clinic_bookings is implemented.
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
