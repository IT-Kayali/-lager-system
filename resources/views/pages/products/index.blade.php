<x-layouts.premium title="Produkte" subtitle="Produktübersicht mit den wichtigsten Daten.">
    @php
        $isSales = auth()->user()?->isSales();
        $productIndexRoute = $isSales ? 'sales.products.index' : 'products.index';
        $productShowRoute = $isSales ? 'sales.products.show' : 'products.show';
        $branchCreateRoute = $isSales ? 'sales.branch-withdrawals.create' : 'branch-withdrawals.create';
    @endphp

    <section class="products-page-actions">
        <div class="products-page-search-card">
            <form method="GET" action="{{ route($productIndexRoute) }}" class="products-search-modern">
                <div class="products-search-field">
                    <i class="bi bi-search"></i>
                    <input name="search" value="{{ $search ?? '' }}" class="premium-input" placeholder="Produkt suchen...">
                </div>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if (! empty($search))
                    <a href="{{ route($productIndexRoute) }}" class="premium-btn products-reset-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>
        </div>

        @unless($isSales)
            <div class="products-page-action-buttons">
                <a href="{{ route('products.excel.export') }}" class="premium-btn">
                    <i class="bi bi-download"></i>
                    Excel exportieren
                </a>

                <a href="{{ route('products.excel.import.form') }}" class="premium-btn">
                    <i class="bi bi-upload"></i>
                    Excel importieren
                </a>

                <a href="{{ route('products.create') }}" class="premium-btn gold">
                    <i class="bi bi-plus-lg"></i>
                    Produkt hinzufügen
                </a>
            </div>
        @endunless
    </section>

    <section class="products-modern-card">
        <div class="products-table-shell modern-products-table-shell">
            <table class="products-clean-table modern-products-table">
                <thead>
                    <tr>
                        <th>Produktbezeichnung</th>
                        <th>Hersteller</th>
                        <th>Code-Nummer</th>
                        <th>Lieferant</th>
                        <th>Verfügbare Menge</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        @php
                            $statusLabels = ['ok' => 'OK', 'low' => 'Niedrig', 'critical' => 'Kritisch'];
                            $unitShortLabels = ['gram' => 'g', 'liter' => 'L', 'piece' => 'Stk.'];
                            $unitShort = $unitShortLabels[$product->unit] ?? $product->unit;
                            $supplierName = $product->supplierRecord?->company_name ?: $product->supplier ?: '—';
                            $codeNumber = $product->serial_number ?: ($product->product_code ?: '—');
                            $stockStatus = $product->stock_status;
                        @endphp

                        <tr>
                            <td>
                                <a class="product-name product-name-link" href="{{ route($productShowRoute, $product) }}">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td>{{ $product->manufacturer_designation ?: '—' }}</td>
                            <td><span class="products-code">{{ $codeNumber }}</span></td>
                            <td>{{ $supplierName }}</td>
                            <td>
                                <span class="products-stock-value {{ $stockStatus }}">{{ number_format((float) $product->available_stock, 2, ',', '.') }}</span>
                                <span class="unit-small">{{ $unitShort }}</span>
                            </td>
                            <td>
                                <span class="products-status-pill {{ $stockStatus }}">{{ $statusLabels[$stockStatus] ?? $stockStatus }}</span>
                            </td>
                            <td>
                                <div class="premium-actions products-actions">
                                    <a class="premium-icon-btn" href="{{ route($productShowRoute, $product) }}" title="Vorschau">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route($branchCreateRoute, ['product_id' => $product->id]) }}" title="Filialausgang erstellen">
                                        <i class="bi bi-shop"></i>
                                    </a>

                                    @unless($isSales)
                                        <a class="premium-icon-btn" href="{{ route('batches.create', ['product_id' => $product->id]) }}" title="Bestand buchen">
                                            <i class="bi bi-layers"></i>
                                        </a>

                                        <a class="premium-icon-btn" href="{{ route('products.edit', $product) }}" title="Bearbeiten">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Produkt wirklich löschen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="premium-icon-btn premium-danger" type="submit" title="Löschen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="products-empty-state">
                                    <i class="bi bi-box-seam"></i>
                                    <strong>Noch keine Produkte vorhanden.</strong>
                                    <span>Aktuell sind keine Produkte vorhanden.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="products-pagination">{{ $products->links() }}</div>
    </section>

    <style>
        .products-page-actions{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:center;margin-bottom:22px}.products-page-search-card{min-width:0;padding:14px;border:1px solid #d8cbb7;border-radius:18px;background:rgba(255,255,255,.82);box-shadow:0 12px 28px rgba(42,36,25,.06)}.products-search-modern{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.products-search-field{position:relative;min-width:280px;flex:1}.products-search-field i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#665f54;font-size:17px;pointer-events:none}.products-search-field .premium-input{width:100%;padding-left:42px!important;min-height:48px;background:#fffdf8!important}.products-page-action-buttons{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap}.products-modern-card{border:1px solid #d8cbb7;border-radius:22px;background:rgba(255,255,255,.86);box-shadow:0 18px 45px rgba(42,36,25,.08);overflow:hidden}.modern-products-table-shell{margin-top:0!important;border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important}.modern-products-table{min-width:1080px}.modern-products-table thead th{padding:18px!important;background:#eee7dc!important;color:#3a332a!important;border-bottom:2px solid #8d8069!important}.modern-products-table tbody td{padding:18px!important;color:#111!important}.product-name-link{color:#111!important;font-size:16px;font-weight:950;text-decoration:none}.product-name-link:hover{color:#8a6a00!important;text-decoration:underline}.products-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace;font-size:13px;font-weight:850;color:#3a332a}.products-stock-value{font-size:15px;font-weight:950;color:#111}.products-stock-value.low{color:#d97706}.products-stock-value.critical{color:#c91f1f}.unit-small{color:#665f54;font-size:12px;font-weight:850;margin-left:4px}.products-status-pill{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:950;line-height:1;background:#dcfce7;color:#166534}.products-status-pill.low{background:#fef3c7;color:#b45309}.products-status-pill.critical{background:#fee2e2;color:#991b1b}.products-actions{justify-content:flex-end;flex-wrap:nowrap;gap:8px}.products-actions form{margin:0}.premium-icon-btn{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #d8cbb7;border-radius:10px;background:#fffdf8;color:#111;text-decoration:none;cursor:pointer;transition:transform .16s ease,border-color .16s ease,background .16s ease}.premium-icon-btn:hover{transform:translateY(-1px);border-color:#c9a227;background:#fff7dc;color:#111}.premium-icon-btn.premium-danger,.premium-danger{color:#991b1b}.premium-icon-btn.premium-danger:hover,.premium-danger:hover{border-color:#ef4444;background:#fee2e2;color:#991b1b}.products-empty-state{display:grid;place-items:center;gap:8px;padding:52px 16px;text-align:center;color:#665f54}.products-empty-state i{font-size:34px;color:#8a6a00}.products-empty-state strong{color:#111;font-size:17px}.products-pagination{padding:16px 18px;border-top:1px solid #e7dece;background:#f8f2e7}@media(max-width:1250px){.products-page-actions{grid-template-columns:1fr}.products-page-action-buttons{justify-content:flex-start}}@media(max-width:700px){.products-search-modern{display:grid}.products-search-field{min-width:0}.products-page-action-buttons{display:grid}}
    </style>
</x-layouts.premium>
