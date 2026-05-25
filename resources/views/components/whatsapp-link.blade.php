@props([
    'number' => null,
    'label' => null,
    'countryCode' => '+49|DE',
])

@php
    $raw = trim((string) $number);
    $selectedCountryCode = trim((string) $countryCode);

    $digits = preg_replace('/\D+/', '', $raw);

    $dialPart = explode('|', $selectedCountryCode)[0] ?? '+49';
    $countryDigits = preg_replace('/\D+/', '', $dialPart ?: '+49');

    if (str_starts_with($raw, '+')) {
        $whatsappDigits = $digits;
    } elseif (str_starts_with($digits, '00')) {
        $whatsappDigits = substr($digits, 2);
    } elseif ($digits) {
        $localDigits = ltrim($digits, '0');
        $whatsappDigits = ($countryDigits ?: '49') . $localDigits;
    } else {
        $whatsappDigits = null;
    }

    $url = $whatsappDigits ? 'https://wa.me/' . $whatsappDigits : null;
@endphp

@if ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="premium-whatsapp-link" title="WhatsApp öffnen">
        <i class="bi bi-whatsapp"></i>
        <span>{{ $label ?: $raw }}</span>
    </a>
@else
    <span class="premium-muted">—</span>
@endif
