<x-layouts.premium title="Statistik" subtitle="Diagramme, Filter und Auswertung von Umsatz, Angeboten, Kunden, Produkten und Lagerbestand.">
    <section class="premium-card premium-filter-card">
        <form method="GET" action="{{ route('statistics.index') }}">
            <div class="premium-filter-grid">
                <div class="premium-form-field">
                    <label>Zeitraum</label>
                    <select name="period" class="premium-select">
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-form-field">
                    <label>Von</label>
                    <input name="date_from" type="date" class="premium-input" value="{{ $filters['date_from'] }}">
                </div>

                <div class="premium-form-field">
                    <label>Bis</label>
                    <input name="date_to" type="date" class="premium-input" value="{{ $filters['date_to'] }}">
                </div>

                <div class="premium-form-field">
                    <label>Status</label>
                    <select name="status" class="premium-select">
                        <option value="">Alle Status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-form-field">
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

                <div class="premium-form-field">
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

                <div class="premium-form-field">
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

                <div class="premium-form-field" style="display:flex; align-items:end; gap:10px;">
                    <button class="premium-btn gold" type="submit">
                        <i class="bi bi-funnel"></i>
                        Filtern
                    </button>

                    <a href="{{ route('statistics.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="premium-grid premium-grid-4" style="margin-bottom:22px;">
        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-currency-euro"></i></div>
            <div class="premium-stat-value">{{ number_format($financialStats['completed_revenue'], 2, ',', '.') }} €</div>
            <div class="premium-stat-label">Umsatz erledigt</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="premium-stat-value">{{ number_format($financialStats['active_reserved_value'], 2, ',', '.') }} €</div>
            <div class="premium-stat-label">Aktiv reservierter Wert</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="premium-stat-value">{{ $financialStats['offers_count'] }}</div>
            <div class="premium-stat-label">Angebote im Filter</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="premium-stat-value">{{ number_format($stockSummary['available_stock'], 3, ',', '.') }}</div>
            <div class="premium-stat-label">Verfügbarer Bestand</div>
        </div>
    </section>

    <section class="premium-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:22px;">
        <div class="premium-card premium-chart-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Angebote nach Status</h2>
            <div class="premium-chart-box">
                <canvas id="offerStatusChart"></canvas>
            </div>
        </div>

        <div class="premium-card premium-chart-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Lagerstatus</h2>
            <div class="premium-chart-box">
                <canvas id="stockStatusChart"></canvas>
            </div>
        </div>
    </section>

    <section class="premium-card premium-chart-card" style="margin-bottom:22px;">
        <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Monatsumsatz</h2>
        <div class="premium-chart-box">
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
    </section>

    <section class="premium-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:22px;">
        <div class="premium-card premium-chart-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Produkte nach Umsatz</h2>
            <div class="premium-chart-box">
                <canvas id="topProductsRevenueChart"></canvas>
            </div>
        </div>

        <div class="premium-card premium-chart-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Produkte nach Menge</h2>
            <div class="premium-chart-box">
                <canvas id="topProductsQuantityChart"></canvas>
            </div>
        </div>
    </section>

    <section class="premium-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:22px;">
        <div class="premium-card premium-chart-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Kunden nach Umsatz</h2>
            <div class="premium-chart-box">
                <canvas id="topCustomersRevenueChart"></canvas>
            </div>
        </div>

        <div class="premium-card premium-chart-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Umsatz nach Kundengruppe</h2>
            <div class="premium-chart-box">
                <canvas id="customerGroupRevenueChart"></canvas>
            </div>
        </div>
    </section>

    <section class="premium-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:22px;">
        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Produkte Tabelle</h2>

            <div class="premium-table-wrap">
                <table class="premium-table">
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
                                <td>{{ number_format((float) $product['sold_quantity'], 3, ',', '.') }}</td>
                                <td>{{ number_format((float) $product['revenue'], 2, ',', '.') }} €</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="premium-muted">Keine Produktdaten im aktuellen Filter.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Kunden Tabelle</h2>

            <div class="premium-table-wrap">
                <table class="premium-table">
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
                                <td>{{ number_format((float) $customer['revenue'], 2, ',', '.') }} €</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="premium-muted">Keine Kundendaten im aktuellen Filter.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="premium-card">
        <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Letzte erledigte Angebote</h2>

        <div class="premium-table-wrap">
            <table class="premium-table">
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
                            <td>{{ number_format((float) $offer->total, 2, ',', '.') }} €</td>
                            <td>
                                <a href="{{ route('offers.show', $offer) }}" class="premium-icon-btn" title="Anzeigen">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="premium-muted">Keine erledigten Angebote im aktuellen Filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

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
