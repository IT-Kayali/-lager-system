<x-layouts.premium title="Angebotsvorschau" subtitle="Angebot anzeigen, Status ändern und PDFs öffnen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:24px; font-weight:900; margin:0;">{{ $offer->offer_number }}</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    {{ $offer->customer?->company_name }} · {{ $offer->customer?->group?->name }} · {{ $offer->templateLabel() }}
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('offers.pdf', [$offer, 'offer']) }}" target="_blank" class="premium-btn gold">
                    <i class="bi bi-file-earmark-pdf"></i>
                    Angebot PDF
                </a>

                <a href="{{ route('offers.pdf', [$offer, 'invoice']) }}" target="_blank" class="premium-btn">
                    <i class="bi bi-receipt"></i>
                    Rechnung PDF
                </a>

                @if (! in_array($offer->status, ['completed', 'cancelled', 'reservation_expired'], true))
                    <a href="{{ route('offers.edit', $offer) }}" class="premium-btn">
                        <i class="bi bi-pencil"></i>
                        Bearbeiten
                    </a>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('offers.status', $offer) }}" style="margin-bottom:18px;">
            @csrf
            @method('PUT')

            <div class="premium-form-grid">
                <div class="premium-form-field">
                    <label>Status ändern</label>
                    <select name="status" class="premium-select">
                        @foreach ([
                            'offer' => 'Angebot',
                            'in_progress' => 'In Bearbeitung',
                            'reserved' => 'Reserviert',
                            'ready_for_pickup' => 'Abholbereit',
                            'completed' => 'Erledigt',
                            'cancelled' => 'Storniert',
                            'reservation_expired' => 'Reservierung abgelaufen',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($offer->status === $value)>{{ $label }}</option>
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

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Menge</th>
                        <th>Preisstufe</th>
                        <th>Einzelpreis</th>
                        <th>Summe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($offer->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                <div class="premium-muted">{{ $item->product_code }}</div>
                            </td>
                            <td>{{ number_format((float) $item->quantity, 3, ',', '.') }} {{ $item->unit }}</td>
                            <td><span class="premium-badge ok">{{ $item->tier_label }}</span></td>
                            <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                            <td>{{ number_format((float) $item->line_total, 2, ',', '.') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="text-align:right; font-size:22px; font-weight:900; margin-top:18px;">
            Gesamt: {{ number_format((float) $offer->total, 2, ',', '.') }} €
        </div>
    </section>
</x-layouts.premium>
