<x-layouts.premium title="Statistik" subtitle="Auswertung von Umsatz, Kunden, Angeboten und Lagerbestand.">
    <section class="statistics-filter-card">
        <form method="GET" action="{{ route('statistics.index') }}">
            <div class="statistics-filter-grid">
                <div class="statistics-filter-field">
                    <label>Zeitraum</label>
                    <select name="period" class="premium-select">
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="statistics-filter-field compact">
                    <label>Von</label>
                    <input name="date_from" type="date" class="premium-input" value="{{ $filters['date_from'] }}">
                </div>

                <div class="statistics-filter-field compact">
                    <label>Bis</label>
                    <input name="date_to" type="date" class="premium-input" value="{{ $filters['date_to'] }}">
                </div>

                <div class="statistics-filter-field">
                    <label>Status</label>
                    <select name="status" class="premium-select">
                        <option value="">Alle Status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="statistics-filter-field">
                    <label>Kundengruppe</label>
                    <select name="customer_group_id" class="premium-select">
                        <option value="">Alle Gruppen</option>
                        @foreach ($customerGroups as $group)
                            <option value="{{ $group->id }}" @selected((string) $filters['customer_group_id'] === (string) $group->id)>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="statistics-filter-field wide">
                    <label>Kunde</label>
                    <select name="customer_id" class="premium-select">
                        <option value="">Alle Kunden</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>
                                {{ $customer->customer_number }} — {{ $customer->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="statistics-filter-field wide">
                    <label>Produkt</label>
                    <select name="product_id" class="premium-select">
                        <option value="">Alle Produkte</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) $filters['product_id'] === (string) $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="statistics-filter-actions">
                    <button class="premium-btn gold" type="submit">
                        <i class="bi bi-funnel"></i>
                        Anwenden
                    </button>

                    <a href="{{ route('statistics.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="statistics-kpi-grid">
        <div class="statistics-kpi-card">
            <span class="statistics-kpi-icon"><i class="bi bi-currency-euro"></i></span>
            <span>Umsatz erledigt</span>
            <strong>{{ number_format($financialStats['completed_revenue'], 2, ',', '.') }} €</strong>
        </div>

        <div class="statistics-kpi-card">
            <span class="statistics-kpi-icon"><i class="bi bi-clock-history"></i></span>
            <span>Aktiv reservierter Wert</span>
            <strong>{{ number_format($financialStats['active_reserved_value'], 2, ',', '.') }} €</strong>
        </div>

        <div class="statistics-kpi-card">
            <span class="statistics-kpi-icon"><i class="bi bi-receipt"></i></span>
            <span>Angebote im Filter</span>
            <strong>{{ $financialStats['offers_count'] }}</strong>
        </div>

        <div class="statistics-kpi-card">
            <span class="statistics-kpi-icon"><i class="bi bi-box-seam"></i></span>
            <span>Verfügbarer Bestand</span>
            <strong>{{ number_format($stockSummary['available_stock'], 2, ',', '.') }}</strong>
        </div>
    </section>

    <section class="statistics-chart-grid two">
        <div class="statistics-card statistics-chart-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Angebote nach Status</h2>
                    <p>Statusverteilung im aktuellen Filter</p>
                </div>
                <i class="bi bi-pie-chart"></i>
            </div>
            <div class="statistics-chart-box"><canvas id="offerStatusChart"></canvas></div>
        </div>

        <div class="statistics-card statistics-chart-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Lagerstatus</h2>
                    <p>OK, niedrige und kritische Bestände</p>
                </div>
                <i class="bi bi-boxes"></i>
            </div>
            <div class="statistics-chart-box"><canvas id="stockStatusChart"></canvas></div>
        </div>
    </section>

    <section class="statistics-card statistics-chart-card large">
        <div class="statistics-card-header">
            <div>
                <h2>Monatsumsatz</h2>
                <p>Entwicklung der erledigten Angebotswerte</p>
            </div>
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="statistics-chart-box"><canvas id="monthlyRevenueChart"></canvas></div>
    </section>

    <section class="statistics-chart-grid two">
        <div class="statistics-card statistics-chart-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Top-Produkte nach Umsatz</h2>
                    <p>Stärkste Produkte nach Wert</p>
                </div>
                <i class="bi bi-bar-chart"></i>
            </div>
            <div class="statistics-chart-box"><canvas id="topProductsRevenueChart"></canvas></div>
        </div>

        <div class="statistics-card statistics-chart-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Top-Produkte nach Menge</h2>
                    <p>Stärkste Produkte nach Absatzmenge</p>
                </div>
                <i class="bi bi-bar-chart-steps"></i>
            </div>
            <div class="statistics-chart-box"><canvas id="topProductsQuantityChart"></canvas></div>
        </div>
    </section>

    <section class="statistics-chart-grid two">
        <div class="statistics-card statistics-chart-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Top-Kunden nach Umsatz</h2>
                    <p>Kunden mit höchstem Umsatz</p>
                </div>
                <i class="bi bi-people"></i>
            </div>
            <div class="statistics-chart-box"><canvas id="topCustomersRevenueChart"></canvas></div>
        </div>

        <div class="statistics-card statistics-chart-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Umsatz nach Kundengruppe</h2>
                    <p>Verteilung nach Gold, Silber und Diamond</p>
                </div>
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="statistics-chart-box"><canvas id="customerGroupRevenueChart"></canvas></div>
        </div>
    </section>

    <section class="statistics-table-grid two">
        <div class="statistics-card statistics-table-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Top-Produkte Tabelle</h2>
                    <p>Produktleistung im Filterzeitraum</p>
                </div>
            </div>

            <div class="premium-table-wrap statistics-table-wrap">
                <table class="premium-table statistics-table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Menge</th>
                            <th>Umsatz</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topProducts as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product['product_name'] }}</strong>
                                    <div class="premium-muted">{{ $product['product_code'] }}</div>
                                </td>
                                <td>{{ number_format((float) $product['sold_quantity'], 2, ',', '.') }}</td>
                                <td><strong>{{ number_format((float) $product['revenue'], 2, ',', '.') }} €</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"><div class="statistics-empty-line">Keine Produktdaten im aktuellen Filter.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="statistics-card statistics-table-card">
            <div class="statistics-card-header">
                <div>
                    <h2>Top-Kunden Tabelle</h2>
                    <p>Kundenleistung im Filterzeitraum</p>
                </div>
            </div>

            <div class="premium-table-wrap statistics-table-wrap">
                <table class="premium-table statistics-table">
                    <thead>
                        <tr>
                            <th>Kunde</th>
                            <th>Angebote</th>
                            <th>Umsatz</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topCustomers as $customer)
                            <tr>
                                <td>
                                    <strong>{{ $customer['company_name'] }}</strong>
                                    <div class="premium-muted">{{ $customer['customer_number'] }}</div>
                                </td>
                                <td>{{ $customer['offers_count'] }}</td>
                                <td><strong>{{ number_format((float) $customer['revenue'], 2, ',', '.') }} €</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"><div class="statistics-empty-line">Keine Kundendaten im aktuellen Filter.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="statistics-card statistics-table-card">
        <div class="statistics-card-header">
            <div>
                <h2>Letzte erledigte Angebote</h2>
                <p>Zuletzt abgeschlossene Vorgänge im aktuellen Filter</p>
            </div>
        </div>

        <div class="premium-table-wrap statistics-table-wrap">
            <table class="premium-table statistics-table recent-offers-table">
                <thead>
                    <tr>
                        <th>Angebot</th>
                        <th>Kunde</th>
                        <th>Erledigt am</th>
                        <th>Gesamt</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentCompletedOffers as $offer)
                        <tr>
                            <td><span class="premium-code">{{ $offer->offer_number }}</span></td>
                            <td>{{ $offer->customer?->company_name }}</td>
                            <td>{{ $offer->completed_at?->format('d.m.Y H:i') ?: '—' }}</td>
                            <td><strong>{{ number_format((float) $offer->total, 2, ',', '.') }} €</strong></td>
                            <td>
                                <a href="{{ route('offers.show', $offer) }}" class="premium-icon-btn" title="Anzeigen">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="statistics-empty-line">Keine erledigten Angebote im aktuellen Filter.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .statistics-filter-card,
        .statistics-card,
        .statistics-kpi-card {
            background: rgba(255, 255, 255, .88);
            border: 1px solid #d9c9ae;
            border-radius: 18px;
            box-shadow: 0 18px 48px rgba(33, 29, 23, .08);
        }

        .statistics-filter-card {
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .statistics-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(170px, 1fr));
            gap: 14px;
            align-items: end;
        }

        .statistics-filter-field {
            display: grid;
            gap: 7px;
        }

        .statistics-filter-field.wide {
            grid-column: span 2;
        }

        .statistics-filter-field label {
            color: #211d17;
            font-size: 12px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .045em;
        }

        .statistics-filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .statistics-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .statistics-kpi-card {
            position: relative;
            display: grid;
            gap: 9px;
            min-height: 156px;
            padding: 22px;
            overflow: hidden;
        }

        .statistics-kpi-card::after {
            content: '';
            position: absolute;
            inset: auto -34px -44px auto;
            width: 116px;
            height: 116px;
            border-radius: 50%;
            background: rgba(243, 232, 190, .55);
        }

        .statistics-kpi-icon {
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

        .statistics-kpi-card span:not(.statistics-kpi-icon) {
            color: #6f665b;
            font-size: 13px;
            font-weight: 850;
            z-index: 1;
        }

        .statistics-kpi-card strong {
            color: #111;
            font-size: 26px;
            font-weight: 950;
            line-height: 1.1;
            z-index: 1;
        }

        .statistics-chart-grid,
        .statistics-table-grid {
            display: grid;
            gap: 22px;
            margin-bottom: 22px;
        }

        .statistics-chart-grid.two,
        .statistics-table-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .statistics-card {
            overflow: hidden;
            margin-bottom: 22px;
        }

        .statistics-card-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 22px 24px 18px;
            border-bottom: 1px solid #e7dece;
        }

        .statistics-card-header h2 {
            margin: 0;
            color: #111;
            font-size: 22px;
            font-weight: 950;
            letter-spacing: -.02em;
        }

        .statistics-card-header p {
            margin: 5px 0 0;
            color: #6f665b;
            font-size: 14px;
            font-weight: 700;
        }

        .statistics-card-header > i {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #7b5c00;
            font-size: 19px;
            flex: 0 0 auto;
        }

        .statistics-chart-box {
            height: 320px;
            padding: 22px;
        }

        .statistics-chart-card.large .statistics-chart-box {
            height: 360px;
        }

        .statistics-table-wrap {
            margin-top: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

        .statistics-table {
            min-width: 640px;
        }

        .recent-offers-table {
            min-width: 860px;
        }

        .statistics-empty-line {
            padding: 18px;
            color: #6f665b;
            font-weight: 800;
            text-align: center;
        }

        @media (max-width: 1180px) {
            .statistics-filter-grid,
            .statistics-kpi-grid,
            .statistics-chart-grid.two,
            .statistics-table-grid.two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .statistics-filter-grid,
            .statistics-kpi-grid,
            .statistics-chart-grid.two,
            .statistics-table-grid.two {
                grid-template-columns: 1fr;
            }

            .statistics-filter-field.wide {
                grid-column: auto;
            }

            .statistics-filter-actions .premium-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const charts = @json($charts);

            const palette = [
                '#e3ca6e',
                '#212121',
                '#f59e0b',
                '#22c55e',
                '#ef4444',
                '#64748b',
                '#8b5cf6',
                '#06b6d4',
                '#84cc16',
                '#f97316'
            ];

            function renderChart(id, type, labels, data, label, extraOptions = {}) {
                const canvas = document.getElementById(id);

                if (!canvas || !window.Chart) {
                    return;
                }

                new Chart(canvas, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: data,
                            backgroundColor: type === 'line' ? 'rgba(227, 202, 110, 0.22)' : palette,
                            borderColor: type === 'line' ? '#e3ca6e' : palette,
                            borderWidth: 2,
                            tension: 0.32,
                            fill: type === 'line'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: type === 'bar' || type === 'line' ? 'top' : 'bottom'
                            }
                        },
                        scales: type === 'bar' || type === 'line' ? {
                            y: {
                                beginAtZero: true
                            }
                        } : {},
                        ...extraOptions
                    }
                });
            }

            renderChart(
                'offerStatusChart',
                'doughnut',
                charts.offerStatus.labels,
                charts.offerStatus.data,
                'Angebote'
            );

            renderChart(
                'stockStatusChart',
                'doughnut',
                charts.stockStatus.labels,
                charts.stockStatus.data,
                'Lagerstatus'
            );

            renderChart(
                'monthlyRevenueChart',
                'line',
                charts.monthlyRevenue.labels,
                charts.monthlyRevenue.data,
                'Umsatz'
            );

            renderChart(
                'topProductsRevenueChart',
                'bar',
                charts.topProductsRevenue.labels,
                charts.topProductsRevenue.data,
                'Umsatz'
            );

            renderChart(
                'topProductsQuantityChart',
                'bar',
                charts.topProductsQuantity.labels,
                charts.topProductsQuantity.data,
                'Menge'
            );

            renderChart(
                'topCustomersRevenueChart',
                'bar',
                charts.topCustomersRevenue.labels,
                charts.topCustomersRevenue.data,
                'Umsatz'
            );

            renderChart(
                'customerGroupRevenueChart',
                'doughnut',
                charts.customerGroupRevenue.labels,
                charts.customerGroupRevenue.data,
                'Umsatz'
            );
        });
    </script>
</x-layouts.premium>
