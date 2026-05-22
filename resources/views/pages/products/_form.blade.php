@csrf

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
            @foreach ($units as $value => $label)
                <option value="{{ $value }}" @selected(old('unit', $product->unit ?: 'gram') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('unit') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="supplier">Lieferant</label>
        <input id="supplier" name="supplier" class="premium-input" value="{{ old('supplier', $product->supplier) }}">
        @error('supplier') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="storage_location">Lagerort</label>
        <input id="storage_location" name="storage_location" class="premium-input" value="{{ old('storage_location', $product->storage_location) }}">
        @error('storage_location') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="minimum_stock">Mindestbestand *</label>
        <input id="minimum_stock" name="minimum_stock" type="number" step="0.001" min="0" class="premium-input" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required>
        @error('minimum_stock') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="image">Produktbild optional</label>
        <input id="image" name="image" type="file" accept="image/*" class="premium-input">
        @error('image') <div class="premium-error">{{ $message }}</div> @enderror
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
