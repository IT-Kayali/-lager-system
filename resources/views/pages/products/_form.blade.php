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

<!-- PRODUCT_EDITOR_UI_REFRESH_END -->