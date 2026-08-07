<x-layouts.premium title="Produktvorschau" subtitle="Alle wichtigen Informationen zum Produkt auf einen Blick.">
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

    <section class="product-preview-hero">
        <div>
            <div class="product-preview-kicker">Produkt</div>
            <h1>{{ $product->name }}</h1>

            <div class="product-preview-meta">
                <span><i class="bi bi-upc-scan"></i> Code: {{ $product->serial_number ?: '—' }}</span>
                <span><i class="bi bi-rulers"></i> Einheit: {{ $unitLabel }}</span>
                <span><i class="bi bi-truck"></i> Lieferant: {{ $supplierName }}</span>
            </div>
        </div>

        <div class="product-preview-status">
            <span class="premium-badge {{ $product->stock_status }}">
                {{ $statusLabels[$product->stock_status] ?? $product->stock_status }}
            </span>
        </div>
    </section>

    <section class="product-preview-actions">
        <a class="premium-btn gold" href="{{ route('batches.create', ['product_id' => $product->id]) }}">
            <i class="bi bi-grid"></i>
            Bestand buchen
        </a>

        <a class="premium-btn" href="{{ route('branch-withdrawals.create', ['product_id' => $product->id]) }}">
            <i class="bi bi-shop"></i>
            Filialausgang
        </a>

        <a class="premium-btn" href="{{ route('products.edit', $product) }}">
            <i class="bi bi-pencil"></i>
            Bearbeiten
        </a>

        <a class="premium-btn" href="{{ route('products.index') }}">
            <i class="bi bi-arrow-left"></i>
            Zurück
        </a>
    </section>

    <section class="preview-grid">
        <div class="premium-card">
            <h2 class="preview-title">Bestand</h2>

            <div class="stock-preview-grid">
                <div class="stock-preview-box">
                    <span>Gesamt</span>
                    <strong>{{ \App\Support\GermanNumber::format($product->total_stock) }}</strong>
                </div>

                <div class="stock-preview-box">
                    <span>Reserviert</span>
                    <strong>{{ \App\Support\GermanNumber::format($product->reserved_stock) }}</strong>
                </div>

                <div class="stock-preview-box highlight">
                    <span>Verfügbar</span>
                    <strong>{{ \App\Support\GermanNumber::format($product->available_stock) }}</strong>
                </div>

                <div class="stock-preview-box">
                    <span>Mindestbestand</span>
                    <strong>{{ \App\Support\GermanNumber::format($product->minimum_stock) }}</strong>
                </div>
            </div>
        </div>

        <div class="premium-card">
            <h2 class="preview-title">Kategorien</h2>

            <div class="category-preview-list">
                @forelse ($product->categories as $category)
                    <span class="category-preview-badge" style="--cat-color: {{ $category->color ?: '#d4af37' }};">
                        <span></span>
                        {{ $category->name }}
                    </span>
                @empty
                    <div class="premium-muted">Keine Kategorien zugeordnet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="premium-card">
        <h2 class="preview-title">Produktdaten</h2>

        <div class="details-grid">
            <div>
                <span>Bezeichnung</span>
                <strong>{{ $product->name }}</strong>
            </div>

            <div>
                <span>Bezeichnung durch Hersteller</span>
                <strong>{{ $product->manufacturer_designation ?: '—' }}</strong>
            </div>

            <div>
                <span>Code-Nummer</span>
                <strong>{{ $product->serial_number ?: '—' }}</strong>
            </div>

            <div>
                <span>Einheit</span>
                <strong>{{ $unitLabel }}</strong>
            </div>

            <div>
                <span>Lieferant</span>
                <strong>{{ $supplierName }}</strong>
            </div>
        </div>

        @if (! empty($product->description))
            <div class="product-description-box">
                <span>Beschreibung</span>
                <p>{{ $product->description }}</p>
            </div>
        @endif
    </section>

    <section class="premium-card">
        <div class="section-head">
            <div>
                <h2 class="preview-title">Chargen</h2>
                <p class="premium-muted" style="margin:4px 0 0;">Wareneingang, Ablaufdatum und aktueller Chargenbestand.</p>
            </div>

            <a class="premium-btn gold" href="{{ route('batches.create', ['product_id' => $product->id]) }}">
                <i class="bi bi-plus-lg"></i>
                Charge hinzufügen
            </a>
        </div>

        <div class="batch-preview-list">
            @forelse ($product->batches as $batch)
                <article class="batch-preview-card">
                    <div>
                        <strong>{{ $batch->batch_number }}</strong>
                        <span>{{ $batch->received_at ? $batch->received_at->format('d.m.Y') : '—' }}</span>
                    </div>

                    <div>
                        <span>Menge</span>
                        <strong>{{ \App\Support\GermanNumber::format($batch->quantity) }} {{ $product->unit === 'gram' ? 'g' : $unitLabel }}</strong>
                    </div>

                    <div>
                        <span>Ablaufdatum</span>
                        <strong>{{ $batch->expires_at ? $batch->expires_at->format('d.m.Y') : '—' }}</strong>
                    </div>

                    <div class="batch-preview-actions">
                        <a class="premium-icon-btn" href="{{ route('batches.edit', $batch) }}" title="Charge bearbeiten">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="product-empty">
                    <i class="bi bi-box-seam"></i>
                    <strong>Noch keine Chargen vorhanden.</strong>
                    <span>Lege eine Charge an, um Bestand zu buchen.</span>
                </div>
            @endforelse
        </div>
    </section>

    <style>
        .product-preview-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            background: #ffffff;
            border: 1px solid #e3dacb;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(0,0,0,.05);
            margin-bottom: 14px;
        }

        .product-preview-kicker {
            color: #a9871f;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .product-preview-hero h1 {
            font-size: 34px;
            font-weight: 950;
            margin: 0;
            color: #111111;
        }

        .product-preview-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            margin-top: 12px;
            color: #6f665a;
            font-weight: 800;
        }

        .product-preview-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .product-preview-meta i {
            color: #a9871f;
        }

        .product-preview-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: 1.4fr .8fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .preview-title {
            font-size: 20px;
            font-weight: 950;
            margin: 0 0 14px;
            color: #111111;
        }

        .stock-preview-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 10px;
        }

        .stock-preview-box {
            background: #fbf8f1;
            border: 1px solid #eadfcd;
            border-radius: 18px;
            padding: 14px;
        }

        .stock-preview-box span,
        .details-grid span,
        .product-description-box span,
        .batch-preview-card span {
            display: block;
            color: #7b7164;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stock-preview-box strong {
            font-size: 20px;
            font-weight: 950;
            color: #111111;
        }

        .stock-preview-box.highlight {
            background: #fff7dc;
            border-color: #dcc067;
        }

        .category-preview-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .category-preview-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #fff7dc;
            border: 1px solid var(--cat-color);
            font-weight: 900;
        }

        .category-preview-badge span {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: var(--cat-color);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 12px;
        }

        .details-grid div,
        .product-description-box {
            background: #fffdf8;
            border: 1px solid #eadfcd;
            border-radius: 18px;
            padding: 14px;
        }

        .details-grid strong {
            color: #111111;
            font-weight: 900;
        }

        .product-description-box {
            margin-top: 12px;
        }

        .product-description-box p {
            margin: 0;
            color: #111111;
            font-weight: 700;
            line-height: 1.5;
        }

        .section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .batch-preview-list {
            display: grid;
            gap: 10px;
        }

        .batch-preview-card {
            display: grid;
            grid-template-columns: 1.2fr .6fr .6fr auto;
            gap: 12px;
            align-items: center;
            background: #ffffff;
            border: 1px solid #e3dacb;
            border-radius: 18px;
            padding: 14px;
        }

        .batch-preview-card strong {
            color: #111111;
            font-weight: 950;
        }

        .batch-preview-actions {
            display: flex;
            justify-content: flex-end;
        }

        .product-empty {
            min-height: 150px;
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
            font-size: 30px;
            color: #d4af37;
        }

        .product-empty strong {
            color: #111111;
            font-size: 18px;
        }

        @media (max-width: 1100px) {
            .preview-grid {
                grid-template-columns: 1fr;
            }

            .stock-preview-grid,
            .details-grid {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
            }

            .batch-preview-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .product-preview-hero,
            .section-head {
                display: grid;
            }

            .stock-preview-grid,
            .details-grid,
            .batch-preview-card {
                grid-template-columns: 1fr;
            }

            .product-preview-hero h1 {
                font-size: 28px;
            }
        }
    </style>
</x-layouts.premium>
