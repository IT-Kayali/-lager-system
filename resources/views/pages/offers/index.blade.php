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

    <style>
        .offers-page-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            margin-bottom: 22px;
        }

        .offers-search-card {
            min-width: 0;
            padding: 14px;
            border: 1px solid #d8cbb7;
            border-radius: 18px;
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 12px 28px rgba(42, 36, 25, .06);
        }

        .offers-search-modern {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .offers-search-field {
            position: relative;
            min-width: 290px;
            flex: 1;
        }

        .offers-search-field i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #665f54;
            font-size: 17px;
            pointer-events: none;
        }

        .offers-search-field .premium-input {
            width: 100%;
            min-height: 48px;
            padding-left: 42px !important;
            background: #fffdf8 !important;
        }

        .offers-search-filter {
            min-width: 190px;
        }

        .offers-search-select {
            width: 100%;
            min-height: 48px;
            background: #fffdf8 !important;
            font-weight: 850;
            color: #211d17;
        }

        .offers-exact-search {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 48px;
            padding: 0 14px;
            border: 1px solid #d8cbb7;
            border-radius: 12px;
            background: #fffdf8;
            color: #211d17;
            font-weight: 850;
            white-space: nowrap;
            cursor: pointer;
        }

        .offers-exact-search input {
            width: 18px;
            height: 18px;
            accent-color: #c9a227;
        }

        .offers-status-select {
            min-width: 190px;
            min-height: 48px;
        }

        .offers-action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .offers-modern-card {
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background: rgba(255, 255, 255, .86);
            box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
            overflow: hidden;
        }

        .offers-table-shell {
            margin-top: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .offers-modern-table {
            min-width: 1220px;
        }

        .offers-modern-table thead th {
            padding: 18px 18px !important;
            background: #eee7dc !important;
            color: #3a332a !important;
            border-bottom: 2px solid #8d8069 !important;
        }

        .offers-modern-table tbody td {
            padding: 18px 18px !important;
            color: #111111 !important;
        }

        .offer-number-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #111111;
            text-decoration: none;
            font-weight: 950;
            white-space: nowrap;
        }

        .offer-number-link:hover {
            color: #8a6a00;
            text-decoration: underline;
        }

        .offer-date {
            margin-top: 5px;
            color: #665f54;
            font-size: 12px;
            font-weight: 850;
        }

        .offer-customer-cell {
            display: grid;
            gap: 4px;
        }

        .offer-customer-cell strong {
            font-size: 15px;
            font-weight: 950;
        }

        .offer-customer-cell span {
            color: #665f54;
            font-size: 12px;
            font-weight: 850;
        }

        .offer-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 950;
            white-space: nowrap;
        }

        .offer-status-pill.open {
            background: #dcfce7;
            color: #166534;
        }

        .offer-status-pill.reserved {
            background: #fef3c7;
            color: #b45309;
        }

        .offer-status-pill.completed {
            background: #e0f2fe;
            color: #075985;
        }

        .offer-status-pill.critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .offer-items-count {
            min-width: 38px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #f3e8be;
            color: #111111;
            font-weight: 950;
        }

        .offer-total {
            color: #111111;
            font-size: 15px;
            font-weight: 950;
            white-space: nowrap;
        }

        .offer-reservation-date {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            font-size: 13px;
            font-weight: 900;
            white-space: nowrap;
        }

        .offer-muted {
            color: #665f54;
            font-weight: 850;
        }

        .offer-pdf-actions {
            justify-content: center !important;
            gap: 7px !important;
        }

        .offer-row-actions {
            justify-content: flex-end !important;
            gap: 7px !important;
        }

        .offer-row-actions form,
        .offer-pdf-actions form {
            margin: 0;
        }

        .premium-icon-btn {
            width: 36px;
            height: 36px;
            min-width: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d8cbb7;
            border-radius: 10px;
            background: #fffdf8;
            color: #111111;
            text-decoration: none;
            cursor: pointer;
            transition: transform .16s ease, border-color .16s ease, background .16s ease;
        }

        .premium-icon-btn:hover {
            transform: translateY(-1px);
            border-color: #c9a227;
            background: #fff7dc;
            color: #111111;
        }

        .premium-icon-btn.premium-danger,
        .premium-danger {
            color: #991b1b;
        }

        .premium-icon-btn.premium-danger:hover,
        .premium-danger:hover {
            border-color: #ef4444;
            background: #fee2e2;
            color: #991b1b;
        }

        .offers-empty-state {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 56px 16px;
            text-align: center;
            color: #665f54;
        }

        .offers-empty-state i {
            font-size: 38px;
            color: #8a6a00;
        }

        .offers-empty-state strong {
            color: #111111;
            font-size: 18px;
        }

        .offers-pagination {
            padding: 16px 18px;
            border-top: 1px solid #e7dece;
            background: #f8f2e7;
        }

        .offers-modern-table th:nth-child(4),
        .offers-modern-table td:nth-child(4),
        .offers-modern-table th:nth-child(5),
        .offers-modern-table td:nth-child(5),
        .offers-modern-table th:nth-child(6),
        .offers-modern-table td:nth-child(6),
        .offers-modern-table th:nth-child(7),
        .offers-modern-table td:nth-child(7) {
            text-align: center !important;
        }

        .offers-modern-table th:nth-child(8),
        .offers-modern-table td:nth-child(8) {
            text-align: right !important;
        }

        @media (max-width: 1250px) {
            .offers-page-actions {
                grid-template-columns: 1fr;
            }

            .offers-action-buttons {
                justify-content: flex-start;
            }
        }

        @media (max-width: 700px) {
            .offers-search-modern {
                display: grid;
            }

            .offers-search-field {
                min-width: 0;
            }

            .offers-action-buttons {
                display: grid;
            }
        }
    </style>
</x-layouts.premium>