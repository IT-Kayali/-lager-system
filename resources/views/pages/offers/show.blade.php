<x-layouts.premium title="Angebotsvorschau" subtitle="Angebot anzeigen, Status ändern und PDFs öffnen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @if ($offer->isFinal())
        <div class="premium-alert" style="border-color: rgba(33,33,33,.15); background: rgba(33,33,33,.06); color:#212121;">
            Dieses Angebot ist abgeschlossen. Bearbeitung und erneute FIFO-Abbuchung sind gesperrt.
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:24px; font-weight:900; margin:0;">{{ $offer->offer_number }}</h2>
                <div class="premium-muted" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px;">
                    <span>{{ $offer->customer?->company_name }}</span>
                    <span>·</span>
                    <x-customer-group-badge :group="$offer->customer?->group" />
                    <span>·</span>
                    <span>{{ $offer->templateLabel() }}</span>
                </div>
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
                <a href="{{ route('offers.pdf', [$offer, 'delivery-note']) }}" target="_blank" class="premium-btn">
                    <i class="bi bi-truck"></i>
                    Lieferschein PDF
                </a>

                @if (! in_array($offer->status, ['completed', 'cancelled', 'reservation_expired'], true))
                    <a href="{{ route('offers.edit', $offer) }}" class="premium-btn">
                        <i class="bi bi-pencil"></i>
                        Bearbeiten
                    </a>
                @endif

                @if (auth()->user()?->isManager())
                    <form method="POST" action="{{ route('offers.destroy', $offer) }}" onsubmit="return confirm('Angebot wirklich löschen? Offene Reservierungen werden dadurch freigegeben. Erledigte Angebote mit FIFO-Abbuchung bleiben geschützt.');">
                        @csrf
                        @method('DELETE')
                        <button class="premium-btn" type="submit" style="background:#991b1b;">
                            <i class="bi bi-trash"></i>
                            Löschen
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (! $offer->isFinal())
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
        @else
            <div style="margin-bottom:18px;">
                <span class="premium-badge ok">Status: {{ $offer->statusLabel() }}</span>
            </div>
        @endif

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
                            <td>{{ \App\Support\GermanNumber::format($item->quantity) }} {{ $item->product?->unitLabel('de') ?? (['gram' => 'Gramm', 'liter' => 'Liter', 'piece' => 'Stück'][$item->unit] ?? ($item->unit ?: '—')) }}</td>
                            <td><span class="premium-badge ok">{{ $item->tier_label }}</span></td>
                            <td>{{ \App\Support\GermanNumber::format($item->unit_price) }} €</td>
                            <td>{{ \App\Support\GermanNumber::format($item->line_total) }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="text-align:right; font-size:22px; font-weight:900; margin-top:18px;">
            Gesamt: {{ \App\Support\GermanNumber::format($offer->total) }} €
        </div>
    </section>
</x-layouts.premium>
