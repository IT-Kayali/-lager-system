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
        <div class="premium-toolbar warnings-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Bestandswarnungen</h2>
            </div>

            <div class="warnings-toolbar-actions">
                <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="premium-btn warning-filter-total {{ $filter === 'warning' ? 'active-warning-filter' : '' }}">
                    Warnungen
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="premium-btn warning-filter-low {{ $filter === 'low' ? 'active-warning-filter' : '' }}">
                    Niedrig
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="premium-btn warning-filter-critical {{ $filter === 'critical' ? 'active-warning-filter' : '' }}">
                    Kritisch
                </a>

                <a href="{{ route('warnings.export', ['filter' => $filter]) }}" class="premium-btn gold">
                    <i class="bi bi-file-earmark-excel"></i>
                    Excel exportieren
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
                            <th>Bezeichnung durch Hersteller</th>
                            <th>Code-Nummer</th>
                            <th>Einheit</th>
                            <th>Lieferant</th>
                            <th class="warning-number">Gesamt</th>
                            <th class="warning-number">Reserviert</th>
                            <th class="warning-number">Verfügbar</th>
                            <th class="warning-number">Mindestbestand</th>
                            <th class="warning-number">Warnschwelle</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $row)
                            @php
                                $product = $row['product'];
                                $supplier = $product->supplierRecord;
                                $labels = [
                                    'ok' => 'OK',
                                    'low' => 'Niedrig',
                                    'critical' => 'Kritisch',
                                ];
                            @endphp

                            <tr class="warning-row-{{ $row['status'] }}">
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="warning-product-link">
                                        {{ $product->name ?: '—' }}
                                    </a>
                                </td>
                                <td>{{ $product->manufacturer_designation ?: '—' }}</td>
                                <td>{{ $product->serial_number ?: '—' }}</td>
                                <td>{{ $product->unit ?: '—' }}</td>
                                <td>{{ $supplier?->company_name ?: ($product->supplier ?: '—') }}</td>
                                <td class="warning-number">{{ \App\Support\GermanNumber::format($row['total_stock']) }}</td>
                                <td class="warning-number">{{ \App\Support\GermanNumber::format($row['reserved_stock']) }}</td>
                                <td class="warning-number">{{ \App\Support\GermanNumber::format($row['available_stock']) }}</td>
                                <td class="warning-number">{{ \App\Support\GermanNumber::format($row['minimum_stock']) }}</td>
                                <td class="warning-number">{{ \App\Support\GermanNumber::format($row['warning_threshold']) }}</td>
                                <td>
                                    <span class="premium-badge {{ $row['status'] }}">
                                        {{ $labels[$row['status']] ?? $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <style>
        .warnings-toolbar {
            gap: 18px;
            align-items: center;
        }

        .warnings-toolbar-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .warnings-table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 18px;
        }

        .warnings-table {
            width: 100%;
            min-width: 1380px;
            table-layout: fixed;
        }

        .warnings-table th,
        .warnings-table td {
            padding-left: 14px;
            padding-right: 14px;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .warnings-table th {
            white-space: normal;
            line-height: 1.2;
            word-break: normal;
            overflow-wrap: normal;
        }

        .warnings-table td {
            white-space: nowrap;
        }

        .warnings-table .warning-number {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .warning-product-link {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: bottom;
            font-weight: 900;
            color: inherit;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .warnings-table .premium-badge {
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            .warnings-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .warnings-toolbar-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</x-layouts.premium>
