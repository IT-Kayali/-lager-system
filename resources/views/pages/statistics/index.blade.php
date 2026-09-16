@php
    $summary = $dashboard['summary'];
    $sales = $dashboard['sales'];
    $productStats = $dashboard['products'];
    $customerStats = $dashboard['customers'];
    $stock = $dashboard['stock'];
    $charts = $dashboard['charts'];
@endphp

<x-layouts.premium title="Statistik" subtitle="Die wichtigsten Verkaufs-, Kunden-, Produkt- und Lagerkennzahlen auf einen Blick.">
    <style>
        .stats-shell{display:grid;gap:18px}.stats-filter{padding:18px}.stats-filter-grid{display:grid;grid-template-columns:1.1fr 1fr 1fr 1fr auto;gap:12px;align-items:end}.stats-dates{display:grid;grid-template-columns:1fr 1fr;gap:10px;grid-column:1/-1}.stats-actions{display:flex;gap:8px;flex-wrap:wrap}.stats-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.stats-kpi{padding:18px;border:1px solid rgba(127,127,127,.18);border-radius:16px;background:var(--premium-card-bg,#fff)}.stats-kpi span{display:block;color:#777;font-size:12px;font-weight:800}.stats-kpi strong{display:block;margin-top:8px;font-size:26px;font-weight:950}.stats-grid{display:grid;grid-template-columns:1.45fr 1fr;gap:16px}.stats-card{padding:18px}.stats-title{margin:0 0 4px;font-size:18px;font-weight:950}.stats-sub{margin:0 0 16px;color:#777;font-size:12px}.stats-chart{height:290px;position:relative}.stats-mini-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.stats-mini{padding:13px;border:1px solid rgba(127,127,127,.16);border-radius:12px}.stats-mini span{display:block;color:#777;font-size:11px;font-weight:800}.stats-mini strong{display:block;margin-top:5px;font-size:19px}.stats-table{width:100%;border-collapse:collapse}.stats-table th,.stats-table td{text-align:left;padding:10px 8px;border-bottom:1px solid rgba(127,127,127,.15);font-size:12px}.stats-table th{color:#777;font-weight:900}.stats-table tr:last-child td{border-bottom:0}.stats-status-list{display:grid;gap:9px}.stats-status-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:1px solid rgba(127,127,127,.15);border-radius:11px}.stats-empty{padding:24px 8px;text-align:center;color:#777;font-size:13px}.stats-note{padding:12px 14px;border-radius:12px;background:rgba(227,202,110,.1);border:1px solid rgba(227,202,110,.3);font-size:12px;color:#666}.stats-link{color:inherit;text-decoration:none;font-weight:900}.stats-link:hover{text-decoration:underline}@media(max-width:1150px){.stats-filter-grid{grid-template-columns:repeat(2,1fr)}.stats-kpis{grid-template-columns:repeat(2,1fr)}.stats-grid{grid-template-columns:1fr}}@media(max-width:650px){.stats-filter-grid,.stats-kpis,.stats-mini-grid{grid-template-columns:1fr}.stats-dates{grid-template-columns:1fr}.stats-table{min-width:620px}.stats-scroll{overflow-x:auto}}
    </style>

    <div class="stats-shell" data-statistics-runtime>
        <section class="premium-card stats-filter">
            <form method="GET" action="{{ route('statistics.index') }}">
                <div class="stats-filter-grid">
                    <div class="premium-form-field">
                        <label>Zeitraum</label>
                        <select name="period" id="statistics-period" class="premium-select">
                            @foreach($periods as $value => $label)
                                <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="premium-form-field">
                        <label>Kunde</label>
                        <select name="customer_id" class="premium-select">
                            <option value="">Alle Kunden</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string)$filters['customer_id']===(string)$customer->id)>{{ $customer->customer_number }} — {{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="premium-form-field">
                        <label>Produkt</label>
                        <select name="product_id" class="premium-select">
                            <option value="">Alle Produkte</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected((string)$filters['product_id']===(string)$product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="premium-form-field">
                        <label>Angebotsstatus</label>
                        <select name="status" class="premium-select">
                            <option value="">Alle Status</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status']===$value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stats-actions">
                        <button class="premium-btn gold" type="submit"><i class="bi bi-funnel"></i> Anwenden</button>
                        <a class="premium-btn" href="{{ route('statistics.index') }}"><i class="bi bi-arrow-counterclockwise"></i> Zurücksetzen</a>
                    </div>
                    <div class="stats-dates" id="statistics-custom-dates" @style(['display:none'=>$filters['period']!=='custom'])>
                        <div class="premium-form-field"><label>Von</label><input class="premium-input" type="date" name="date_from" value="{{ $filters['date_from'] }}"></div>
                        <div class="premium-form-field"><label>Bis</label><input class="premium-input" type="date" name="date_to" value="{{ $filters['date_to'] }}"></div>
                    </div>
                </div>
            </form>
        </section>

        <div class="stats-note"><strong>Verkauf</strong> bedeutet hier ein tatsächlich abgeschlossenes Angebot. Offene, gesendete oder stornierte Angebote erhöhen den Umsatz nicht. Der Angebotsstatus-Filter bleibt trotzdem für die gezielte Auswertung verfügbar.</div>

        <section class="stats-kpis">
            <div class="stats-kpi"><span>Umsatz aus Verkäufen</span><strong>{{ number_format((float)$summary['completed_revenue'],2,',','.') }} €</strong></div>
            <div class="stats-kpi"><span>Abgeschlossene Verkäufe</span><strong>{{ number_format((int)$summary['completed_orders'],0,',','.') }}</strong></div>
            <div class="stats-kpi"><span>Ø Auftragswert</span><strong>{{ number_format((float)$summary['average_order_value'],2,',','.') }} €</strong></div>
            <div class="stats-kpi"><span>Angebote gesamt</span><strong>{{ number_format((int)$summary['offers_count'],0,',','.') }}</strong></div>
        </section>

        <section class="stats-grid">
            <div class="premium-card stats-card">
                <h3 class="stats-title">Umsatzentwicklung</h3><p class="stats-sub">Abgeschlossene Verkäufe im gewählten Zeitraum</p>
                <div class="stats-chart">
                    <canvas
                        id="salesTrendChart"
                        data-sales-trend-chart
                        data-sales-trend="{{ json_encode($charts['salesTrend'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                    ></canvas>
                </div>
            </div>
            <div class="premium-card stats-card">
                <h3 class="stats-title">Angebotsstatus</h3><p class="stats-sub">Verteilung der Angebote im aktuellen Filter</p>
                <div class="stats-status-list">
                    @foreach($summary['status_counts'] as $status => $count)
                        <div class="stats-status-row"><span>{{ $statusLabels[$status] ?? $status }}</span><strong>{{ $count }}</strong></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="premium-card stats-card">
                <h3 class="stats-title">Top 10 Produkte</h3><p class="stats-sub">Produkte mit dem höchsten Verkaufsumsatz – inklusive verkaufter Menge</p>
                <div class="stats-scroll"><table class="stats-table"><thead><tr><th>Produkt</th><th>Menge</th><th>Verkäufe</th><th>Umsatz</th></tr></thead><tbody>
                @forelse($productStats['top'] ?? [] as $row)
                    <tr><td>@if($row->product_id)<a class="stats-link" href="{{ route('products.show',$row->product_id) }}">{{ $row->product_name }}</a>@else{{ $row->product_name }}@endif</td><td>{{ number_format((float)$row->sold_quantity,2,',','.') }} {{ $row->unit }}</td><td>{{ $row->orders_count }}</td><td>{{ number_format((float)$row->revenue,2,',','.') }} €</td></tr>
                @empty<tr><td colspan="4" class="stats-empty">Noch keine Verkäufe vorhanden.</td></tr>@endforelse
                </tbody></table></div>
            </div>
            <div class="premium-card stats-card">
                <h3 class="stats-title">Produkte ohne Verkauf</h3><p class="stats-sub">Hilft dabei, schwach laufende oder neue Artikel schnell zu erkennen</p>
                <div class="stats-scroll"><table class="stats-table"><thead><tr><th>Produkt</th><th>Code</th></tr></thead><tbody>
                @forelse($productStats['products_without_sales'] ?? [] as $row)
                    <tr><td><a class="stats-link" href="{{ route('products.show',$row->id) }}">{{ $row->name }}</a></td><td>{{ $row->product_code }}</td></tr>
                @empty<tr><td colspan="2" class="stats-empty">Keine Produkte ohne Verkauf.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="premium-card stats-card">
                <h3 class="stats-title">Top 10 Kunden</h3><p class="stats-sub">Kunden nach Verkaufsumsatz</p>
                <div class="stats-scroll"><table class="stats-table"><thead><tr><th>Kunde</th><th>Verkäufe</th><th>Umsatz</th></tr></thead><tbody>
                @forelse($customerStats['top'] ?? [] as $row)
                    <tr><td>{{ $row['company_name'] ?? 'Unbekannt' }}</td><td>{{ $row['orders_count'] ?? 0 }}</td><td>{{ number_format((float)($row['revenue'] ?? 0),2,',','.') }} €</td></tr>
                @empty<tr><td colspan="3" class="stats-empty">Noch keine Kundendaten vorhanden.</td></tr>@endforelse
                </tbody></table></div>
            </div>
            <div class="premium-card stats-card">
                <h3 class="stats-title">Lagerübersicht</h3><p class="stats-sub">Nur die wichtigsten aktuellen Bestandswerte</p>
                <div class="stats-mini-grid">
                    <div class="stats-mini"><span>Gesamtbestand</span><strong>{{ number_format((float)($stock['total_stock'] ?? 0),2,',','.') }}</strong></div>
                    <div class="stats-mini"><span>Reserviert</span><strong>{{ number_format((float)($stock['reserved_stock'] ?? 0),2,',','.') }}</strong></div>
                    <div class="stats-mini"><span>Verfügbar</span><strong>{{ number_format((float)($stock['available_stock'] ?? 0),2,',','.') }}</strong></div>
                    <div class="stats-mini"><span>Niedrig / kritisch</span><strong>{{ (int)($stock['low_count'] ?? 0)+(int)($stock['critical_count'] ?? 0) }}</strong></div>
                </div>
            </div>
        </section>
    </div>

    <script src="{{ asset('js/statistics-runtime.js') }}" defer></script>
</x-layouts.premium>
