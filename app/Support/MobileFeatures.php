<?php

namespace App\Support;

class MobileFeatures
{
    public static function enabled(): bool
    {
        return (bool) config('mobile.enabled', true);
    }

    public static function isEnabled(string $feature): bool
    {
        if (! self::enabled()) {
            return false;
        }

        return (bool) config("mobile.features.{$feature}", false);
    }

    public static function publicConfig(): array
    {
        $features = config('mobile.features', []);

        return [
            'api_enabled' => self::enabled(),
            'features' => $features,
            'flow' => [
                'mode' => self::recommendedMode(),
                'guest_available' => self::isEnabled('guest'),
                'login_available' => self::isEnabled('auth'),
                'booking_available' => self::isEnabled('booking'),
            ],
            'country' => config('clinic.country'),
            'country_name' => config('clinic.country_name_ar'),
            'currency' => config('clinic.currency'),
            'currency_symbol' => config('clinic.currency_symbol'),
            'phone_country_code' => config('clinic.phone_country_code'),
        ];
    }

    public static function recommendedMode(): string
    {
        if (self::isEnabled('auth') || self::isEnabled('booking')) {
            return 'optional_auth';
        }

        return 'browse_only';
    }
}
