<x-layouts.premium title="Kategorievorschau" subtitle="Eigenschaften der Kategorie und alle enthaltenen Produkte.">
    @php
        $statusLabel = $category->is_active ? 'Aktiv' : 'Inaktiv';
        $statusClass = $category->is_active ? 'ok' : 'critical';
        $statusLabels = ['ok' => 'OK', 'low' => 'Niedrig', 'critical' => 'Kritisch'];
    @endphp

    <section class="category-preview-hero" style="--cat-color: {{ $category->color ?: '#d4af37' }};">
        <div>
            <div class="category-preview-kicker">Kategorie</div>
            <h1><span></span>{{ $category->name }}</h1>
            <p>{{ $category->description ?: 'Keine Beschreibung hinterlegt.' }}</p>
        </div>
        <span class="premium-badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </section>

    <section class="category-preview-actions">
        <a class="premium-btn gold" href="{{ route('product-categories.edit', $category) }}"><i class="bi bi-pencil"></i> Bearbeiten</a>
        <a class="premium-btn" href="{{ route('product-categories.index') }}"><i class="bi bi-arrow-left"></i> Zurück</a>
    </section>

    <section class="preview-grid">
        <div class="premium-card">
            <h2 class="preview-title">Eigenschaften</h2>
            <div class="details-grid">
                <div><span>Name</span><strong>{{ $category->name }}</strong></div>
                <div><span>Status</span><strong>{{ $statusLabel }}</strong></div>
                <div><span>Produkte</span><strong>{{ $category->products->count() }}</strong></div>
                <div><span>Farbe</span><strong class="color-value"><i style="background: {{ $category->color ?: '#d4af37' }};"></i>{{ $category->color ?: '#d4af37' }}</strong></div>
                <div><span>Erstellt</span><strong>{{ $category->created_at ? $category->created_at->format('d.m.Y H:i') : '—' }}</strong></div>
                <div><span>Aktualisiert</span><strong>{{ $category->updated_at ? $category->updated_at->format('d.m.Y H:i') : '—' }}</strong></div>
            </div>
        </div>
        <div class="premium-card">
            <h2 class="preview-title">Beschreibung</h2>
            <div class="description-box">{{ $category->description ?: 'Keine Beschreibung hinterlegt.' }}</div>
        </div>
    </section>

    <section class="premium-card">
        <div class="section-head">
            <div>
                <h2 class="preview-title">Enthaltene Produkte</h2>
                <p class="premium-muted" style="margin:4px 0 0;">Alle Produkte, die dieser Kategorie zugeordnet sind.</p>
            </div>
            <div class="category-products-actions">
                <div class="category-product-search">
                    <i class="bi bi-search"></i>
                    <input id="category-product-search" class="premium-input" type="search" placeholder="Produktbezeichnung suchen..." autocomplete="off">
                    <button id="category-product-search-clear" type="button" class="category-product-search-clear" title="Suche löschen" aria-label="Suche löschen"><i class="bi bi-x-lg"></i></button>
                </div>
                <a class="premium-btn gold" href="{{ route('products.create', ['category_id' => $category->id]) }}"><i class="bi bi-plus-lg"></i> Produkt hinzufügen</a>
            </div>
        </div>

        <div class="category-products-table-shell">
            <table class="category-products-table" id="category-products-table">
                <thead><tr><th>Produktbezeichnung</th><th>Fake Name</th><th>Code-Nummer</th><th>Lieferant</th><th>Verfügbare Menge</th><th>Status</th><th>Aktion</th></tr></thead>
                <tbody>
                    @forelse ($category->products as $product)
                        @php
                            $unitLabel = $product->unitLabel('de');
                            $supplierName = $product->supplierRecord?->company_name ?: $product->supplier ?: '—';
                        @endphp
                        <tr data-category-product-row>
                            <td><a class="product-link" href="{{ route('products.show', ['product' => $product->name]) }}">{{ $product->name }}</a></td>
                            <td>{{ $product->manufacturer_designation ?: '—' }}</td>
                            <td>{{ $product->serial_number ?: '—' }}</td>
                            <td>{{ $supplierName }}</td>
                            <td><strong>{{ number_format((float) $product->available_stock, 2, ',', '.') }}</strong><span class="unit-small">{{ $unitLabel }}</span></td>
                            <td><span class="premium-badge {{ $product->stock_status }}">{{ $statusLabels[$product->stock_status] ?? $product->stock_status }}</span></td>
                            <td><a class="premium-icon-btn" href="{{ route('products.show', ['product' => $product->name]) }}" title="Vorschau"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="premium-muted">Diese Kategorie enthält noch keine Produkte.</div></td></tr>
                    @endforelse
                    @if ($category->products->isNotEmpty())
                        <tr id="category-product-search-empty" hidden><td colspan="7"><div class="premium-muted">Kein passendes Produkt gefunden.</div></td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <script>
        (() => {
            const input = document.getElementById('category-product-search');
            const clear = document.getElementById('category-product-search-clear');
            const rows = Array.from(document.querySelectorAll('[data-category-product-row]'));
            const empty = document.getElementById('category-product-search-empty');
            if (!input || !rows.length) return;
            const filter = () => {
                const term = input.value.trim().toLocaleLowerCase('de');
                let visible = 0;
                rows.forEach((row) => {
                    const productName = row.querySelector('.product-link')?.textContent.trim().toLocaleLowerCase('de') ?? '';
                    const matches = term === '' || productName.includes(term);
                    row.hidden = !matches;
                    if (matches) visible++;
                });
                if (empty) empty.hidden = visible !== 0;
                if (clear) clear.classList.toggle('visible', term !== '');
            };
            input.addEventListener('input', filter);
            clear?.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
        })();
    </script>

    <style>
        .category-preview-hero{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;background:#fff;border:1px solid #e3dacb;border-left:7px solid var(--cat-color);border-radius:24px;padding:24px;box-shadow:0 18px 45px rgba(0,0,0,.05);margin-bottom:14px}.category-preview-kicker{color:#a9871f;font-size:12px;font-weight:950;letter-spacing:.08em;text-transform:uppercase;margin-bottom:6px}.category-preview-hero h1{display:flex;align-items:center;gap:10px;font-size:34px;font-weight:950;margin:0;color:#111}.category-preview-hero h1 span{width:14px;height:14px;border-radius:999px;background:var(--cat-color);box-shadow:0 0 0 4px rgba(0,0,0,.04)}.category-preview-hero p{margin:10px 0 0;color:#6f665a;font-weight:700;line-height:1.5}.category-preview-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}.preview-grid{display:grid;grid-template-columns:1.35fr .75fr;gap:14px;margin-bottom:14px}.preview-title{font-size:20px;font-weight:950;margin:0 0 14px;color:#111}.details-grid{display:grid;grid-template-columns:repeat(3,minmax(160px,1fr));gap:12px}.details-grid div,.description-box{background:#fffdf8;border:1px solid #eadfcd;border-radius:18px;padding:14px}.details-grid span{display:block;color:#7b7164;font-size:11px;font-weight:950;letter-spacing:.06em;text-transform:uppercase;margin-bottom:5px}.details-grid strong{color:#111;font-weight:950}.color-value{display:inline-flex;align-items:center;gap:8px}.color-value i{width:12px;height:12px;border-radius:999px;display:inline-block}.description-box{color:#111;font-weight:700;line-height:1.55;min-height:104px}.section-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:14px}.category-products-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-wrap:wrap}.category-product-search{position:relative;width:min(360px,40vw)}.category-product-search>i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#7a7064;pointer-events:none}.category-product-search .premium-input{width:100%;padding-left:42px!important;padding-right:42px!important}.category-product-search-clear{display:none;position:absolute;right:8px;top:50%;transform:translateY(-50%);width:32px;height:32px;border:0;border-radius:9px;background:transparent;cursor:pointer;color:#6f665a}.category-product-search-clear.visible{display:grid;place-items:center}.category-product-search-clear:hover{background:#f3ead8;color:#111}.category-products-table-shell{overflow-x:auto;border:1px solid #e7dece;background:#fff}.category-products-table{width:100%;min-width:980px;border-collapse:collapse}.category-products-table th{padding:14px 12px;color:#7a7064;font-size:11px;font-weight:950;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;text-align:left;border-bottom:1px solid #e7dece;background:#fffdf8}.category-products-table td{padding:16px 12px;border-bottom:1px solid #e7dece;white-space:nowrap;vertical-align:middle}.category-products-table tr:last-child td{border-bottom:0}.category-products-table tr:hover td{background:#fffaf0}.product-link{color:#111;font-weight:950;text-decoration:none}.product-link:hover{color:#a9871f;text-decoration:underline}.unit-small{color:#7a7064;font-size:12px;font-weight:800;margin-left:4px}@media(max-width:1100px){.preview-grid{grid-template-columns:1fr}.details-grid{grid-template-columns:repeat(2,minmax(150px,1fr))}.category-product-search{width:min(320px,100%)}}@media(max-width:700px){.category-preview-hero,.section-head{display:grid}.details-grid{grid-template-columns:1fr}.category-preview-hero h1{font-size:28px}.category-products-actions{width:100%;display:grid}.category-product-search{width:100%}.category-products-actions .premium-btn{justify-content:center}}
    </style>
</x-layouts.premium>