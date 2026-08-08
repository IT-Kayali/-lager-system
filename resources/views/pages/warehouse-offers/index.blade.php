<x-layouts.premium title="Angebote & Rechnungen" subtitle="Lageransicht für Bestellungen ab dem Status In Bearbeitung.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <div class="premium-alert" style="margin-bottom:18px;">
        <strong>Lager-Arbeitsansicht:</strong>
        Hier werden nur Bestellungen ab „In Bearbeitung“ angezeigt. Du kannst die Bestellung ansehen, den Status im Lagerprozess weiterführen und den Lieferschein öffnen.
    </div>

    <section class="premium-card">
        <form method="GET" action="{{ route('warehouse.offers.index') }}" class="premium-toolbar" style="margin-bottom:18px;">
            <div class="premium-search" style="flex:1;">
                <input
                    name="search"
                    value="{{ $search }}"
                    class="premium-input"
                    style="min-width:280px; flex:1;"
                    placeholder="Angebotsnummer oder Kunde suchen..."
                >

                <select name="status" class="premium-select" style="min-width:210px;">
                    <option value="">Alle Lagerstatus</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search || $selectedStatus)
                    <a href="{{ route('warehouse.offers.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </div>
        </form>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Angebot</th>
                        <th>Kunde</th>
                        <th>Status</th>
                        <th>Positionen</th>
                        <th>Versandart</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td>
                                <a href="{{ route('warehouse.offers.show', $offer) }}" style="color:#111; font-weight:900; text-decoration:none;">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    {{ $offer->offer_number }}
                                </a>
                                <div class="premium-muted">{{ $offer->created_at?->format('d.m.Y H:i') }}</div>
                            </td>

                            <td>
                                <strong>{{ $offer->customer?->company_name ?: '—' }}</strong>
                                <div class="premium-muted">
                                    {{ $offer->customer?->customer_number ?: '—' }}
                                    @if ($offer->customer?->delivery_city || $offer->customer?->billing_city)
                                        · {{ $offer->customer?->delivery_city ?: $offer->customer?->billing_city }}
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="premium-badge ok">{{ $offer->statusLabel() }}</span>
                            </td>

                            <td>
                                <span class="premium-code">{{ $offer->items->count() }}</span>
                            </td>

                            <td>
                                <strong>{{ $offer->shipping_method ?: '—' }}</strong>
                                @if ($offer->shipping_method === 'Lieferung' && $offer->carton_count)
                                    <div class="premium-muted">{{ $offer->carton_count }} Kartons</div>
                                @endif
                            </td>

                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('warehouse.offers.show', $offer) }}" title="Bestellung ansehen">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('warehouse.offers.delivery-note', $offer) }}" target="_blank" title="Lieferschein öffnen">
                                        <i class="bi bi-truck"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="premium-muted" style="padding:24px; text-align:center;">
                                    Aktuell gibt es keine Bestellungen für die Lagervorbereitung.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $offers->links() }}
        </div>
    </section>
</x-layouts.premium>
