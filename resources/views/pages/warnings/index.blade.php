<x-layouts.premium title="Bestandswarnungen" subtitle="Überblick über kritische und niedrige Lagerbestände.">
    <section class="warnings-summary-grid">
        <a href="{{ route('products.index') }}" class="warnings-summary-card">
            <span class="warnings-summary-icon neutral"><i class="bi bi-box-seam"></i></span>
            <span class="warnings-summary-label">Alle Produkte</span>
            <strong>{{ $summary['all'] }}</strong>
            <small>Gesamter Artikelbestand</small>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="warnings-summary-card {{ $filter === 'warning' ? 'active' : '' }}">
            <span class="warnings-summary-icon warning"><i class="bi bi-exclamation-lg"></i></span>
            <span class="warnings-summary-label">Warnungen gesamt</span>
            <strong>{{ $summary['warning'] }}</strong>
            <small>Niedrig + kritisch</small>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="warnings-summary-card {{ $filter === 'low' ? 'active' : '' }}">
            <span class="warnings-summary-icon low"><i class="bi bi-arrow-down-circle"></i></span>
            <span class="warnings-summary-label">Niedrig</span>
            <strong>{{ $summary['low'] }}</strong>
            <small>Baldige Nachbestellung</small>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="warnings-summary-card critical {{ $filter === 'critical' ? 'active' : '' }}">
            <span class="warnings-summary-icon critical"><i class="bi bi-exclamation-triangle"></i></span>
            <span class="warnings-summary-label">Kritisch</span>
            <strong>{{ $summary['critical'] }}</strong>
            <small>Sofort handeln</small>
        </a>
    </section>

    <section class="warnings-table-card">
        <div class="warnings-table-header">
            <div>
                <h2>Warnungsliste</h2>
                <p>OK-Produkte werden hier nicht angezeigt. Kritisch ist Mindestbestand oder darunter.</p>
            </div>

            <div class="warnings-header-actions">
                <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="warnings-filter-pill {{ $filter === 'warning' ? 'active' : '' }}">
                    Alle Warnungen
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="warnings-filter-pill low {{ $filter === 'low' ? 'active' : '' }}">
                    Niedrig
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="warnings-filter-pill critical {{ $filter === 'critical' ? 'active' : '' }}">
                    Kritisch
                </a>

                <a href="{{ route('products.index') }}" class="premium-btn gold">
                    <i class="bi bi-box-seam"></i>
                    Alle Produkte öffnen
                </a>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="warnings-empty-state">
                <i class="bi bi-check2-circle"></i>
                <strong>Keine Bestandswarnungen vorhanden.</strong>
                <span>Alle Produkte sind aktuell im grünen Bereich.</span>
            </div>
        @else
            <div class="premium-table-wrap warnings-table-wrap">
                <table class="premium-table warnings-table">
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

                            <tr class="warning-row warning-row-{{ $row['status'] }}">
                                <td>
                                    <strong>{{ $productName }}</strong>
                                    <div class="premium-muted">{{ $product->product_code ?? 'Ohne Code' }}</div>
                                </td>

                                <td>{{ $manufacturerName }}</td>

                                <td>{{ number_format((float) $row['total_stock'], 2, ',', '.') }}</td>

                                <td>{{ number_format((float) $row['reserved_stock'], 2, ',', '.') }}</td>

                                <td>
                                    <strong class="warning-stock-value {{ $row['status'] }}">
                                        {{ number_format((float) $row['available_stock'], 2, ',', '.') }}
                                    </strong>
                                </td>

                                <td>{{ number_format((float) $row['minimum_stock'], 2, ',', '.') }}</td>

                                <td>{{ number_format((float) $row['max_reservable'], 2, ',', '.') }}</td>

                                <td>
                                    <span class="warning-status-pill {{ $row['status'] }}">
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

    <style>
        .warnings-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .warnings-summary-card,
        .warnings-table-card {
            background: rgba(255, 255, 255, .88);
            border: 1px solid #d9c9ae;
            border-radius: 18px;
            box-shadow: 0 18px 48px rgba(33, 29, 23, .08);
        }

        .warnings-summary-card {
            position: relative;
            display: grid;
            gap: 8px;
            min-height: 156px;
            padding: 22px;
            color: #111;
            text-decoration: none;
            overflow: hidden;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .warnings-summary-card::after {
            content: '';
            position: absolute;
            inset: auto -34px -44px auto;
            width: 116px;
            height: 116px;
            border-radius: 50%;
            background: rgba(243, 232, 190, .55);
        }

        .warnings-summary-card:hover,
        .warnings-summary-card.active {
            transform: translateY(-2px);
            border-color: #c9a227;
            box-shadow: 0 20px 54px rgba(33, 29, 23, .12);
        }

        .warnings-summary-card.critical {
            border-left: 5px solid #cc1f1a;
        }

        .warnings-summary-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #7b5c00;
            font-size: 20px;
            z-index: 1;
        }

        .warnings-summary-icon.low {
            background: #fff4ce;
            color: #9a6a00;
        }

        .warnings-summary-icon.critical {
            background: #ffe0df;
            color: #b91c1c;
        }

        .warnings-summary-label {
            color: #6f665b;
            font-size: 12px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .06em;
            z-index: 1;
        }

        .warnings-summary-card strong {
            font-size: 32px;
            font-weight: 950;
            line-height: 1;
            z-index: 1;
        }

        .warnings-summary-card small {
            color: #6f665b;
            font-size: 13px;
            font-weight: 800;
            z-index: 1;
        }

        .warnings-table-card {
            overflow: hidden;
        }

        .warnings-table-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 22px 24px 18px;
            border-bottom: 1px solid #e7dece;
        }

        .warnings-table-header h2 {
            margin: 0;
            color: #111;
            font-size: 22px;
            font-weight: 950;
            letter-spacing: -.02em;
        }

        .warnings-table-header p {
            margin: 5px 0 0;
            color: #6f665b;
            font-size: 14px;
            font-weight: 700;
        }

        .warnings-header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .warnings-filter-pill {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 9px 13px;
            border-radius: 999px;
            border: 1px solid #d9c9ae;
            background: #fffdf8;
            color: #211d17;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .warnings-filter-pill.active,
        .warnings-filter-pill:hover {
            border-color: #c9a227;
            background: #d5aa1f;
            color: #111;
        }

        .warnings-filter-pill.critical.active,
        .warnings-filter-pill.critical:hover {
            border-color: #ef9a9a;
            background: #ffe0df;
            color: #991b1b;
        }

        .warnings-table-wrap {
            margin-top: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

        .warnings-table {
            min-width: 1160px;
        }

        .warning-row-critical td {
            background: #fffafa !important;
        }

        .warning-stock-value.low {
            color: #b77900;
        }

        .warning-stock-value.critical {
            color: #c81e1e;
        }

        .warning-status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #d7c7ab;
            background: #f4efe5;
            color: #3d352b;
            font-size: 13px;
            font-weight: 950;
        }

        .warning-status-pill.low {
            border-color: #f3d783;
            background: #fff4ce;
            color: #8a5a00;
        }

        .warning-status-pill.critical {
            border-color: #ffc0bd;
            background: #ffe0df;
            color: #991b1b;
        }

        .warning-actions {
            justify-content: flex-end !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
        }

        .warnings-empty-state {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 46px 22px;
            color: #6f665b;
            text-align: center;
        }

        .warnings-empty-state i {
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: #dff4df;
            color: #177a22;
            font-size: 24px;
        }

        .warnings-empty-state strong {
            color: #111;
            font-size: 19px;
        }

        @media (max-width: 1100px) {
            .warnings-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .warnings-table-header {
                align-items: stretch;
                flex-direction: column;
            }

            .warnings-header-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 680px) {
            .warnings-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-layouts.premium>
