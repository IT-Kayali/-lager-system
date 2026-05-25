<?php

namespace App\Support;

use libphonenumber\PhoneNumberUtil;

class PhoneCountries
{
    private static ?array $countries = null;

    public static function all(): array
    {
        if (self::$countries !== null) {
            return self::$countries;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $regions = $phoneUtil->getSupportedRegions();

        $countries = [];

        foreach ($regions as $region) {
            $dialCode = '+' . $phoneUtil->getCountryCodeForRegion($region);
            $name = \Locale::getDisplayRegion('-' . $region, 'de') ?: $region;

            $countries[] = [
                'iso' => $region,
                'flag' => self::flag($region),
                'name' => $name,
                'dial' => $dialCode,
                'value' => $dialCode . '|' . $region,
                'label' => self::flag($region) . ' ' . $name . ' ' . $dialCode,
            ];
        }

        usort($countries, function ($a, $b) {
            if ($a['iso'] === 'DE') {
                return -1;
            }

            if ($b['iso'] === 'DE') {
                return 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return self::$countries = $countries;
    }

    private static function flag(string $region): string
    {
        $region = strtoupper($region);

        if (strlen($region) !== 2 || ! function_exists('mb_chr')) {
            return '';
        }

        return mb_chr(127397 + ord($region[0]), 'UTF-8')
            . mb_chr(127397 + ord($region[1]), 'UTF-8');
    }
}
