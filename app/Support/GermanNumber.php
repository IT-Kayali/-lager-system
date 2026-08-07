<?php

namespace App\Support;

final class GermanNumber
{
    public static function format(int|float|string|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            $value = 0;
        }

        return number_format((float) $value, $decimals, ',', '.');
    }

    public static function parse(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = trim(str_replace(["\u{00A0}", ' '], '', $value));

        if ($normalized === '') {
            return $value;
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)$/', $normalized)
            ? $normalized
            : $value;
    }
}
