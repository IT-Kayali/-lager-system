<x-layouts.premium title="Warnungen" subtitle="Bestandswarnungen mit Suche, Statusfilter und Excel-Export.">
    <section class="premium-grid premium-grid-4" style="margin-bottom:22px;">
        <a href="{{ route('products.index') }}" class="premium-stat-card" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="premium-stat-value">{{ $summary['all'] }}</div>
            <div class="premium-stat-label">Alle Produkte</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="premium-stat-card warning-card-total" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-exclamation-lg"></i></div>
            <div class="premium-stat-value">{{ $summary['warning'] }}</div>
            <div class="premium-stat-label">Warnungen gesamt</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="premium-stat-card warning-card-low" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="premium-stat-value">{{ $summary['low'] }}</div>
            <div class="premium-stat-label">Niedrig</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="premium-stat-card warning-card-critical" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="premium-stat-value">{{ $summary['critical'] }}</div>
            <div class="premium-stat-label">Kritisch</div>
        </a>
    </section>

    @php
        $hasWarningFilters = ! empty($search)
            || ($searchField ?? 'all') !== 'all'
            || ($exact ?? false)
            || ($filter ?? 'warning') !== 'warning';
    @endphp

    <section class="erp-list-toolbar">
        <div class="erp-list-filter-card">
            <form method="GET" action="{{ route('warnings.index') }}" class="erp-list-filter-form">
                <div class="erp-list-search">
                    <i class="bi bi-search"></i>
                    <input name="search" value="{{ $search ?? '' }}" class="premium-input" placeholder="Produkt oder Lieferant suchen...">
                </div>

                <select name="search_field" class="premium-select erp-list-select" aria-label="Suchfeld auswählen">
                    <option value="all" @selected(($searchField ?? 'all') === 'all')>Alle</option>
                    <option value="name" @selected(($searchField ?? 'all') === 'name')>Produkt</option>
                    <option value="manufacturer" @selected(($searchField ?? 'all') === 'manufacturer')>Fake Name</option>
                    <option value="code" @selected(($searchField ?? 'all') === 'code')>Code-Nummer</option>
                    <option value="supplier" @selected(($searchField ?? 'all') === 'supplier')>Lieferant</option>
                </select>

                <select name="filter" class="premium-select erp-list-select" aria-label="Bestandsstatus filtern">
                    <option value="warning" @selected(($filter ?? 'warning') === 'warning')>Alle Warnungen</option>
                    <option value="low" @selected(($filter ?? 'warning') === 'low')>Niedrig</option>
                    <option value="critical" @selected(($filter ?? 'warning') === 'critical')>Kritisch</option>
                    <option value="all" @selected(($filter ?? 'warning') === 'all')>Alle Produkte</option>
                </select>

                <label class="erp-list-exact">
                    <input type="checkbox" name="exact" value="1" @checked($exact ?? false)>
                    <span>Exakter Wert</span>
                </label>

                <button class="premium-btn" type="submit"><i class="bi bi-search"></i> Suchen</button>

                @if ($hasWarningFilters)
                    <a href="{{ route('warnings.index') }}" class="premium-btn"><i class="bi bi-x-lg"></i> Zurücksetzen</a>
                @endif
            </form>
        </div>

        <div class="erp-list-actions">
            <a
                href="{{ route('warnings.export', [
                    'filter' => $filter,
                    'search' => $search ?? '',
                    'search_field' => $searchField ?? 'all',
                    'exact' => ($exact ?? false) ? 1 : null,
                ]) }}"
                class="premium-btn gold"
            >
                <i class="bi bi-file-earmark-excel"></i>
                Excel exportieren
            </a>
        </div>
    </section>

    <section class="erp-list-card">
        @if ($products->isEmpty())
            <div class="erp-list-empty">
                <i class="bi bi-shield-check"></i>
                <strong>Keine passenden Bestandswarnungen.</strong>
                <span>Passe die Filter an oder prüfe einen anderen Status.</span>
            </div>
        @else
            <div class="premium-table-wrap erp-list-table-shell warnings-table-wrap">
                <table class="premium-table warnings-table">
                    <colgroup>
                        <col style="width:13%;">
                        <col style="width:15%;">
                        <col style="width:10%;">
                        <col style="width:7%;">
                        <col style="width:12%;">
                        <col style="width:8%;">
                        <col style="width:8%;">
                        <col style="width:8%;">
                        <col style="width:9%;">
                        <col style="width:9%;">
                        <col style="width:7%;">
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Fake Name</th>
                            <th>Code-Nummer</th>
                            <th>Einheit</th>
                            <th>Lieferant</th>
                            <th>Gesamt</th>
                            <th>Reserviert</th>
                            <th>Verfügbar</th>
                            <th>Mindestbestand</th>
                            <th>Warnschwelle</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $row)
                            @php
                                $product = $row['product'];
                                $supplier = $product->supplierRecord;
                                $labels = ['ok' => 'OK', 'low' => 'Niedrig', 'critical' => 'Kritisch'];
                            @endphp

                            <tr class="warning-row-{{ $row['status'] }}">
                                <td><a href="{{ route('products.show', $product) }}" class="warning-product-link">{{ $product->name ?: '—' }}</a></td>
                                <td>{{ $product->manufacturer_designation ?: '—' }}</td>
                                <td>{{ $product->serial_number ?: ($product->product_code ?: '—') }}</td>
                                <td>{{ $product->unitLabel('de') }}</td>
                                <td>{{ $supplier?->company_name ?: ($product->supplier ?: '—') }}</td>
                                <td>{{ \App\Support\GermanNumber::format($row['total_stock']) }}</td>
                                <td>{{ \App\Support\GermanNumber::format($row['reserved_stock']) }}</td>
                                <td>{{ \App\Support\GermanNumber::format($row['available_stock']) }}</td>
                                <td>{{ \App\Support\GermanNumber::format($row['minimum_stock']) }}</td>
                                <td>{{ \App\Support\GermanNumber::format($row['warning_threshold']) }}</td>
                                <td><span class="premium-badge {{ $row['status'] }}">{{ $labels[$row['status']] ?? $row['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- CSP static styles moved to public/css/csp-static-bulk.css: resources/views/pages/warnings/index.blade.php --}}
</x-layouts.premium>