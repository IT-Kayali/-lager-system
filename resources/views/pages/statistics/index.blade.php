@php
    $context = $dashboard['context'];
    $sections = $context['sections'];
    $summary = $dashboard['summary'];
    $advancedKeys = ['customer_group_id', 'category_id', 'supplier_id', 'user_id', 'shipping_method', 'branch', 'branch_status', 'movement_type'];
    $advancedActive = collect($advancedKeys)->contains(fn ($key) => filled($filters[$key] ?? null));
@endphp

<x-layouts.premium title="Statistik" subtitle="ERP-Auswertung mit dynamischen Filtern für Verkauf, Kunden, Produkte, Lager und Filialen.">
    <style>
        .stats-shell { display:grid; gap:20px; }
        .stats-hero { padding:24px; border:1px solid rgba(227,202,110,.35); border-radius:18px; background:linear-gradient(135deg,rgba(227,202,110,.14),rgba(255,255,255,.02)); }
        .stats-hero-top { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; flex-wrap:wrap; }
        .stats-hero h2 { margin:0; font-size:28px; font-weight:900; line-height:1.15; }
        .stats-hero p { margin:8px 0 0; max-width:820px; color:#777; }
        .stats-period-pill { display:inline-flex; align-items:center; gap:8px; padding:9px 13px; border-radius:999px; background:#171717; color:#fff; font-weight:800; font-size:13px; white-space:nowrap; }
        .stats-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }
        .stats-chip { display:inline-flex; align-items:center; gap:7px; padding:7px 10px; border-radius:999px; border:1px solid rgba(227,202,110,.55); background:rgba(227,202,110,.12); font-size:12px; font-weight:800; }
        .stats-chip a { color:inherit; text-decoration:none; opacity:.75; }
        .stats-filter-card { padding:20px; }
        .stats-filter-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; align-items:end; }
        .stats-filter-actions { display:flex; gap:9px; flex-wrap:wrap; align-items:center; }
        .stats-date-fields { display:grid; grid-template-columns:1fr 1fr; gap:10px; grid-column:span 2; }
        .stats-more { margin-top:14px; border-top:1px solid rgba(127,127,127,.2); padding-top:14px; }
        .stats-more summary { cursor:pointer; font-weight:900; display:flex; align-items:center; gap:8px; user-select:none; }
        .stats-more-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-top:14px; }
        .stats-kpi-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; }
        .stats-kpi { position:relative; min-height:126px; padding:17px; border-radius:16px; border:1px solid rgba(127,127,127,.18); background:var(--premium-card-bg,#fff); overflow:hidden; }
        .stats-kpi-icon { width:38px; height:38px; border-radius:12px; display:grid; place-items:center; background:rgba(227,202,110,.2); font-size:18px; }
        .stats-kpi-value { margin-top:14px; font-size:24px; font-weight:950; line-height:1.05; word-break:break-word; }
        .stats-kpi-label { margin-top:6px; color:#777; font-size:12px; font-weight:800; }
        .stats-kpi-note { margin-top:6px; font-size:11px; color:#888; }
        .stats-change { display:inline-flex; align-items:center; gap:4px; margin-top:7px; font-size:11px; font-weight:900; }
        .stats-section { display:grid; gap:14px; }
        .stats-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .stats-section-head h3 { margin:0; font-size:21px; font-weight:950; }
        .stats-section-head p { margin:4px 0 0; color:#777; font-size:13px; }
        .stats-two { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .stats-three { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
        .stats-four { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
        .stats-mini { padding:15px 16px; border:1px solid rgba(127,127,127,.18); border-radius:14px; background:var(--premium-card-bg,#fff); }
        .stats-mini strong { display:block; font-size:20px; font-weight:950; margin-top:4px; }
        .stats-mini span { color:#777; font-size:12px; font-weight:800; }
        .stats-chart-card { min-height:360px; }
        .stats-chart-box { height:300px; position:relative; }
        .stats-table-title { margin:0 0 12px; font-size:17px; font-weight:950; }
        .stats-status { display:inline-flex; align-items:center; padding:4px 8px; border-radius:999px; background:rgba(127,127,127,.12); font-size:11px; font-weight:900; }
        .stats-status.critical { background:rgba(239,68,68,.12); color:#b91c1c; }
        .stats-status.low { background:rgba(245,158,11,.14); color:#b45309; }
        .stats-status.ok { background:rgba(34,197,94,.12); color:#15803d; }
        .stats-empty { padding:28px 16px; text-align:center; color:#777; }
        .stats-context-note { padding:12px 14px; border-radius:12px; background:rgba(227,202,110,.1); border:1px solid rgba(227,202,110,.3); font-size:12px; color:#666; }
        @media (max-width:1350px) { .stats-kpi-grid { grid-template-columns:repeat(3,1fr); } .stats-filter-grid,.stats-more-grid { grid-template-columns:repeat(3,1fr); } }
        @media (max-width:980px) { .stats-two,.stats-three,.stats-four { grid-template-columns:1fr; } .stats-filter-grid,.stats-more-grid { grid-template-columns:repeat(2,1fr); } .stats-kpi-grid { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:640px) { .stats-filter-grid,.stats-more-grid,.stats-kpi-grid { grid-template-columns:1fr; } .stats-date-fields { grid-column:span 1; grid-template-columns:1fr; } .stats-hero h2 { font-size:23px; } }
    </style>

    <div class="stats-shell">
        <section class="stats-hero">
            <div class="stats-hero-top">
                <div>
                    <h2>{{ $context['title'] }}</h2>
                    <p>{{ $context['subtitle'] }}</p>
                </div>
                <div class="stats-period-pill">
                    <i class="bi bi-calendar3"></i>
                    {{ $context['period_label'] }}
                </div>
            </div>

            @if ($context['chips'])
                <div class="stats-chips">
                    @foreach ($context['chips'] as $chip)
                        @php
                            $removeQuery = request()->query();
                            unset($removeQuery[$chip['key']]);
                            if ($chip['key'] === 'period') {
                                unset($removeQuery['date_from'], $removeQuery['date_to']);
                            }
                        @endphp
                        <span class="stats-chip">
                            {{ $chip['label'] }}
                            <a href="{{ route('statistics.index', $removeQuery) }}" aria-label="Filter entfernen"><i class="bi bi-x-lg"></i></a>
                        </span>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="premium-card stats-filter-card">
            <form method="GET" action="{{ route('statistics.index') }}" id="statistics-filter-form">
                <div class="stats-filter-grid">
                    <div class="premium-form-field">
                        <label>Zeitraum</label>
                        <select name="period" id="statistics-period" class="premium-select">
                            @foreach ($periods as $value => $label)
                                <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="stats-date-fields" id="statistics-custom-dates" @style(['display:none' => $filters['period'] !== 'custom'])>
                        <div class="premium-form-field">
                            <label>Von</label>
                            <input name="date_from" type="date" class="premium-input" value="{{ $filters['date_from'] }}">
                        </div>
                        <div class="premium-form-field">
                            <label>Bis</label>
                            <input name="date_to" type="date" class="premium-input" value="{{ $filters['date_to'] }}">
                        </div>
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
                                <option value="{{ $product->id }}" @selected((string) $filters['product_id'] === (string) $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="stats-filter-actions">
                        <button class="premium-btn gold" type="submit"><i class="bi bi-funnel"></i> Anwenden</button>
                        <a href="{{ route('statistics.index') }}" class="premium-btn"><i class="bi bi-arrow-counterclockwise"></i> Zurücksetzen</a>
                    </div>
                </div>

                <details class="stats-more" @if($advancedActive) open @endif>
                    <summary><i class="bi bi-sliders"></i> Weitere ERP-Filter</summary>
                    <div class="stats-more-grid">
                        <div class="premium-form-field">
                            <label>Kundengruppe</label>
                            <select name="customer_group_id" class="premium-select">
                                <option value="">Alle Gruppen</option>
                                @foreach ($customerGroups as $group)
                                    <option value="{{ $group->id }}" @selected((string) $filters['customer_group_id'] === (string) $group->id)>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Kategorie</label>
                            <select name="category_id" class="premium-select">
                                <option value="">Alle Kategorien</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Lieferant</label>
                            <select name="supplier_id" class="premium-select">
                                <option value="">Alle Lieferanten</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((string) $filters['supplier_id'] === (string) $supplier->id)>{{ $supplier->company_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Mitarbeiter</label>
                            <select name="user_id" class="premium-select">
                                <option value="">Alle Mitarbeiter</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Versandart</label>
                            <select name="shipping_method" class="premium-select">
                                <option value="">Alle Versandarten</option>
                                @foreach ($shippingMethods as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['shipping_method'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Filiale</label>
                            <select name="branch" class="premium-select">
                                <option value="">Alle Filialen</option>
                                @foreach ($branches as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['branch'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Filialstatus</label>
                            <select name="branch_status" class="premium-select">
                                <option value="">Alle Filialstatus</option>
                                @foreach ($branchStatusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['branch_status'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-form-field">
                            <label>Lagerbewegung</label>
                            <select name="movement_type" class="premium-select">
                                <option value="">Alle Bewegungen</option>
                                @foreach ($movementTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['movement_type'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </details>
            </form>
        </section>

        @if ($context['has_context_filters'])
            <div class="stats-context-note">
                <strong>Kontextmodus:</strong> Unpassende Statistikbereiche werden ausgeblendet. Zeitraumfilter bleiben global; aktuelle Bestände zeigen immer den heutigen Lagerstand, während Lagerbewegungen auf den Zeitraum reagieren.
            </div>
        @endif

        <section class="stats-kpi-grid">
            <div class="stats-kpi">
                <div class="stats-kpi-icon"><i class="bi bi-currency-euro"></i></div>
                <div class="stats-kpi-value">{{ number_format($summary['completed_revenue'], 2, ',', '.') }} €</div>
                <div class="stats-kpi-label">Erledigter Umsatz</div>
                @if (! is_null($summary['revenue_change_percent']))
                    <div class="stats-change">
                        <i class="bi {{ $summary['revenue_change_percent'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ number_format(abs($summary['revenue_change_percent']), 1, ',', '.') }} % zum vorherigen Zeitraum
                    </div>
                @endif
            </div>
            <div class="stats-kpi">
                <div class="stats-kpi-icon"><i class="bi bi-check2-circle"></i></div>
                <div class="stats-kpi-value">{{ $summary['completed_orders'] }}</div>
                <div class="stats-kpi-label">Abgeschlossene Verkäufe</div>
            </div>
            <div class="stats-kpi">
                <div class="stats-kpi-icon"><i class="bi bi-receipt"></i></div>
                <div class="stats-kpi-value">{{ number_format($summary['average_order_value'], 2, ',', '.') }} €</div>
                <div class="stats-kpi-label">Ø Auftragswert</div>
            </div>
            <div class="stats-kpi">
                <div class="stats-kpi-icon"><i class="bi bi-files"></i></div>
                <div class="stats-kpi-value">{{ $summary['offers_count'] }}</div>
                <div class="stats-kpi-label">Vorgänge im Filter</div>
                <div class="stats-kpi-note">{{ number_format($summary['offers_value'], 2, ',', '.') }} € Vorgangswert</div>
            </div>
            <div class="stats-kpi">
                <div class="stats-kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stats-kpi-value">{{ number_format($summary['reserved_value'], 2, ',', '.') }} €</div>
                <div class="stats-kpi-label">Aktiv reservierter Wert</div>
            </div>
            <div class="stats-kpi">
                <div class="stats-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stats-kpi-value">{{ number_format($summary['conversion_rate'], 1, ',', '.') }} %</div>
                <div class="stats-kpi-label">Abschlussquote</div>
                <div class="stats-kpi-note">Stornoquote {{ number_format($summary['cancel_rate'], 1, ',', '.') }} %</div>
            </div>
        </section>

        @if ($sections['sales'] && $dashboard['sales'])
            @php $sales = $dashboard['sales']; @endphp
            <section class="stats-section">
                <div class="stats-section-head">
                    <div><h3>Verkauf & Umsatz</h3><p>Erledigte Verkäufe werden nach Abschlussdatum ausgewertet.</p></div>
                </div>
                <div class="stats-four">
                    <div class="stats-mini"><span>Lieferungen</span><strong>{{ $sales['delivery_count'] }}</strong></div>
                    <div class="stats-mini"><span>Abholungen</span><strong>{{ $sales['pickup_count'] }}</strong></div>
                    <div class="stats-mini"><span>Kartons bei Lieferungen</span><strong>{{ $sales['cartons'] }}</strong></div>
                    <div class="stats-mini"><span>Ø Bearbeitungszeit</span><strong>{{ number_format($sales['average_processing_hours'], 1, ',', '.') }} Std.</strong></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Umsatzentwicklung</h4><div class="stats-chart-box"><canvas id="salesTrendChart"></canvas></div></div>
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Versandarten</h4><div class="stats-chart-box"><canvas id="shippingChart"></canvas></div></div>
                </div>
                <div class="premium-card">
                    <h4 class="stats-table-title">Letzte erledigte Verkäufe</h4>
                    <div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Vorgang</th><th>Kunde</th><th>Mitarbeiter</th><th>Erledigt</th><th>Gesamt</th><th></th></tr></thead><tbody>
                        @forelse ($sales['recent'] as $offer)
                            <tr><td><span class="premium-code">{{ $offer->offer_number }}</span></td><td>{{ $offer->customer?->company_name ?? '—' }}</td><td>{{ $offer->user?->name ?? '—' }}</td><td>{{ ($offer->completed_at ?: $offer->created_at)->format('d.m.Y H:i') }}</td><td>{{ number_format((float) $offer->total, 2, ',', '.') }} €</td><td><a href="{{ route('offers.show', $offer) }}" class="premium-icon-btn"><i class="bi bi-eye"></i></a></td></tr>
                        @empty
                            <tr><td colspan="6"><div class="stats-empty">Keine erledigten Verkäufe im aktuellen Filter.</div></td></tr>
                        @endforelse
                    </tbody></table></div>
                </div>
            </section>
        @endif

        @if ($sections['products'] && $dashboard['products'])
            @php $productStats = $dashboard['products']; @endphp
            <section class="stats-section">
                <div class="stats-section-head"><div><h3>Produkte & Absatz</h3><p>Welche Produkte Umsatz und Menge im gewählten Zeitraum erzeugen.</p></div></div>
                <div class="stats-three">
                    <div class="stats-mini"><span>Produkte mit Verkauf</span><strong>{{ $productStats['sold_products_count'] }}</strong></div>
                    <div class="stats-mini"><span>Produktumsatz</span><strong>{{ number_format($productStats['total_revenue'], 2, ',', '.') }} €</strong></div>
                    <div class="stats-mini"><span>Verkaufte Menge gesamt</span><strong>{{ number_format($productStats['total_quantity'], 3, ',', '.') }}</strong></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Top-Produkte nach Umsatz</h4><div class="stats-chart-box"><canvas id="topProductsChart"></canvas></div></div>
                    <div class="premium-card"><h4 class="stats-table-title">Produkte ohne Verkauf</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Einheit</th><th>Mindestbestand</th></tr></thead><tbody>
                        @forelse ($productStats['products_without_sales'] as $product)
                            <tr><td><strong>{{ $product->name }}</strong><div class="premium-muted">{{ $product->product_code }}</div></td><td>{{ $product->unit ?: '—' }}</td><td>{{ number_format((float) $product->minimum_stock, 3, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="3"><div class="stats-empty">Keine passenden Produkte ohne Verkauf.</div></td></tr>
                        @endforelse
                    </tbody></table></div></div>
                </div>
                <div class="premium-card"><h4 class="stats-table-title">Top-Produkte</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Menge</th><th>Einheit</th><th>Verkäufe</th><th>Umsatz</th></tr></thead><tbody>
                    @forelse ($productStats['top'] as $row)
                        <tr><td><strong>{{ $row->product_name }}</strong><div class="premium-muted">{{ $row->product_code }}</div></td><td>{{ number_format((float) $row->sold_quantity, 3, ',', '.') }}</td><td>{{ $row->unit ?: '—' }}</td><td>{{ $row->orders_count }}</td><td>{{ number_format((float) $row->revenue, 2, ',', '.') }} €</td></tr>
                    @empty
                        <tr><td colspan="5"><div class="stats-empty">Keine Produktverkäufe im aktuellen Filter.</div></td></tr>
                    @endforelse
                </tbody></table></div></div>
            </section>
        @endif

        @if ($sections['customers'] && $dashboard['customers'])
            @php $customerStats = $dashboard['customers']; @endphp
            <section class="stats-section">
                <div class="stats-section-head"><div><h3>Kunden</h3><p>Aktive, neue und wiederkehrende Kunden sowie die umsatzstärksten Beziehungen.</p></div></div>
                <div class="stats-three">
                    <div class="stats-mini"><span>Aktive Kunden</span><strong>{{ $customerStats['active_customers'] }}</strong></div>
                    <div class="stats-mini"><span>Neue Kunden im Zeitraum</span><strong>{{ $customerStats['new_customers'] }}</strong></div>
                    <div class="stats-mini"><span>Wiederkehrende Kunden</span><strong>{{ $customerStats['repeat_customers'] }}</strong></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Top-Kunden nach Umsatz</h4><div class="stats-chart-box"><canvas id="topCustomersChart"></canvas></div></div>
                    <div class="premium-card"><h4 class="stats-table-title">Top-Kunden</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Kunde</th><th>Verkäufe</th><th>Umsatz</th></tr></thead><tbody>
                        @forelse ($customerStats['top'] as $row)
                            <tr><td><strong>{{ $row['company_name'] }}</strong><div class="premium-muted">{{ $row['customer_number'] }}</div></td><td>{{ $row['orders_count'] }}</td><td>{{ number_format((float) $row['revenue'], 2, ',', '.') }} €</td></tr>
                        @empty
                            <tr><td colspan="3"><div class="stats-empty">Keine Kundendaten im aktuellen Filter.</div></td></tr>
                        @endforelse
                    </tbody></table></div></div>
                </div>
            </section>
        @endif

        @if ($sections['stock'] && $dashboard['stock'])
            @php $stock = $dashboard['stock']; @endphp
            <section class="stats-section">
                <div class="stats-section-head"><div><h3>Lager & Chargen</h3><p>Der aktuelle Bestand ist eine Momentaufnahme; Bewegungen und Chargenereignisse reagieren auf den Zeitraum.</p></div></div>
                <div class="stats-four">
                    <div class="stats-mini"><span>Gesamtbestand</span><strong>{{ number_format($stock['total_stock'], 3, ',', '.') }}</strong></div>
                    <div class="stats-mini"><span>Verfügbar</span><strong>{{ number_format($stock['available_stock'], 3, ',', '.') }}</strong></div>
                    <div class="stats-mini"><span>Reserviert</span><strong>{{ number_format($stock['reserved_stock'], 3, ',', '.') }}</strong></div>
                    <div class="stats-mini"><span>Kritische Produkte</span><strong>{{ $stock['critical_count'] }}</strong></div>
                    <div class="stats-mini"><span>Wareneingang</span><strong>{{ number_format($stock['movement_in'], 3, ',', '.') }}</strong></div>
                    <div class="stats-mini"><span>Warenausgang</span><strong>{{ number_format($stock['movement_out'], 3, ',', '.') }}</strong></div>
                    <div class="stats-mini"><span>Abgelaufene Chargen</span><strong>{{ $stock['expired_batches']->count() }}</strong></div>
                    <div class="stats-mini"><span>Ablauf in 30 Tagen</span><strong>{{ $stock['expiring_batches']->count() }}</strong></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Lagerstatus</h4><div class="stats-chart-box"><canvas id="stockStatusChart"></canvas></div></div>
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Lagerbewegungen</h4><div class="stats-chart-box"><canvas id="stockMovementsChart"></canvas></div></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card"><h4 class="stats-table-title">Niedrige / kritische Bestände</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Verfügbar</th><th>Minimum</th><th>Status</th></tr></thead><tbody>
                        @forelse ($stock['critical_products'] as $row)
                            <tr><td><strong>{{ $row['name'] }}</strong><div class="premium-muted">{{ $row['product_code'] }}</div></td><td>{{ number_format($row['available'], 3, ',', '.') }} {{ $row['unit'] }}</td><td>{{ number_format($row['minimum'], 3, ',', '.') }}</td><td><span class="stats-status {{ $row['status'] }}">{{ $row['status'] === 'critical' ? 'Kritisch' : 'Niedrig' }}</span></td></tr>
                        @empty
                            <tr><td colspan="4"><div class="stats-empty">Keine kritischen Bestände.</div></td></tr>
                        @endforelse
                    </tbody></table></div></div>
                    <div class="premium-card"><h4 class="stats-table-title">Bald ablaufende Chargen</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Charge</th><th>Menge</th><th>Ablauf</th></tr></thead><tbody>
                        @forelse ($stock['expiring_batches']->take(10) as $batch)
                            <tr><td>{{ $batch->product?->name ?? '—' }}</td><td><span class="premium-code">{{ $batch->batch_number }}</span></td><td>{{ number_format((float) $batch->quantity, 3, ',', '.') }}</td><td>{{ $batch->expires_at?->format('d.m.Y') }}</td></tr>
                        @empty
                            <tr><td colspan="4"><div class="stats-empty">Keine Chargen mit Ablauf in den nächsten 30 Tagen.</div></td></tr>
                        @endforelse
                    </tbody></table></div></div>
                </div>
                <div class="premium-card"><h4 class="stats-table-title">Letzte Lagerbewegungen</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Zeit</th><th>Produkt</th><th>Typ</th><th>Menge</th><th>Mitarbeiter</th></tr></thead><tbody>
                    @forelse ($stock['recent_movements'] as $movement)
                        <tr><td>{{ $movement->created_at->format('d.m.Y H:i') }}</td><td>{{ $movement->product?->name ?? '—' }}</td><td>{{ $movementTypes[$movement->type] ?? $movement->type }}</td><td>{{ number_format((float) $movement->quantity, 3, ',', '.') }}</td><td>{{ $movement->user?->name ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5"><div class="stats-empty">Keine Lagerbewegungen im Zeitraum.</div></td></tr>
                    @endforelse
                </tbody></table></div></div>
            </section>
        @endif

        @if ($sections['branches'] && $dashboard['branches'])
            @php $branchStats = $dashboard['branches']; @endphp
            <section class="stats-section">
                <div class="stats-section-head"><div><h3>Filialausgänge</h3><p>Mengen, Status und Top-Produkte der Filialversorgung.</p></div></div>
                <div class="stats-four">
                    <div class="stats-mini"><span>Ausgegeben</span><strong>{{ $branchStats['issued_count'] }}</strong></div>
                    <div class="stats-mini"><span>Offen</span><strong>{{ $branchStats['open_count'] }}</strong></div>
                    <div class="stats-mini"><span>In Bearbeitung</span><strong>{{ $branchStats['in_progress_count'] }}</strong></div>
                    <div class="stats-mini"><span>Ausgegebene Menge</span><strong>{{ number_format($branchStats['issued_quantity'], 3, ',', '.') }}</strong></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Menge je Filiale</h4><div class="stats-chart-box"><canvas id="branchesChart"></canvas></div></div>
                    <div class="premium-card"><h4 class="stats-table-title">Top-Produkte je Filialausgang</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Menge</th><th>Einheit</th></tr></thead><tbody>
                        @forelse ($branchStats['top_products'] as $row)
                            <tr><td>{{ $row['product_name'] }}</td><td>{{ number_format($row['quantity'], 3, ',', '.') }}</td><td>{{ $row['unit'] ?: '—' }}</td></tr>
                        @empty
                            <tr><td colspan="3"><div class="stats-empty">Keine ausgegebenen Filialpositionen.</div></td></tr>
                        @endforelse
                    </tbody></table></div></div>
                </div>
                <div class="premium-card"><h4 class="stats-table-title">Letzte Filialvorgänge</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Nummer</th><th>Filiale</th><th>Status</th><th>Positionen</th><th>Erstellt von</th><th>Bearbeitet von</th></tr></thead><tbody>
                    @forelse ($branchStats['recent'] as $withdrawal)
                        <tr><td><span class="premium-code">{{ $withdrawal->withdrawal_number }}</span></td><td>{{ $withdrawal->branch_name }}</td><td>{{ $branchStatusLabels[$withdrawal->status] ?? $withdrawal->status }}</td><td>{{ $withdrawal->items->count() }}</td><td>{{ $withdrawal->user?->name ?? '—' }}</td><td>{{ $withdrawal->processor?->name ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="6"><div class="stats-empty">Keine Filialvorgänge im aktuellen Filter.</div></td></tr>
                    @endforelse
                </tbody></table></div></div>
            </section>
        @endif

        @if ($sections['wallet'] && $dashboard['wallet'])
            @php $wallet = $dashboard['wallet']; @endphp
            <section class="stats-section">
                <div class="stats-section-head"><div><h3>Kundenkonto</h3><p>Gutschriften, Belastungen und Kontobewegungen im gewählten Zeitraum.</p></div></div>
                <div class="stats-four">
                    <div class="stats-mini"><span>Transaktionen</span><strong>{{ $wallet['transactions_count'] }}</strong></div>
                    <div class="stats-mini"><span>Gutschriften</span><strong>{{ number_format($wallet['credits'], 2, ',', '.') }} €</strong></div>
                    <div class="stats-mini"><span>Belastungen</span><strong>{{ number_format($wallet['debits'], 2, ',', '.') }} €</strong></div>
                    <div class="stats-mini"><span>Netto-Bewegung</span><strong>{{ number_format($wallet['net'], 2, ',', '.') }} €</strong></div>
                    @if (! is_null($wallet['selected_customer_balance']))
                        <div class="stats-mini"><span>Aktueller Kundensaldo</span><strong>{{ number_format($wallet['selected_customer_balance'], 2, ',', '.') }} €</strong></div>
                    @endif
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Gutschriften / Belastungen</h4><div class="stats-chart-box"><canvas id="walletChart"></canvas></div></div>
                    <div class="premium-card"><h4 class="stats-table-title">Stärkste Kontobewegungen nach Kunde</h4><div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Kunde</th><th>Transaktionen</th><th>Netto</th></tr></thead><tbody>
                        @forelse ($wallet['top_customers'] as $row)
                            <tr><td><strong>{{ $row['company_name'] }}</strong><div class="premium-muted">{{ $row['customer_number'] }}</div></td><td>{{ $row['transactions'] }}</td><td>{{ number_format($row['net'], 2, ',', '.') }} €</td></tr>
                        @empty
                            <tr><td colspan="3"><div class="stats-empty">Keine Kundenkonto-Bewegungen.</div></td></tr>
                        @endforelse
                    </tbody></table></div></div>
                </div>
            </section>
        @endif

        @if ($sections['operations'] && $dashboard['operations'])
            @php $operations = $dashboard['operations']; @endphp
            <section class="stats-section">
                <div class="stats-section-head"><div><h3>Prozess & Mitarbeiter</h3><p>Bearbeitungsdauer, auslaufende Reservierungen und Verkaufsleistung nach Mitarbeiter.</p></div></div>
                <div class="stats-three">
                    <div class="stats-mini"><span>Reservierungen &lt; 24 Std.</span><strong>{{ $operations['expiring_soon'] }}</strong></div>
                    <div class="stats-mini"><span>Ø Zeit bis Erledigung</span><strong>{{ number_format($operations['average_processing_hours'], 1, ',', '.') }} Std.</strong></div>
                    <div class="stats-mini"><span>Erfasste Mitarbeiter</span><strong>{{ $operations['employee_rows']->count() }}</strong></div>
                </div>
                <div class="stats-two">
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Umsatz nach Mitarbeiter</h4><div class="stats-chart-box"><canvas id="employeesChart"></canvas></div></div>
                    <div class="premium-card stats-chart-card"><h4 class="stats-table-title">Vorgänge nach Status</h4><div class="stats-chart-box"><canvas id="offerStatusChart"></canvas></div></div>
                </div>
            </section>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const period = document.getElementById('statistics-period');
            const customDates = document.getElementById('statistics-custom-dates');
            const charts = @json($dashboard['charts']);
            const palette = ['#e3ca6e','#212121','#f59e0b','#22c55e','#ef4444','#64748b','#8b5cf6','#06b6d4','#84cc16','#f97316'];

            function syncCustomDates() {
                if (customDates && period) customDates.style.display = period.value === 'custom' ? 'grid' : 'none';
            }
            if (period) period.addEventListener('change', syncCustomDates);
            syncCustomDates();

            function simpleChart(id, type, labels, data, label) {
                const canvas = document.getElementById(id);
                if (!canvas || !window.Chart) return;
                new Chart(canvas, {
                    type,
                    data: { labels, datasets: [{ label, data, backgroundColor: type === 'line' ? 'rgba(227,202,110,.18)' : palette, borderColor: type === 'line' ? '#e3ca6e' : palette, borderWidth:2, tension:.3, fill:type === 'line' }] },
                    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:type === 'doughnut' ? 'bottom' : 'top' } }, scales:type === 'bar' || type === 'line' ? { y:{ beginAtZero:true } } : {} }
                });
            }

            function salesTrendChart() {
                const canvas = document.getElementById('salesTrendChart');
                if (!canvas || !window.Chart) return;
                new Chart(canvas, {
                    type:'line',
                    data:{ labels:charts.salesTrend.labels, datasets:[
                        { label:'Umsatz €', data:charts.salesTrend.data, borderColor:'#e3ca6e', backgroundColor:'rgba(227,202,110,.18)', borderWidth:2, tension:.3, fill:true, yAxisID:'y' },
                        { label:'Verkäufe', data:charts.salesTrend.orders, borderColor:'#212121', backgroundColor:'rgba(33,33,33,.08)', borderWidth:2, tension:.3, fill:false, yAxisID:'y1' }
                    ]},
                    options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, scales:{ y:{beginAtZero:true,position:'left'}, y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false}} } }
                });
            }

            function stockMovementChart() {
                const canvas = document.getElementById('stockMovementsChart');
                if (!canvas || !window.Chart) return;
                new Chart(canvas, {
                    type:'line',
                    data:{ labels:charts.stockMovements.labels, datasets:[
                        {label:'Eingang',data:charts.stockMovements.in,borderWidth:2,tension:.25},
                        {label:'Ausgang',data:charts.stockMovements.out,borderWidth:2,tension:.25},
                        {label:'Korrektur',data:charts.stockMovements.adjustment,borderWidth:2,tension:.25}
                    ]},
                    options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}
                });
            }

            salesTrendChart();
            stockMovementChart();
            simpleChart('shippingChart','doughnut',charts.shipping.labels,charts.shipping.data,'Versand');
            simpleChart('topProductsChart','bar',charts.topProducts.labels,charts.topProducts.data,'Umsatz €');
            simpleChart('topCustomersChart','bar',charts.topCustomers.labels,charts.topCustomers.data,'Umsatz €');
            simpleChart('stockStatusChart','doughnut',charts.stockStatus.labels,charts.stockStatus.data,'Produkte');
            simpleChart('branchesChart','bar',charts.branches.labels,charts.branches.data,'Menge');
            simpleChart('walletChart','doughnut',charts.wallet.labels,charts.wallet.data,'Betrag €');
            simpleChart('employeesChart','bar',charts.employees.labels,charts.employees.data,'Umsatz €');
            simpleChart('offerStatusChart','doughnut',charts.offerStatus.labels,charts.offerStatus.data,'Vorgänge');
        });
    </script>
</x-layouts.premium>
