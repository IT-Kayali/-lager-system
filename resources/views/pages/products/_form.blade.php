@csrf

@if (($suppliers ?? collect())->isEmpty())
    <div class="premium-alert" style="border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.10); color:#92400e;">
        Noch keine Lieferanten vorhanden. Du kannst das Produkt trotzdem speichern, aber empfohlen ist zuerst einen Lieferanten anzulegen.
        <a href="{{ route('suppliers.create') }}" style="font-weight:900; color:#92400e;">Lieferant hinzufügen</a>
    </div>
@endif

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="name">Bezeichnung *</label>
        <input id="name" name="name" class="premium-input" value="{{ old('name', $product->name) }}" required>
        @error('name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="manufacturer_designation">Bezeichnung durch Hersteller</label>
        <input id="manufacturer_designation" name="manufacturer_designation" class="premium-input" value="{{ old('manufacturer_designation', $product->manufacturer_designation) }}">
        @error('manufacturer_designation') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="serial_number">Code-Nummer</label>
        <input id="serial_number" name="serial_number" class="premium-input" value="{{ old('serial_number', $product->serial_number) }}">
        @error('serial_number') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="unit">Einheit *</label>
        <select id="unit" name="unit" class="premium-select" required>
            @foreach (['gram' => 'Gramm', 'liter' => 'Liter', 'piece' => 'Stück'] as $value => $label)
                <option value="{{ $value }}" @selected(old('unit', $product->unit) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('unit') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="supplier_id">Lieferant</label>
        <select id="supplier_id" name="supplier_id" class="premium-select">
            <option value="">Kein Lieferant ausgewählt</option>
            @foreach (($suppliers ?? collect()) as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id) === (string) $supplier->id)>
                    {{ $supplier->supplier_number }} — {{ $supplier->company_name }}
                </option>
            @endforeach
        </select>
        @error('supplier_id') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label for="minimum_stock">Mindestbestand *</label>
        <input id="minimum_stock" name="minimum_stock" type="number" step="0.01" min="0" class="premium-input" value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
        @error('minimum_stock') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

{{-- INITIAL_BATCH_ON_CREATE_START --}}
@if (! $product->exists)
    @php
        $initialBatchRows = collect(old('initial_batches', [
            [
                'quantity' => '',
                'batch_number' => '',
                'received_at' => now()->format('Y-m-d'),
                'expires_at' => '',
            ],
        ]))->values();
    @endphp

    <div class="premium-form-field full initial-batch-card">
        <div class="initial-batch-head">
            <div>
                <h3>Erste Chargen optional</h3>
                <p>Du kannst direkt mehrere Chargen eintragen. Sobald du eine Zeile nutzt, erscheint automatisch die nächste.</p>
            </div>
            <span class="initial-batch-badge">Optional</span>
        </div>

        <div id="initial-batches-wrapper" data-default-received="{{ now()->format('Y-m-d') }}">
            @foreach ($initialBatchRows as $index => $row)
                <div class="initial-batch-row">
                    <div class="premium-form-field">
                        <label>Menge</label>
                        <input name="initial_batches[{{ $index }}][quantity]" data-name="quantity" type="number" step="0.01" min="0" class="premium-input initial-batch-trigger" value="{{ $row['quantity'] ?? '' }}" placeholder="z. B. 1000">
                    </div>

                    <div class="premium-form-field">
                        <label>Batchnummer optional</label>
                        <input name="initial_batches[{{ $index }}][batch_number]" data-name="batch_number" class="premium-input initial-batch-trigger" value="{{ $row['batch_number'] ?? '' }}" placeholder="wird sonst automatisch erzeugt">
                    </div>

                    <div class="premium-form-field">
                        <label>Wareneingangsdatum</label>
                        <input name="initial_batches[{{ $index }}][received_at]" data-name="received_at" type="date" class="premium-input" value="{{ $row['received_at'] ?? now()->format('Y-m-d') }}">
                    </div>

                    <div class="premium-form-field">
                        <label>Ablaufdatum optional</label>
                        <input name="initial_batches[{{ $index }}][expires_at]" data-name="expires_at" type="date" class="premium-input initial-batch-trigger" value="{{ $row['expires_at'] ?? '' }}">
                    </div>

                    <button type="button" class="premium-icon-btn premium-danger initial-batch-remove" title="Charge entfernen">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            @endforeach
        </div>

        @error('initial_batches') <div class="premium-error">{{ $message }}</div> @enderror
        @error('initial_batches.*.quantity') <div class="premium-error">{{ $message }}</div> @enderror
        @error('initial_batches.*.batch_number') <div class="premium-error">{{ $message }}</div> @enderror
        @error('initial_batches.*.received_at') <div class="premium-error">{{ $message }}</div> @enderror
        @error('initial_batches.*.expires_at') <div class="premium-error">{{ $message }}</div> @enderror

        <template id="initial-batch-template">
            <div class="initial-batch-row">
                <div class="premium-form-field">
                    <label>Menge</label>
                    <input data-name="quantity" type="number" step="0.01" min="0" class="premium-input initial-batch-trigger" placeholder="z. B. 1000">
                </div>

                <div class="premium-form-field">
                    <label>Batchnummer optional</label>
                    <input data-name="batch_number" class="premium-input initial-batch-trigger" placeholder="wird sonst automatisch erzeugt">
                </div>

                <div class="premium-form-field">
                    <label>Wareneingangsdatum</label>
                    <input data-name="received_at" type="date" class="premium-input">
                </div>

                <div class="premium-form-field">
                    <label>Ablaufdatum optional</label>
                    <input data-name="expires_at" type="date" class="premium-input initial-batch-trigger">
                </div>

                <button type="button" class="premium-icon-btn premium-danger initial-batch-remove" title="Charge entfernen">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </template>
    </div>

    <style>
        .initial-batch-card {
            border: 1px solid #e4d7bf;
            border-radius: 18px;
            padding: 16px;
            background: #fffdf8;
        }

        .initial-batch-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .initial-batch-head h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 950;
            color: #111111;
        }

        .initial-batch-head p {
            margin: 5px 0 0;
            color: #7a7064;
            font-weight: 700;
        }

        .initial-batch-badge {
            border-radius: 999px;
            padding: 6px 10px;
            background: #fff7dc;
            border: 1px solid #d4af37;
            font-size: 12px;
            font-weight: 900;
            color: #111111;
            white-space: nowrap;
        }

        .initial-batch-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
            padding: 12px;
            border: 1px solid #eadfcd;
            border-radius: 16px;
            background: #ffffff;
            margin-top: 10px;
        }

        .initial-batch-row:first-child {
            margin-top: 0;
        }

        @media (max-width: 1100px) {
            .initial-batch-row {
                grid-template-columns: repeat(2, minmax(220px, 1fr));
            }
        }

        @media (max-width: 700px) {
            .initial-batch-head,
            .initial-batch-row {
                display: grid;
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script src="{{ asset('js/product-initial-batches-runtime.js') }}" defer></script>
@endif
{{-- INITIAL_BATCH_ON_CREATE_END --}}

{{-- CATEGORY_VISIBLE_BLOCK_START --}}
@php
    $categoryOptionsForProductForm = \App\Models\ProductCategory::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $selectedCategoryIds = collect(old(
        'category_ids',
        isset($product) && $product->exists
            ? $product->categories()->pluck('product_categories.id')->all()
            : []
    ))->map(fn ($id) => (string) $id)->all();
@endphp

<div class="premium-form-field full">
    <label>Kategorien</label>

    <div class="category-checkbox-grid">
        @forelse ($categoryOptionsForProductForm as $category)
            <label class="category-checkbox-card">
                <input
                    type="checkbox"
                    name="category_ids[]"
                    value="{{ $category->id }}"
                    @checked(in_array((string) $category->id, $selectedCategoryIds, true))
                >

                <span class="category-checkbox-dot" style="background: {{ $category->color ?: '#d4af37' }};"></span>

                <span class="category-checkbox-text">
                    {{ $category->name }}
                </span>

                <span class="category-checkbox-check">
                    <i class="bi bi-check2"></i>
                </span>
            </label>
        @empty
            <div class="premium-muted">
                Noch keine Kategorien vorhanden.
                <a href="{{ route('product-categories.create') }}">Kategorie erstellen</a>
            </div>
        @endforelse
    </div>

    <div class="premium-muted" style="margin-top:8px;">
        Du kannst mehrere Kategorien einfach anklicken. Keine Strg-Taste nötig.
    </div>

    @error('category_ids') <div class="premium-error">{{ $message }}</div> @enderror
    @error('category_ids.*') <div class="premium-error">{{ $message }}</div> @enderror
</div>

<style>
    .category-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 10px;
        margin-top: 8px;
    }

    .category-checkbox-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 48px;
        padding: 12px 44px 12px 14px;
        border: 1px solid #ded6c8;
        border-radius: 16px;
        background: #ffffff;
        cursor: pointer;
        font-weight: 800;
        transition: border-color .18s ease, box-shadow .18s ease, transform .12s ease, background .18s ease;
    }

    .category-checkbox-card:hover {
        border-color: #d4af37;
        box-shadow: 0 0 0 3px rgba(212, 175, 55, .15);
        transform: translateY(-1px);
    }

    .category-checkbox-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .category-checkbox-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex: 0 0 auto;
        box-shadow: 0 0 0 3px rgba(0,0,0,.04);
    }

    .category-checkbox-text {
        color: #111;
        line-height: 1.25;
    }

    .category-checkbox-check {
        position: absolute;
        right: 12px;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f4efe5;
        color: transparent;
        border: 1px solid #ded6c8;
    }

    .category-checkbox-card:has(input:checked) {
        background: #fff7dc;
        border-color: #d4af37;
        box-shadow: 0 0 0 3px rgba(212, 175, 55, .18);
    }

    .category-checkbox-card:has(input:checked) .category-checkbox-check {
        background: #d4af37;
        border-color: #d4af37;
        color: #111;
    }
</style>
{{-- CATEGORY_VISIBLE_BLOCK_END --}}

<div class="premium-form-field full">
        <label for="description">Beschreibung optional</label>
        <textarea id="description" name="description" rows="5" class="premium-textarea">{{ old('description', $product->description) }}</textarea>
        @error('description') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>
<div class="product-form-actions">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('products.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>


<!-- PRODUCT_EDITOR_UI_REFRESH_START -->
<style>
    .product-editor-card {
        padding: 0 !important;
        overflow: hidden;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .product-editor-form {
        display: grid;
        gap: 22px;
    }

    .product-editor-form > .premium-alert {
        margin: 0;
    }

    .product-editor-form > .premium-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .product-editor-form .premium-form-field {
        min-width: 0;
    }

    .product-editor-form > .premium-form-grid > .premium-form-field:not(.full) {
        padding: 22px;
        border: 1px solid #d8cbb7;
        border-radius: 20px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 14px 34px rgba(42, 36, 25, .07);
    }

    .product-editor-form > .premium-form-grid > .premium-form-field.full {
        grid-column: 1 / -1;
        padding: 24px;
        border: 1px solid #d8cbb7;
        border-radius: 22px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 16px 38px rgba(42, 36, 25, .08);
    }

    .product-editor-form label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        color: #111111;
        font-size: 14px;
        font-weight: 900;
    }

    .product-editor-form .premium-input,
    .product-editor-form .premium-select,
    .product-editor-form .premium-textarea {
        width: 100%;
        min-height: 52px;
        border: 1px solid #c9b895 !important;
        border-radius: 14px !important;
        background: #fffdf8 !important;
        color: #111111 !important;
        font-size: 16px;
        font-weight: 750;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .65);
    }

    .product-editor-form .premium-input:focus,
    .product-editor-form .premium-select:focus,
    .product-editor-form .premium-textarea:focus {
        border-color: #d4aa20 !important;
        box-shadow: 0 0 0 4px rgba(212, 170, 32, .18) !important;
        outline: none !important;
    }

    .product-editor-form .premium-textarea {
        min-height: 132px;
        resize: vertical;
    }

    .product-editor-form .premium-error {
        margin-top: 7px;
        color: #991b1b;
        font-size: 13px;
        font-weight: 850;
    }

    .product-editor-form .premium-muted {
        color: #665f54 !important;
        font-size: 14px !important;
        font-weight: 700 !important;
    }

    .initial-batch-card,
    .product-editor-form .initial-batch-card {
        border-radius: 22px !important;
        border-color: #d8cbb7 !important;
        background: rgba(255, 255, 255, .88) !important;
        box-shadow: 0 16px 38px rgba(42, 36, 25, .08);
    }

    .initial-batch-head {
        padding-bottom: 14px;
        border-bottom: 1px solid #e7dece;
    }

    .initial-batch-head h3 {
        font-size: 22px !important;
        letter-spacing: -.035em;
    }

    .initial-batch-row {
        border-color: #e2d6c5 !important;
        border-radius: 18px !important;
        background: #fffdf8 !important;
    }

    .category-checkbox-grid {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important;
        gap: 12px !important;
    }

    .category-checkbox-card {
        min-height: 56px !important;
        border-color: #c9b895 !important;
        border-radius: 18px !important;
        background: #fffdf8 !important;
    }

    .category-checkbox-card:hover {
        border-color: #d4aa20 !important;
        box-shadow: 0 0 0 4px rgba(212, 170, 32, .15) !important;
    }

    .category-checkbox-card:has(input:checked) {
        background: #fff7dc !important;
        border-color: #d4aa20 !important;
        box-shadow: 0 0 0 4px rgba(212, 170, 32, .16) !important;
    }

    .product-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 4;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        flex-wrap: wrap;
        margin-top: 4px;
        padding: 18px 0 0;
        background: linear-gradient(180deg, rgba(246, 241, 231, 0), #f6f1e7 36%);
    }

    .product-form-actions .premium-btn {
        min-width: 150px;
    }

    @media (max-width: 1100px) {
        .product-editor-form > .premium-form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .product-editor-form > .premium-form-grid > .premium-form-field:not(.full),
        .product-editor-form > .premium-form-grid > .premium-form-field.full {
            padding: 18px;
        }

        .product-form-actions {
            position: static;
            display: grid;
        }

        .product-form-actions .premium-btn {
            width: 100%;
        }
    }
</style>
<!-- PRODUCT_EDITOR_UI_REFRESH_END -->

