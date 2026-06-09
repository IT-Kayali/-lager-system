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
<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('products.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>
