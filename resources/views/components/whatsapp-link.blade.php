@props([
    'number' => null,
    'label' => null,
])

@php
    $raw = trim((string) $number);
    $digits = preg_replace('/\D+/', '', $raw);

    if (str_starts_with($digits, '00')) {
        $digits = substr($digits, 2);
    } elseif (str_starts_with($digits, '0')) {
        $digits = '49' . substr($digits, 1);
    }

    $url = $digits ? 'https://wa.me/' . $digits : null;
@endphp

@if ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="premium-whatsapp-link" title="WhatsApp öffnen">
        <i class="bi bi-whatsapp"></i>
        <span>{{ $label ?: $raw }}</span>
    </a>
@else
    <span class="premium-muted">—</span>
@endif
