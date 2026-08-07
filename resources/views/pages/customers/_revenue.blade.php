@php
    $unitLabels = [
        'gram' => 'g',
        'grams' => 'g',
        'g' => 'g',
        'piece' => 'Stk.',
        'pieces' => 'Stk.',
        'pcs' => 'Stk.',
        'stk' => 'Stk.',
    ];
@endphp

<section id="customer-revenue" class="premium-card" style="margin-top:18px;">
    <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:18px;">
        <div>
            <div class="premium-muted" style="font-weight:900; text-transform:uppercase; letter-spacing:.08em;">Kundenumsatz</div>
            <h2 style="font-size:24px; font-weight:950; margin:5px 0 5px;">Umsatz und gekaufte Produkte</h2>
            <div class="premium-muted">Es werden nur erledigte Verkäufe berücksichtigt.</div>
        </div>

        <form method="GET" action="{{ route('customers.show', $customer) }}" style="min-width:240px;">
            <div class="premium-form-field">
                <label for="revenue_year">Zeitraum</label>
                <select
                    id="revenue_year"
                    name="revenue_year"
                    class="premium-select"
                    data-search="false"
                    onchange="this.form.submit()"
                >
                    <option value="all" @selected($selectedRevenueYear === 'all')>Gesamter Zeitraum</option>
                    @foreach ($revenueYears as $year)
                        <option value="{{ $year }}" @selected($selectedRevenueYear === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:18px;">
        <div class="premium-card" style="padding:16px; box-shadow:none;">
            <div class="premium-muted" style="font-weight:900; text-transform:uppercase; letter-spacing:.06em;">Warenwert</div>
            <div style="font-size:27px; font-weight:950; margin-top:6px;">
                {{ \App\Support\GermanNumber::format($customerRevenue) }} €
            </div>
        </div>

        <div class="premium-card" style="padding:16px; box-shadow:none;">
            <div class="premium-muted" style="font-weight:900; text-transform:uppercase; letter-spacing:.06em;">Erledigte Käufe</div>
            <div style="font-size:27px; font-weight:950; margin-top:6px;">{{ $completedSalesCount }}</div>
        </div>

        <div class="premium-card" style="padding:16px; box-shadow:none;">
            <div class="premium-muted" style="font-weight:900; text-transform:uppercase; letter-spacing:.06em;">Produkte</div>
            <div style="font-size:27px; font-weight:950; margin-top:6px;">{{ $revenueProductCount }}</div>
        </div>

        <div class="premium-card" style="padding:16px; box-shadow:none;">
            <div class="premium-muted" style="font-weight:900; text-transform:uppercase; letter-spacing:.06em;">Gesamtmenge</div>
            <div style="font-size:20px; font-weight:950; margin-top:9px; line-height:1.35;">
                @forelse ($revenueQuantitySummary as $quantityGroup)
                    @php
                        $unit = strtolower(trim((string) $quantityGroup['unit']));
                        $unitLabel = $unitLabels[$unit] ?? $quantityGroup['unit'];
                    @endphp
                    <span style="white-space:nowrap;">
                        {{ \App\Support\GermanNumber::format($quantityGroup['quantity']) }} {{ $unitLabel }}
                    </span>@if (! $loop->last) <span class="premium-muted"> · </span> @endif
                @empty
                    0,00
                @endforelse
            </div>
        </div>
    </div>

    <div class="premium-table-wrap">
        <table class="premium-table premium-wide-table">
            <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Artikelnummer</th>
                    <th>Menge</th>
                    <th>Käufe</th>
                    <th>Warenwert</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($revenueProductSales as $productSale)
                    @php
                        $unit = strtolower(trim((string) $productSale['unit']));
                        $unitLabel = $unitLabels[$unit] ?? $productSale['unit'];
                    @endphp
                    <tr>
                        <td><strong>{{ $productSale['product_name'] }}</strong></td>
                        <td><span class="premium-code">{{ $productSale['product_code'] }}</span></td>
                        <td>{{ \App\Support\GermanNumber::format($productSale['quantity']) }} {{ $unitLabel }}</td>
                        <td>{{ $productSale['sales_count'] }}</td>
                        <td><strong>{{ \App\Support\GermanNumber::format($productSale['revenue']) }} €</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="premium-muted">Keine erledigten Käufe in diesem Zeitraum.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($revenueProductSales->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Gesamter Warenwert</strong></td>
                        <td><strong>{{ \App\Support\GermanNumber::format($customerRevenue) }} €</strong></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>
