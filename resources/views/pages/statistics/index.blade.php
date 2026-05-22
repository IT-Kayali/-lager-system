<x-layouts.premium title="Statistik" subtitle="Auswertung von Umsatz, Angeboten, Kunden, Produkten und Lagerbestand.">
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
            <div class="premium-stat-value">{{ $financialStats['completed_count'] }}</div>
            <div class="premium-stat-label">Erledigte Angebote</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="premium-stat-value">{{ number_format($stockSummary['available_stock'], 3, ',', '.') }}</div>
            <div class="premium-stat-label">Verfügbarer Bestand</div>
        </div>
    </section>

    <section class="premium-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:22px;">
        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Angebote nach Status</h2>

            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Anzahl</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statusLabels as $status => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td><span class="premium-code">{{ $offerStatusCounts[$status] ?? 0 }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Lagerstatus</h2>

            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Anzahl / Menge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>OK</td>
                            <td><span class="premium-badge ok">{{ $stockSummary['ok'] }}</span></td>
                        </tr>
                        <tr>
                            <td>Niedrig</td>
                            <td><span class="premium-badge low">{{ $stockSummary['low'] }}</span></td>
                        </tr>
                        <tr>
                            <td>Kritisch</td>
                            <td><span class="premium-badge critical">{{ $stockSummary['critical'] }}</span></td>
                        </tr>
                        <tr>
                            <td>Gesamtbestand</td>
                            <td>{{ number_format($stockSummary['total_stock'], 3, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Reserviert</td>
                            <td>{{ number_format($stockSummary['reserved_stock'], 3, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Verfügbar</td>
                            <td>{{ number_format($stockSummary['available_stock'], 3, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="premium-card" style="margin-bottom:22px;">
        <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Monatsumsatz</h2>

        @php
            $maxRevenue = max(1, (float) $monthlyRevenue->max('revenue'));
        @endphp

        @if ($monthlyRevenue->isEmpty())
            <div class="premium-placeholder">Noch keine erledigten Angebote für Monatsumsatz vorhanden.</div>
        @else
            <div class="premium-mini-chart">
                @foreach ($monthlyRevenue as $month)
                    @php
                        $percent = min(100, ((float) $month->revenue / $maxRevenue) * 100);
                    @endphp

                    <div class="premium-mini-chart-row">
                        <strong>{{ $month->month }}</strong>
                        <div class="premium-progress">
                            <div class="premium-progress-bar" style="width: {{ $percent }}%;"></div>
                        </div>
                        <span>{{ number_format((float) $month->revenue, 2, ',', '.') }} €</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="premium-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:22px;">
        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Produkte</h2>

            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Verkaufte Menge</th>
                            <th>Umsatz</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topProducts as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->product_name }}</strong>
                                    <div class="premium-muted">{{ $product->product_code }}</div>
                                </td>
                                <td>{{ number_format((float) $product->sold_quantity, 3, ',', '.') }}</td>
                                <td>{{ number_format((float) $product->revenue, 2, ',', '.') }} €</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="premium-muted">Noch keine erledigten Verkäufe vorhanden.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Top-Kunden</h2>

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
                                    <strong>{{ $customer->company_name }}</strong>
                                    <div class="premium-muted">{{ $customer->customer_number }}</div>
                                </td>
                                <td>{{ $customer->offers_count }}</td>
                                <td>{{ number_format((float) $customer->revenue, 2, ',', '.') }} €</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="premium-muted">Noch keine erledigten Kundenumsätze vorhanden.</div>
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
                                <div class="premium-muted">Noch keine erledigten Angebote vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.premium>
