@csrf

@if (($suppliers ?? collect())->isEmpty())
    <div class="premium-alert" style="border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.10); color:#92400e;">
        Noch keine Lieferanten vorhanden. Du kannst das Produkt trotzdem speichern, aber empfohlen ist zuerst einen Lieferanten anzulegen.
        <a href="{{ route('suppliers.create') }}" style="font-weight:900; color:#92400e;">Lieferant hinzufügen</a>
    </div>
@endif

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="name">Produktname *</label>
        <input id="name" name="name" class="premium-input" value="{{ old('name', $product->name) }}" required>
        @error('name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="manufacturer">Hersteller</label>
        <input id="manufacturer" name="manufacturer" class="premium-input" value="{{ old('manufacturer', $product->manufacturer) }}">
        @error('manufacturer') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="serial_number">Seriennummer</label>
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
        <input id="minimum_stock" name="minimum_stock" type="number" step="0.001" min="0" class="premium-input" value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
        @error('minimum_stock') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

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
