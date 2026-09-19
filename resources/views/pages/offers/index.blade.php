<x-layouts.premium title="Angebote & Rechnungen" subtitle="Angebote, Rechnungen, PDFs, Status und Reservierungen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="offers-page-actions erp-list-toolbar">
        <div class="offers-search-card erp-list-filter-card">
            <form method="GET" action="{{ route('offers.index') }}" class="offers-search-modern erp-list-filter-form">
                <div class="offers-search-field erp-list-search">
                    <i class="bi bi-search"></i>
                    <input
                        name="search"
                        value="{{ $search }}"
                        class="premium-input"
                        placeholder="Angebot, Kunde, Kundennummer oder Produkt suchen..."
                    >
                </div>

                <div class="offers-search-filter">
                    <select name="search_field" class="premium-input offers-search-select erp-list-select" aria-label="Suchfeld auswählen">
                        <option value="all" @selected(($searchField ?? 'all') === 'all')>Alle</option>
                        <option value="number" @selected(($searchField ?? 'all') === 'number')>Angebotsnummer</option>
                        <option value="customer" @selected(($searchField ?? 'all') === 'customer')>Kunde</option>
                        <option value="customer_number" @selected(($searchField ?? 'all') === 'customer_number')>Kundennummer</option>
                        <option value="product" @selected(($searchField ?? 'all') === 'product')>Produkt</option>
                    </select>
                </div>

                <label class="offers-exact-search erp-list-exact">
                    <input type="checkbox" name="exact" value="1" @checked($exact ?? false)>
                    <span>Exakter Wert</span>
                </label>

                <select name="status" class="premium-select offers-status-select erp-list-select">
                    <option value="">Alle Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search || $selectedStatus || ($searchField ?? 'all') !== 'all' || ($exact ?? false))
                    <a href="{{ route('offers.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>
        </div>

        <div class="offers-action-buttons erp-list-actions">
            <a href="{{ route('offers.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Neues Angebot
            </a>
        </div>
    </section>

    <section class="offers-modern-card erp-list-card">
        <div class="premium-table-wrap offers-table-shell erp-list-table-shell">
            <table class="premium-table offers-modern-table">
                <thead>
                    <tr>
                        <th>Angebot</th>
                        <th>Kunde</th>
                        <th>Status</th>
                        <th>Positionen</th>
                        <th>Gesamt</th>
                        <th>Reserviert bis</th>
                        <th>PDF</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($offers as $offer)
                        @php
                            $isCancelled = $offer->status === 'cancelled';
                            $isExpired = $offer->status === 'reservation_expired';
                            $isCompleted = $offer->status === 'completed';
                            $isActiveReservation = $offer->isReservationActive();

                            $statusClass = $isCancelled || $isExpired
                                ? 'critical'
                                : ($isActiveReservation ? 'reserved' : ($isCompleted ? 'completed' : 'open'));

                            $unifiedStatusClass = match ($offer->status) {
                                \App\Models\Offer::STATUS_OFFER => 'status-unified-offer',
                                \App\Models\Offer::STATUS_IN_PROGRESS => 'status-unified-progress',
                                \App\Models\Offer::STATUS_READY => 'status-unified-ready',
                                \App\Models\Offer::STATUS_COMPLETED => 'status-unified-completed',
                                \App\Models\Offer::STATUS_CANCELLED => 'status-unified-cancelled',
                                \App\Models\Offer::STATUS_RESERVATION_EXPIRED => 'status-unified-expired',
                                default => '',
                            };
                        @endphp

                        <tr>
                            <td>
                                <a href="{{ route('offers.show', $offer) }}" class="offer-number-link">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    {{ $offer->offer_number }}
                                </a>

                                <div class="offer-date">
                                    {{ $offer->created_at?->format('d.m.Y H:i') }}
                                </div>
                            </td>

                            <td>
                                <div class="offer-customer-cell">
                                    <strong>{{ $offer->customer?->company_name ?: '—' }}</strong>
                                    <span>
                                        {{ $offer->customer?->customer_number ?: '—' }}
                                        @if ($offer->customer?->group?->name)
                                            · {{ $offer->customer?->group?->name }}
                                        @endif
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="offer-status-pill {{ $statusClass }} {{ $unifiedStatusClass }}">
                                    {{ $offer->statusLabel() }}
                                </span>
                            </td>

                            <td>
                                <span class="offer-items-count">
                                    {{ $offer->items->count() }}
                                </span>
                            </td>

                            <td>
                                <span class="offer-total">
                                    {{ number_format((float) $offer->total, 2, ',', '.') }} €
                                </span>
                            </td>

                            <td>
                                @if ($offer->reserved_until && $offer->isReservationActive())
                                    <span class="offer-reservation-date">
                                        <i class="bi bi-clock"></i>
                                        {{ $offer->reserved_until->format('d.m.Y H:i') }}
                                    </span>
                                @else
                                    <span class="offer-muted">—</span>
                                @endif
                            </td>

                            <td>
                                <div class="premium-actions offer-pdf-actions">
                                    <a class="premium-icon-btn" href="{{ route('offers.pdf', [$offer, 'offer']) }}" target="_blank" title="Angebot PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('offers.pdf', [$offer, 'invoice']) }}" target="_blank" title="Rechnung PDF">
                                        <i class="bi bi-receipt"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('offers.pdf', [$offer, 'delivery-note']) }}" target="_blank" title="Lieferschein PDF">
                                        <i class="bi bi-truck"></i>
                                    </a>
                                </div>
                            </td>

                            <td>
                                <div class="premium-actions offer-row-actions">
                                    <a class="premium-icon-btn" href="{{ route('offers.show', $offer) }}" title="Vorschau">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @php
                                        $canSalesEdit = auth()->user()?->isSales() && $offer->status === \App\Models\Offer::STATUS_OFFER;
                                        $canManagerEdit = auth()->user()?->isManager() && ! in_array($offer->status, ['completed', 'cancelled', 'reservation_expired'], true);
                                    @endphp

                                    @if ($canSalesEdit || $canManagerEdit)
                                        <a class="premium-icon-btn" href="{{ route('offers.edit', $offer) }}" title="Bearbeiten">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form method="POST" action="{{ route('offers.cancel', $offer) }}" data-confirm="Angebot stornieren und Reservierung freigeben?">
                                            @csrf
                                            <button class="premium-icon-btn premium-danger" type="submit" title="Stornieren">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if (auth()->user()?->isManager())
                                        <form method="POST" action="{{ route('offers.destroy', $offer) }}" data-confirm="Angebot wirklich löschen? Offene Reservierungen werden dadurch freigegeben. Erledigte Angebote mit FIFO-Abbuchung bleiben geschützt.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="premium-icon-btn premium-danger" type="submit" title="Löschen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="offers-empty-state">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <strong>Noch keine Angebote vorhanden.</strong>
                                    <span>Erstelle dein erstes Angebot und reserviere Ware automatisch.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="offers-pagination erp-list-pagination">
            {{ $offers->links() }}
        </div>
    </section>

    
</x-layouts.premium>