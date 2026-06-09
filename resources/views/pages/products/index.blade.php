<x-layouts.premium title="Produkte" subtitle="Produktverwaltung ohne Produktcode, Hersteller und Charge. Preise werden ausschließlich im Tab Preise gepflegt.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar product-toolbar">
            <form method="GET" action="{{ route('products.index') }}" class="premium-search product-search">
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

        <div class="product-card-list">
            @forelse ($products as $product)
                @php
                    $statusLabels = [
                        'ok' => 'OK',
                        'low' => 'Niedrig',
                        'critical' => 'Kritisch',
                    ];

                    $unitLabels = [
                        'gram' => 'Gramm',
                        'liter' => 'Liter',
                        'piece' => 'Stück',
                    ];

                    $unitLabel = $unitLabels[$product->unit] ?? $product->unit;
                    $supplierName = $product->supplierRecord?->company_name ?: $product->supplier ?: '—';
                @endphp

                <article class="product-row-card">
                    <div class="product-row-head">
                        <div>
                            <div class="product-title">
                                {{ $product->name }}
                            </div>

                            <div class="product-meta">
                                <span>
                                    <i class="bi bi-upc-scan"></i>
                                    Code: {{ $product->serial_number ?: '—' }}
                                </span>

                                <span>
                                    <i class="bi bi-building"></i>
                                    Hersteller: {{ $product->manufacturer_designation ?: '—' }}
                                </span>

                                <span>
                                    <i class="bi bi-truck"></i>
                                    Lieferant: {{ $supplierName }}
                                </span>

                                <span>
                                    <i class="bi bi-rulers"></i>
                                    Einheit: {{ $unitLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="product-status">
                            <span class="premium-badge {{ $product->stock_status }}">
                                {{ $statusLabels[$product->stock_status] ?? $product->stock_status }}
                            </span>
                        </div>
                    </div>

                    <div class="product-row-body">
                        <div class="product-stock-grid">
                            <div class="product-stock-box">
                                <span>Gesamt</span>
                                <strong>{{ number_format((float) $product->total_stock, 2, ',', '.') }}</strong>
                            </div>

                            <div class="product-stock-box">
                                <span>Reserviert</span>
                                <strong>{{ number_format((float) $product->reserved_stock, 2, ',', '.') }}</strong>
                            </div>

                            <div class="product-stock-box highlight">
                                <span>Verfügbar</span>
                                <strong>{{ number_format((float) $product->available_stock, 2, ',', '.') }}</strong>
                            </div>

                            <div class="product-stock-box">
                                <span>Mindestbestand</span>
                                <strong>{{ number_format((float) $product->minimum_stock, 2, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="product-actions">
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
                    </div>
                </article>
            @empty
                <div class="product-empty">
                    <i class="bi bi-box-seam"></i>
                    <strong>Noch keine Produkte vorhanden.</strong>
                    <span>Lege dein erstes Produkt über „Produkt hinzufügen“ an.</span>
                </div>
            @endforelse
        </div>

        <div style="margin-top:18px;">
            {{ $products->links() }}
        </div>
    </section>

    <style>
        .product-toolbar {
            align-items: flex-start;
            gap: 16px;
        }

        .product-search {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .product-search .premium-input {
            min-width: 360px;
        }

        .product-card-list {
            display: grid;
            gap: 12px;
            margin-top: 22px;
        }

        .product-row-card {
            background: #ffffff;
            border: 1px solid #e3dacb;
            border-radius: 20px;
            padding: 16px;
            box-shadow: 0 10px 28px rgba(0,0,0,.035);
            transition: border-color .18s ease, box-shadow .18s ease, transform .14s ease;
        }

        .product-row-card:hover {
            border-color: rgba(212, 175, 55, .75);
            box-shadow: 0 16px 34px rgba(0,0,0,.07);
            transform: translateY(-1px);
        }

        .product-row-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid #eee5d8;
        }

        .product-title {
            font-size: 20px;
            font-weight: 950;
            color: #111111;
            line-height: 1.15;
        }

        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            margin-top: 9px;
            color: #6f665a;
            font-weight: 700;
            font-size: 13px;
        }

        .product-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .product-meta i {
            color: #a9871f;
        }

        .product-status {
            flex: 0 0 auto;
            padding-top: 2px;
        }

        .product-row-body {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: center;
            padding-top: 14px;
        }

        .product-stock-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 10px;
        }

        .product-stock-box {
            background: #fbf8f1;
            border: 1px solid #eadfcd;
            border-radius: 16px;
            padding: 11px 12px;
        }

        .product-stock-box span {
            display: block;
            color: #7b7164;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .product-stock-box strong {
            display: block;
            color: #111111;
            font-size: 16px;
            font-weight: 950;
        }

        .product-stock-box.highlight {
            background: #fff7dc;
            border-color: #dcc067;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            align-items: center;
            white-space: nowrap;
        }

        .product-empty {
            min-height: 170px;
            display: grid;
            place-items: center;
            text-align: center;
            gap: 8px;
            color: #6f665a;
            border: 1px dashed #d8cab4;
            border-radius: 20px;
            background: #fffdf8;
            padding: 28px;
        }

        .product-empty i {
            font-size: 32px;
            color: #d4af37;
        }

        .product-empty strong {
            color: #111111;
            font-size: 18px;
        }

        @media (max-width: 1100px) {
            .product-row-body {
                grid-template-columns: 1fr;
            }

            .product-actions {
                justify-content: flex-start;
            }

            .product-stock-grid {
                grid-template-columns: repeat(2, minmax(120px, 1fr));
            }
        }

        @media (max-width: 700px) {
            .product-toolbar {
                display: grid;
            }

            .product-search .premium-input {
                min-width: 100%;
                width: 100%;
            }

            .product-row-head {
                display: grid;
            }

            .product-status {
                justify-self: start;
            }

            .product-stock-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-layouts.premium>
