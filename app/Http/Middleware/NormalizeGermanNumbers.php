<?php

namespace App\Http\Middleware;

use App\Support\GermanNumber;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeGermanNumbers
{
    private const DECIMAL_KEYS = [
        'quantity',
        'minimum_stock',
        'price',
        'amount',
        'shipping_price_gross',
        'tax_rate',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $request->merge($this->normalizeArray($request->all()));

        return $next($request);
    }

    private function normalizeArray(array $values, array $path = []): array
    {
        foreach ($values as $key => $value) {
            $currentPath = [...$path, (string) $key];

            if (is_array($value)) {
                $values[$key] = $this->normalizeArray($value, $currentPath);
                continue;
            }

            if ($this->shouldNormalize((string) $key, $path)) {
                $values[$key] = GermanNumber::parse($value);
            }
        }

        return $values;
    }

    private function shouldNormalize(string $key, array $parentPath): bool
    {
        if (in_array($key, self::DECIMAL_KEYS, true)) {
            return true;
        }

        return in_array('prices', $parentPath, true);
    }
}
