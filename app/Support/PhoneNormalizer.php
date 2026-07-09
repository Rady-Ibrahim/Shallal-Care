<?php

namespace App\Support;

class PhoneNormalizer
{
    /**
     * Normalize Egyptian mobile numbers to E.164 (+20...).
     */
    public static function toE164(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '20')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+20'.substr($digits, 1);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '+20'.$digits;
        }

        return '+'.$digits;
    }

    public static function toLocal(string $phone): string
    {
        $e164 = self::toE164($phone);
        $digits = substr($e164, 3); // strip +20

        return '0'.$digits;
    }
}
