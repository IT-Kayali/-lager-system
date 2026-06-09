<x-layouts.premium title="Produkte" subtitle="Produktübersicht mit den wichtigsten Daten. Details findest du in der Produktvorschau.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar products-toolbar">
            <form method="GET" action="{{ route('products.index') }}" class="products-search">
                <input
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="premium-input"
                    placeholder="Suchen nach Bezeichnung, Code-Nummer, Lieferant..."
                >

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if (! empty($search))
                    <a href="{{ route('products.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <a href="{{ route('products.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Produkt hinzufügen
            </a>
        </div>

        <div class="products-table-shell">
            <table class="products-clean-table">
                <thead>
                    <tr>
                        <th>Produktbezeichnung</th>
                        <th>Bezeichnung durch Hersteller</th>
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
                            $statusLabels = [
                                'ok' => 'OK',
                                'low' => 'Niedrig',
                                'critical' => 'Kritisch',
                            ];

                            $unitShortLabels = [
                                'gram' => 'g',
                                'liter' => 'L',
                                'piece' => 'Stk.',
                            ];

                            $unitShort = $unitShortLabels[$product->unit] ?? $product->unit;
                            $supplierName = $product->supplierRecord?->company_name ?: $product->supplier ?: '—';
                        @endphp

                        <tr>
                            <td>
                                <a class="product-name product-name-link" href="{{ route('products.show', ['product' => $product->name]) }}">
                                    {{ $product->name }}
                                </a>
                            </td>

                            <td>{{ $product->manufacturer_designation ?: '—' }}</td>

                            <td>{{ $product->serial_number ?: '—' }}</td>

                            <td>{{ $supplierName }}</td>

                            <td>
                                <strong>{{ number_format((float) $product->available_stock, 2, ',', '.') }}</strong>
                                <span class="unit-small">{{ $unitShort }}</span>
                            </td>

                            <td>
                                <span class="premium-badge {{ $product->stock_status }}">
                                    {{ $statusLabels[$product->stock_status] ?? $product->stock_status }}
                                </span>
                            </td>

                            <td>
                                <div class="premium-actions products-actions">
                                    <a class="premium-icon-btn" href="{{ route('products.show', ['product' => $product->name]) }}" title="Vorschau">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('branch-withdrawals.create', ['product_id' => $product->id]) }}" title="Filialausgang buchen">
                                        <i class="bi bi-shop"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('batches.create', ['product_id' => $product->id]) }}" title="Bestand buchen">
                                        <i class="bi bi-grid"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('products.edit', $product) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Produkt wirklich löschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="premium-icon-btn premium-danger" type="submit" title="Löschen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="premium-muted">Noch keine Produkte vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $products->links() }}
        </div>
    </section>

    <style>
        .products-toolbar {
            align-items: flex-start;
            gap: 16px;
        }

        .products-search {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .products-search .premium-input {
            width: 390px;
            max-width: 100%;
        }

        .products-table-shell {
            margin-top: 22px;
            overflow-x: auto;
            border: 1px solid #e7dece;
            background: #ffffff;
        }

        .products-clean-table {
            width: 100%;
            min-width: 980px;
            border-collapse: collapse;
        }

        .products-clean-table thead th {
            padding: 14px 12px;
            color: #7a7064;
            font-size: 11px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .06em;
            white-space: nowrap;
            text-align: left;
            border-bottom: 1px solid #e7dece;
            background: #fffdf8;
        }

        .products-clean-table tbody td {
            padding: 16px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e7dece;
            white-space: nowrap;
            background: #ffffff;
        }

        .products-clean-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .products-clean-table tbody tr:hover td {
            background: #fffaf0;
        }

        .product-name {
            font-size: 15px;
            font-weight: 950;
            color: #111111;
        }

        .unit-small {
            color: #7a7064;
            font-size: 12px;
            font-weight: 800;
            margin-left: 4px;
        }

        .products-clean-table th:nth-child(1),
        .products-clean-table td:nth-child(1) {
            width: 190px;
        }

        .products-clean-table th:nth-child(2),
        .products-clean-table td:nth-child(2) {
            width: 230px;
        }

        .products-clean-table th:nth-child(3),
        .products-clean-table td:nth-child(3) {
            width: 140px;
        }

        .products-clean-table th:nth-child(4),
        .products-clean-table td:nth-child(4) {
            width: 160px;
        }

        .products-clean-table th:nth-child(5),
        .products-clean-table td:nth-child(5) {
            width: 150px;
            text-align: right;
        }

        .products-clean-table th:nth-child(6),
        .products-clean-table td:nth-child(6) {
            width: 110px;
            text-align: center;
        }

        .products-clean-table th:nth-child(7),
        .products-clean-table td:nth-child(7) {
            width: 190px;
            text-align: right;
        }

        .products-actions {
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: 8px;
        }

        .products-actions form {
            margin: 0;
        }

        @media (max-width: 900px) {
            .products-toolbar {
                display: grid;
            }

            .products-search,
            .products-search .premium-input {
                width: 100%;
            }
        }
    
        .product-name-link {
            color: #111111;
            text-decoration: none;
            font-size: 15px;
            font-weight: 950;
        }

        .product-name-link:hover {
            color: #a9871f;
            text-decoration: underline;
        }

</style>
</x-layouts.premium>
