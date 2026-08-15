<x-layouts.premium title="Bestellung vorbereiten" subtitle="Lageransicht mit Positionen, Versand, Status und Lieferschein.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @php
        $customer = $offer->customer;
        $street = $customer?->delivery_street ?: $customer?->billing_street;
        $houseNumber = $customer?->delivery_house_number ?: $customer?->billing_house_number;
        $postalCode = $customer?->delivery_postal_code ?: $customer?->billing_postal_code;
        $city = $customer?->delivery_city ?: $customer?->billing_city ?: $customer?->city;
        $country = $customer?->delivery_country ?: $customer?->billing_country;
        $statusClass = match ($offer->status) {
            \App\Models\Offer::STATUS_IN_PROGRESS => 'progress',
            \App\Models\Offer::STATUS_READY => 'ready',
            \App\Models\Offer::STATUS_COMPLETED => 'completed',
            default => 'neutral',
        };
    @endphp

    <section class="premium-card" style="margin-bottom:22px;">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:24px; font-weight:950; margin:0;">{{ $offer->offer_number }}</h2>
                <div class="premium-muted" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:6px;">
                    <span>{{ $customer?->company_name ?: '—' }}</span>
                    <span>·</span>
                    <span class="warehouse-status-badge {{ $statusClass }}">{{ $offer->statusLabel() }}</span>
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('warehouse.offers.delivery-note', $offer) }}" target="_blank" class="premium-btn gold">
                    <i class="bi bi-truck"></i>
                    Lieferschein PDF
                </a>

                <a href="{{ route('warehouse.offers.index') }}" class="premium-btn">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>
            </div>
        </div>

        @if ($nextStatuses)
            <form method="POST" action="{{ route('warehouse.offers.status', $offer) }}" style="margin-top:18px;">
                @csrf
                @method('PUT')

                <div class="premium-form-grid">
                    <div class="premium-form-field">
                        <label for="status">Nächster Lagerstatus</label>
                        <select id="status" name="status" class="premium-select" required>
                            @foreach ($nextStatuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="premium-form-field" style="display:flex; align-items:end;">
                        <button class="premium-btn gold" type="submit">
                            <i class="bi bi-arrow-repeat"></i>
                            Status speichern
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="premium-alert" style="margin-top:18px;">
                Diese Bestellung ist erledigt. Weitere Statusänderungen sind für Lager gesperrt.
            </div>
        @endif
    </section>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:18px; margin-bottom:22px;">
        <section class="premium-card">
            <h3 style="margin:0 0 12px; font-size:18px;">Empfänger</h3>
            <div style="display:grid; gap:6px;">
                <strong>{{ $customer?->company_name ?: '—' }}</strong>
                <span>{{ $customer?->customer_number ?: '—' }}</span>
                @if ($street || $houseNumber)
                    <span>{{ trim(($street ?? '') . ' ' . ($houseNumber ?? '')) }}</span>
                @endif
                @if ($postalCode || $city)
                    <span>{{ trim(($postalCode ?? '') . ' ' . ($city ?? '')) }}</span>
                @endif
                @if ($country)
                    <span>{{ $country }}</span>
                @endif
            </div>
        </section>

        <section class="premium-card">
            <h3 style="margin:0 0 12px; font-size:18px;">Versand</h3>
            <div style="display:grid; gap:6px;">
                <div><strong>Versandart:</strong> {{ $offer->shipping_method ?: '—' }}</div>
                @if ($offer->shipping_method === 'Lieferung')
                    <div><strong>Anzahl Kartons:</strong> {{ $offer->carton_count ?: '—' }}</div>
                @endif
            </div>
        </section>

        @if ($offer->notes)
            <section class="premium-card">
                <h3 style="margin:0 0 12px; font-size:18px;">Hinweis</h3>
                <div style="white-space:pre-wrap;">{{ $offer->notes }}</div>
            </section>
        @endif
    </div>

    <section class="premium-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Positionen vorbereiten</h2>
                <p class="premium-muted" style="margin:4px 0 0;">Nur Produkt, Menge und Einheit – keine Preis- oder Rechnungsdaten.</p>
            </div>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Menge</th>
                        <th>Einheit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($offer->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                <div class="premium-muted">{{ $item->product_code }}</div>
                            </td>
                            <td>{{ \App\Support\GermanNumber::format($item->quantity) }}</td>
                            <td>{{ $item->unit ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .warehouse-status-badge { display:inline-flex; align-items:center; min-height:32px; padding:7px 11px; border-radius:999px; font-size:12px; font-weight:950; white-space:nowrap; }
        .warehouse-status-badge.progress { background:#dbeafe; color:#1d4ed8; }
        .warehouse-status-badge.ready { background:#fef3c7; color:#b45309; }
        .warehouse-status-badge.completed { background:#dcfce7; color:#166534; }
        .warehouse-status-badge.neutral { background:#f3f4f6; color:#374151; }
    </style>
</x-layouts.premium>
