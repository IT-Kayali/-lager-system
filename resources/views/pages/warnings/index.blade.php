<x-layouts.premium title="Warnungen" subtitle="Nur Produkte mit niedrigem oder kritischem Bestand werden hier angezeigt.">
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

    <section class="premium-card warnings-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Bestandswarnungen</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    OK-Produkte werden hier nicht angezeigt. Statuslogik: Niedrig bis 10% über Mindestbestand, Kritisch bei Mindestbestand oder darunter.
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="premium-btn warning-filter-total {{ $filter === 'warning' ? 'active-warning-filter' : '' }}">
                    Warnungen
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="premium-btn warning-filter-low {{ $filter === 'low' ? 'active-warning-filter' : '' }}">
                    Niedrig
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="premium-btn warning-filter-critical {{ $filter === 'critical' ? 'active-warning-filter' : '' }}">
                    Kritisch
                </a>

                <a href="{{ route('products.index') }}" class="premium-btn">
                    <i class="bi bi-box-seam"></i>
                    Alle Produkte öffnen
                </a>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="premium-placeholder">
                Keine Bestandswarnungen vorhanden. Alle Produkte sind aktuell im grünen Bereich.
            </div>
        @else
            <div class="premium-table-wrap warnings-table-wrap">
                <table class="premium-table warnings-table">
                    <colgroup>
                        <col style="width:18%;">
                        <col style="width:13%;">
                        <col style="width:11%;">
                        <col style="width:11%;">
                        <col style="width:11%;">
                        <col style="width:12%;">
                        <col style="width:12%;">
                        <col style="width:7%;">
                        <col style="width:5%;">
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Hersteller</th>
                            <th>Gesamt</th>
                            <th>Reserviert</th>
                            <th>Verfügbar</th>
                            <th>Mindestbestand</th>
                            <th>Max. reservierbar</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $row)
                            @php
                                $product = $row['product'];

                                $productName = data_get($product, 'name')
                                    ?: data_get($product, 'designation')
                                    ?: data_get($product, 'product_name')
                                    ?: '—';

                                $manufacturerName = data_get($product, 'manufacturer')
                                    ?: data_get($product, 'manufacturer_name')
                                    ?: data_get($product, 'manufacturer_designation')
                                    ?: data_get($product, 'supplierRecord.company_name')
                                    ?: '—';

                                $labels = [
                                    'ok' => 'OK',
                                    'low' => 'Niedrig',
                                    'critical' => 'Kritisch',
                                ];
                            @endphp

                            <tr class="warning-row-{{ $row['status'] }}">
                                <td>
                                    <strong>{{ $productName }}</strong>
                                </td>

                                <td>{{ $manufacturerName }}</td>

                                <td>{{ number_format((float) $row['total_stock'], 2, ',', '.') }}</td>

                                <td>{{ number_format((float) $row['reserved_stock'], 2, ',', '.') }}</td>

                                <td>{{ number_format((float) $row['available_stock'], 2, ',', '.') }}</td>

                                <td>{{ number_format((float) $row['minimum_stock'], 2, ',', '.') }}</td>

                                <td>{{ number_format((float) $row['max_reservable'], 2, ',', '.') }}</td>

                                <td>
                                    <span class="premium-badge {{ $row['status'] }}">
                                        {{ $labels[$row['status']] ?? $row['status'] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="premium-actions warning-actions">
                                        <a class="premium-icon-btn" href="{{ route('products.edit', $product) }}" title="Produkt bearbeiten">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <a class="premium-icon-btn" href="{{ route('branch-withdrawals.create', ['product_id' => $product->id]) }}" title="Filialausgang buchen">
                                            <i class="bi bi-shop"></i>
                                        </a>

                                        <a class="premium-icon-btn" href="{{ route('batches.create', ['product_id' => $product->id]) }}" title="Bestand buchen">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.premium>
