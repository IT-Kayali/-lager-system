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

<section id="customer-revenue" class="premium-card" data-csp-style="s-e9f7b175">
    <div data-csp-style="s-9ed7c9ea">
        <div>
            <div class="premium-muted" data-csp-style="s-fc61b41d">Kundenumsatz</div>
            <h2 data-csp-style="s-56000839">Umsatz und gekaufte Produkte</h2>
            <div class="premium-muted">Es werden nur erledigte Verkäufe berücksichtigt.</div>
        </div>

        <form method="GET" action="{{ route('customers.show', $customer) }}" data-csp-style="s-117b5f53">
            <div class="premium-form-field">
                <label for="revenue_year">Zeitraum</label>
                <select
                    id="revenue_year"
                    name="revenue_year"
                    class="premium-select"
                    data-search="false"
                    data-auto-submit
                >
                    <option value="all" @selected($selectedRevenueYear === 'all')>Gesamter Zeitraum</option>
                    @foreach ($revenueYears as $year)
                        <option value="{{ $year }}" @selected($selectedRevenueYear === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div data-csp-style="s-53c071a4">
        <div class="premium-card" data-csp-style="s-c8488db5">
            <div class="premium-muted" data-csp-style="s-173123fb">Warenwert</div>
            <div data-csp-style="s-8f429d68">
                {{ \App\Support\GermanNumber::format($customerRevenue) }} €
            </div>
        </div>

        <div class="premium-card" data-csp-style="s-c8488db5">
            <div class="premium-muted" data-csp-style="s-173123fb">Erledigte Käufe</div>
            <div data-csp-style="s-8f429d68">{{ $completedSalesCount }}</div>
        </div>

        <div class="premium-card" data-csp-style="s-c8488db5">
            <div class="premium-muted" data-csp-style="s-173123fb">Produkte</div>
            <div data-csp-style="s-8f429d68">{{ $revenueProductCount }}</div>
        </div>

        <div class="premium-card" data-csp-style="s-c8488db5">
            <div class="premium-muted" data-csp-style="s-173123fb">Gesamtmenge</div>
            <div data-csp-style="s-35bfa37a">
                @forelse ($revenueQuantitySummary as $quantityGroup)
                    @php
                        $unit = strtolower(trim((string) $quantityGroup['unit']));
                        $unitLabel = $unitLabels[$unit] ?? $quantityGroup['unit'];
                    @endphp
                    <span data-csp-style="s-1813e65b">
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