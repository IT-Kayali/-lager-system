@php
    $summary = $dashboard['summary'];
    $sales = $dashboard['sales'];
    $productStats = $dashboard['products'];
    $customerStats = $dashboard['customers'];
    $stock = $dashboard['stock'];
    $charts = $dashboard['charts'];
    $context = $dashboard['context'];

    $coverage = $summary['revenue_coverage'] ?? [
        'orders_total' => (int) ($summary['completed_orders'] ?? 0),
        'orders_with_amount' => 0,
        'orders_without_amount' => 0,
        'has_missing_amounts' => false,
        'all_amounts_missing' => false,
        'is_complete' => true,
    ];

    $hasMissingAmounts = (bool) $coverage['has_missing_amounts'];
    $allAmountsMissing = (bool) $coverage['all_amounts_missing'];

    $recentSales = $sales['recent'] ?? collect();

    $advancedKeys = [
        'customer_group_id',
        'category_id',
        'supplier_id',
        'user_id',
        'shipping_method',
        'branch',
        'branch_status',
        'movement_type',
    ];

    $advancedFilterActive = collect($advancedKeys)
        ->contains(fn ($key) => filled($filters[$key] ?? null));

    $unitLabels = [
        'gram' => 'g',
        'liter' => 'L',
        'piece' => 'Stk.',
    ];
@endphp

<x-layouts.premium
    title="Statistik"
    subtitle="Die wichtigsten Verkaufs-, Kunden-, Produkt- und Lagerkennzahlen auf einen Blick."
>
    <link
        rel="stylesheet"
        href="{{ asset('css/statistics-dashboard.css') }}"
    >

    <div
        class="statistics-dashboard"
        data-statistics-runtime
    >
        <section class="statistics-filter-card">
            <form
                method="GET"
                action="{{ route('statistics.index') }}"
            >
                <div class="statistics-filter-grid">
                    <div class="premium-form-field">
                        <label for="statistics-period">
                            Zeitraum
                        </label>

                        <select
                            id="statistics-period"
                            name="period"
                            class="premium-select"
                            data-statistics-period
                        >
                            @foreach ($periods as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($filters['period'] === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="premium-form-field">
                        <label for="statistics-customer">
                            Kunde
                        </label>

                        <select
                            id="statistics-customer"
                            name="customer_id"
                            class="premium-select"
                        >
                            <option value="">
                                Alle Kunden
                            </option>

                            @foreach ($customers as $customer)
                                <option
                                    value="{{ $customer->id }}"
                                    @selected(
                                        (string) $filters['customer_id']
                                        === (string) $customer->id
                                    )
                                >
                                    {{ $customer->customer_number }}
                                    —
                                    {{ $customer->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="premium-form-field">
                        <label for="statistics-product">
                            Produkt
                        </label>

                        <select
                            id="statistics-product"
                            name="product_id"
                            class="premium-select"
                        >
                            <option value="">
                                Alle Produkte
                            </option>

                            @foreach ($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    @selected(
                                        (string) $filters['product_id']
                                        === (string) $product->id
                                    )
                                >
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="premium-form-field">
                        <label for="statistics-status">
                            Angebotsstatus
                        </label>

                        <select
                            id="statistics-status"
                            name="status"
                            class="premium-select"
                        >
                            <option value="">
                                Alle Status
                            </option>

                            @foreach ($statusLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($filters['status'] === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="statistics-filter-actions">
                        <button
                            type="submit"
                            class="premium-btn gold"
                        >
                            <i class="bi bi-funnel"></i>
                            Anwenden
                        </button>

                        <a
                            href="{{ route('statistics.index') }}"
                            class="premium-btn"
                        >
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Zurücksetzen
                        </a>
                    </div>

                    <div
                        class="statistics-custom-dates"
                        data-custom-dates
                        @if ($filters['period'] !== 'custom') hidden @endif
                    >
                        <div class="premium-form-field">
                            <label for="statistics-date-from">
                                Von
                            </label>

                            <input
                                id="statistics-date-from"
                                class="premium-input"
                                type="date"
                                name="date_from"
                                value="{{ $filters['date_from'] }}"
                            >
                        </div>

                        <div class="premium-form-field">
                            <label for="statistics-date-to">
                                Bis
                            </label>

                            <input
                                id="statistics-date-to"
                                class="premium-input"
                                type="date"
                                name="date_to"
                                value="{{ $filters['date_to'] }}"
                            >
                        </div>
                    </div>

                    <details
                        class="statistics-advanced"
                        @if ($advancedFilterActive) open @endif
                    >
                        <summary>
                            Weitere Filter
                        </summary>

                        <div class="statistics-advanced-grid">
                            <div class="premium-form-field">
                                <label>Kundengruppe</label>

                                <select
                                    name="customer_group_id"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Kundengruppen
                                    </option>

                                    @foreach ($customerGroups as $group)
                                        <option
                                            value="{{ $group->id }}"
                                            @selected(
                                                (string) $filters['customer_group_id']
                                                === (string) $group->id
                                            )
                                        >
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Kategorie</label>

                                <select
                                    name="category_id"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Kategorien
                                    </option>

                                    @foreach ($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected(
                                                (string) $filters['category_id']
                                                === (string) $category->id
                                            )
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Lieferant</label>

                                <select
                                    name="supplier_id"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Lieferanten
                                    </option>

                                    @foreach ($suppliers as $supplier)
                                        <option
                                            value="{{ $supplier->id }}"
                                            @selected(
                                                (string) $filters['supplier_id']
                                                === (string) $supplier->id
                                            )
                                        >
                                            {{ $supplier->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Mitarbeiter</label>

                                <select
                                    name="user_id"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Mitarbeiter
                                    </option>

                                    @foreach ($users as $user)
                                        <option
                                            value="{{ $user->id }}"
                                            @selected(
                                                (string) $filters['user_id']
                                                === (string) $user->id
                                            )
                                        >
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Versandart</label>

                                <select
                                    name="shipping_method"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Versandarten
                                    </option>

                                    @foreach ($shippingMethods as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                $filters['shipping_method'] === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Filiale</label>

                                <select
                                    name="branch"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Filialen
                                    </option>

                                    @foreach ($branches as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected($filters['branch'] === $value)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Filialstatus</label>

                                <select
                                    name="branch_status"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Filialstatus
                                    </option>

                                    @foreach ($branchStatusLabels as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                $filters['branch_status'] === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="premium-form-field">
                                <label>Lagerbewegung</label>

                                <select
                                    name="movement_type"
                                    class="premium-select"
                                >
                                    <option value="">
                                        Alle Bewegungen
                                    </option>

                                    @foreach ($movementTypes as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                $filters['movement_type'] === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </details>
                </div>
            </form>

            <div class="statistics-filter-context">
                <span class="statistics-period-badge">
                    <i class="bi bi-calendar3"></i>
                    {{ $context['period_label'] }}
                </span>

                @foreach ($context['chips'] ?? [] as $chip)
                    @if (($chip['key'] ?? '') !== 'period')
                        <span class="statistics-inline-badge">
                            {{ $chip['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="statistics-kpi-grid">
            <article class="statistics-kpi">
                <span class="statistics-kpi-label">
                    {{ $hasMissingAmounts
                        ? 'Erfasster Umsatz'
                        : 'Umsatz aus Verkäufen' }}
                </span>

                @if ($allAmountsMissing)
                    <strong class="statistics-kpi-value is-missing">
                        Nicht erfasst
                    </strong>
                @else
                    <strong class="statistics-kpi-value">
                        {{ number_format(
                            (float) $summary['completed_revenue'],
                            2,
                            ',',
                            '.'
                        ) }}
                        €
                    </strong>
                @endif

                @if ($hasMissingAmounts)
                    <span class="statistics-kpi-note">
                        {{ $coverage['orders_with_amount'] }}
                        mit Betrag /
                        {{ $coverage['orders_without_amount'] }}
                        ohne Betrag
                    </span>
                @endif
            </article>

            <article class="statistics-kpi">
                <span class="statistics-kpi-label">
                    Abgeschlossene Verkäufe
                </span>

                <strong class="statistics-kpi-value">
                    {{ number_format(
                        (int) $summary['completed_orders'],
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

                <span class="statistics-kpi-note">
                    Tatsächlich erledigte Angebote
                </span>
            </article>

            <article class="statistics-kpi">
                <span class="statistics-kpi-label">
                    {{ $hasMissingAmounts
                        ? 'Ø erfasster Auftragswert'
                        : 'Ø Auftragswert' }}
                </span>

                @if ($summary['average_order_value'] === null)
                    <strong class="statistics-kpi-value is-missing">
                        —
                    </strong>
                @else
                    <strong class="statistics-kpi-value">
                        {{ number_format(
                            (float) $summary['average_order_value'],
                            2,
                            ',',
                            '.'
                        ) }}
                        €
                    </strong>
                @endif

                <span class="statistics-kpi-note">
                    Nur abgeschlossene Verkäufe
                </span>
            </article>

            <article class="statistics-kpi">
                <span class="statistics-kpi-label">
                    Angebote gesamt
                </span>

                <strong class="statistics-kpi-value">
                    {{ number_format(
                        (int) $summary['offers_count'],
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

                <span class="statistics-kpi-note">
                    Im gewählten Zeitraum
                </span>
            </article>
        </section>

        <section class="statistics-main-grid">
            <article class="statistics-card">
                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            {{ $hasMissingAmounts
                                ? 'Verkaufsentwicklung'
                                : 'Umsatzentwicklung' }}
                        </h2>

                        <p class="statistics-card-subtitle">
                            {{ $hasMissingAmounts
                                ? 'Anzahl abgeschlossener Verkäufe im Zeitraum'
                                : 'Erfasster Umsatz abgeschlossener Verkäufe' }}
                        </p>
                    </div>

                    <span class="statistics-inline-badge">
                        {{ $context['period_label'] }}
                    </span>
                </div>

                <div class="statistics-chart">
                    <canvas
                        data-sales-trend-chart
                        data-chart-mode="{{ $hasMissingAmounts
                            ? 'orders'
                            : 'revenue' }}"
                        data-sales-trend="{{ json_encode(
                            $charts['salesTrend'],
                            JSON_HEX_TAG
                            | JSON_HEX_APOS
                            | JSON_HEX_AMP
                            | JSON_HEX_QUOT
                        ) }}"
                    ></canvas>
                </div>
            </article>

            <article class="statistics-card">
                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            Angebotsstatus
                        </h2>

                        <p class="statistics-card-subtitle">
                            Verteilung im aktuellen Filter
                        </p>
                    </div>
                </div>

                <div class="statistics-status-list">
                    @foreach ($summary['status_counts'] as $status => $count)
                        <div class="statistics-status-row">
                            <span>
                                {{ $statusLabels[$status] ?? $status }}
                            </span>

                            <strong>
                                {{ number_format(
                                    (int) $count,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="statistics-half-grid">
            <article class="statistics-card">
                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            Top 10 Produkte
                        </h2>

                        <p class="statistics-card-subtitle">
                            Nach Verkauf, Menge und – soweit vorhanden –
                            erfasstem Umsatz
                        </p>
                    </div>
                </div>

                <div class="statistics-table-scroll">
                    <table class="statistics-table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Menge</th>
                                <th>Verkäufe</th>
                                <th>Umsatz</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($productStats['top'] ?? [] as $row)
                                <tr>
                                    <td>
                                        @if ($row->product_id)
                                            <a
                                                class="statistics-table-link"
                                                href="{{ route(
                                                    'products.show',
                                                    $row->product_id
                                                ) }}"
                                            >
                                                {{ $row->product_name }}
                                            </a>
                                        @else
                                            {{ $row->product_name }}
                                        @endif
                                    </td>

                                    <td>
                                        {{ number_format(
                                            (float) $row->sold_quantity,
                                            2,
                                            ',',
                                            '.'
                                        ) }}
                                        {{ $unitLabels[$row->unit]
                                            ?? $row->unit }}
                                    </td>

                                    <td>
                                        {{ number_format(
                                            (int) $row->orders_count,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td>
                                        @if (
                                            $hasMissingAmounts
                                            && (float) $row->revenue <= 0
                                        )
                                            <span class="statistics-money-missing">
                                                —
                                            </span>
                                        @else
                                            {{ number_format(
                                                (float) $row->revenue,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                            €
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="statistics-empty"
                                    >
                                        Keine abgeschlossenen Verkäufe
                                        im gewählten Zeitraum.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="statistics-card">
                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            Top 10 Kunden
                        </h2>

                        <p class="statistics-card-subtitle">
                            Nach abgeschlossenen Verkäufen und
                            erfasstem Umsatz
                        </p>
                    </div>
                </div>

                <div class="statistics-table-scroll">
                    <table class="statistics-table">
                        <thead>
                            <tr>
                                <th>Kunde</th>
                                <th>Verkäufe</th>
                                <th>Umsatz</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($customerStats['top'] ?? [] as $row)
                                <tr>
                                    <td>
                                        {{ $row['company_name']
                                            ?? 'Unbekannt' }}
                                    </td>

                                    <td>
                                        {{ number_format(
                                            (int) ($row['orders_count'] ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td>
                                        @if (
                                            $hasMissingAmounts
                                            && (float) ($row['revenue'] ?? 0) <= 0
                                        )
                                            <span class="statistics-money-missing">
                                                —
                                            </span>
                                        @else
                                            {{ number_format(
                                                (float) ($row['revenue'] ?? 0),
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                            €
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="3"
                                        class="statistics-empty"
                                    >
                                        Keine Kundendaten im
                                        gewählten Zeitraum.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="statistics-bottom-grid">
            <article class="statistics-card">
                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            Letzte abgeschlossene Verkäufe
                        </h2>

                        <p class="statistics-card-subtitle">
                            Die letzten Verkäufe innerhalb des
                            aktuellen Filters
                        </p>
                    </div>
                </div>

                <div class="statistics-table-scroll">
                    <table class="statistics-table">
                        <thead>
                            <tr>
                                <th>Angebot</th>
                                <th>Datum</th>
                                <th>Kunde</th>
                                <th>Mitarbeiter</th>
                                <th>Betrag</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($recentSales as $offer)
                                <tr>
                                    <td>
                                        <a
                                            class="statistics-table-link"
                                            href="{{ route(
                                                'offers.show',
                                                $offer
                                            ) }}"
                                        >
                                            {{ $offer->offer_number }}
                                        </a>
                                    </td>

                                    <td>
                                        {{ ($offer->completed_at
                                            ?? $offer->created_at)
                                            ?->format('d.m.Y H:i') }}
                                    </td>

                                    <td>
                                        {{ $offer->customer?->company_name
                                            ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $offer->user?->name ?? '—' }}
                                    </td>

                                    <td>
                                        @if ((float) $offer->total <= 0)
                                            <span class="statistics-money-missing">
                                                Nicht erfasst
                                            </span>
                                        @else
                                            {{ number_format(
                                                (float) $offer->total,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                            €
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="statistics-empty"
                                    >
                                        Keine abgeschlossenen Verkäufe
                                        im gewählten Zeitraum.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="statistics-card">
                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            Lagerübersicht
                        </h2>

                        <p class="statistics-card-subtitle">
                            Aktueller Bestandsstatus
                        </p>
                    </div>
                </div>

                <div class="statistics-stock-grid">
                    <div class="statistics-stock-item">
                        <span>Gesamtbestand</span>
                        <strong>
                            {{ number_format(
                                (float) ($stock['total_stock'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <div class="statistics-stock-item">
                        <span>Reserviert</span>
                        <strong>
                            {{ number_format(
                                (float) ($stock['reserved_stock'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <div class="statistics-stock-item">
                        <span>Verfügbar</span>
                        <strong>
                            {{ number_format(
                                (float) ($stock['available_stock'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <div class="statistics-stock-item">
                        <span>Niedrig / kritisch</span>
                        <strong>
                            {{ (int) ($stock['low_count'] ?? 0)
                                + (int) ($stock['critical_count'] ?? 0) }}
                        </strong>
                    </div>
                </div>

                <div class="statistics-card-head">
                    <div>
                        <h2 class="statistics-card-title">
                            Produkte ohne Verkauf
                        </h2>

                        <p class="statistics-card-subtitle">
                            Im aktuellen Filter
                        </p>
                    </div>
                </div>

                <div class="statistics-table-scroll">
                    <table class="statistics-table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Code</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse (
                                $productStats['products_without_sales'] ?? []
                                as $row
                            )
                                <tr>
                                    <td>
                                        <a
                                            class="statistics-table-link"
                                            href="{{ route(
                                                'products.show',
                                                $row->id
                                            ) }}"
                                        >
                                            {{ $row->name }}
                                        </a>
                                    </td>

                                    <td>
                                        {{ $row->product_code }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="2"
                                        class="statistics-empty"
                                    >
                                        Keine Produkte ohne Verkauf.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>

    <script
        src="{{ asset('js/statistics-runtime.js') }}"
        defer
    ></script>
</x-layouts.premium>
