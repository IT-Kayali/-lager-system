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

    @php
        $hasWarehouseOfferFilters = ! empty($search)
            || ($searchField ?? 'all') !== 'all'
            || ($exact ?? false)
            || ! empty($selectedStatus);
    @endphp

    <section class="erp-list-toolbar">
        <div class="erp-list-filter-card">
            <form method="GET" action="{{ route('warehouse.offers.index') }}" class="erp-list-filter-form">
                <div class="erp-list-search">
                    <i class="bi bi-search"></i>
                    <input name="search" value="{{ $search ?? '' }}" class="premium-input" placeholder="Angebot, Kunde oder Produkt suchen...">
                </div>

                <select name="search_field" class="premium-select erp-list-select" aria-label="Suchfeld auswählen">
                    <option value="all" @selected(($searchField ?? 'all') === 'all')>Alle</option>
                    <option value="number" @selected(($searchField ?? 'all') === 'number')>Angebotsnummer</option>
                    <option value="customer" @selected(($searchField ?? 'all') === 'customer')>Kunde</option>
                    <option value="customer_number" @selected(($searchField ?? 'all') === 'customer_number')>Kundennummer</option>
                    <option value="product" @selected(($searchField ?? 'all') === 'product')>Produkt</option>
                </select>

                <select name="status" class="premium-select erp-list-select" aria-label="Lagerstatus filtern">
                    <option value="">Alle Lagerstatus</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($selectedStatus ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <label class="erp-list-exact">
                    <input type="checkbox" name="exact" value="1" @checked($exact ?? false)>
                    <span>Exakter Wert</span>
                </label>

                <button class="premium-btn" type="submit"><i class="bi bi-search"></i> Suchen</button>

                @if ($hasWarehouseOfferFilters)
                    <a href="{{ route('warehouse.offers.index') }}" class="premium-btn"><i class="bi bi-x-lg"></i> Zurücksetzen</a>
                @endif
            </form>
        </div>
    </section>

    <section class="erp-list-card">
        <div class="premium-table-wrap erp-list-table-shell">
            <table class="premium-table warehouse-offers-table">
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
                        @php
                            $statusClass = match ($offer->status) {
                                \App\Models\Offer::STATUS_IN_PROGRESS => 'progress',
                                \App\Models\Offer::STATUS_READY => 'ready',
                                \App\Models\Offer::STATUS_COMPLETED => 'completed',
                                default => 'neutral',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('warehouse.offers.show', $offer) }}" class="warehouse-offer-link">
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

                            <td><span class="warehouse-status-badge {{ $statusClass }}">{{ $offer->statusLabel() }}</span></td>
                            <td><span class="premium-code">{{ $offer->items->count() }}</span></td>
                            <td>
                                <strong>{{ $offer->shipping_method ?: '—' }}</strong>
                                @if ($offer->shipping_method === 'Lieferung' && $offer->carton_count)
                                    <div class="premium-muted">{{ $offer->carton_count }} Kartons</div>
                                @endif
                            </td>
                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('warehouse.offers.show', $offer) }}" title="Bestellung ansehen"><i class="bi bi-eye"></i></a>
                                    <a class="premium-icon-btn" href="{{ route('warehouse.offers.delivery-note', $offer) }}" target="_blank" title="Lieferschein öffnen"><i class="bi bi-truck"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="erp-list-empty">
                                    <i class="bi bi-box-seam"></i>
                                    <strong>Keine Lagerangebote gefunden.</strong>
                                    <span>Passe die Filter an oder warte auf eine Übergabe aus dem Verkauf.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="erp-list-pagination">{{ $offers->links() }}</div>
    </section>

    <style>
        .warehouse-offers-table{min-width:980px}
        .warehouse-offer-link{display:inline-flex;align-items:center;gap:7px;color:#111;font-weight:900;text-decoration:none}
        .warehouse-offer-link:hover{color:#8a6a00;text-decoration:underline}
        .warehouse-status-badge{display:inline-flex;align-items:center;min-height:32px;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:950;white-space:nowrap}
        .warehouse-status-badge.progress{background:#dbeafe;color:#1d4ed8}
        .warehouse-status-badge.ready{background:#fef3c7;color:#b45309}
        .warehouse-status-badge.completed{background:#dcfce7;color:#166534}
        .warehouse-status-badge.neutral{background:#f3f4f6;color:#374151}
    </style>
</x-layouts.premium>
