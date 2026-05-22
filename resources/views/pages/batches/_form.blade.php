@csrf

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="product_id">Produkt *</label>
        <select id="product_id" name="product_id" class="premium-select" required>
            <option value="">Produkt auswählen</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected((string) old('product_id', $selectedProductId) === (string) $product->id)>
                    {{ $product->product_code }} — {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_id') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="batch_number">Batchnummer</label>
        <input
            id="batch_number"
            name="batch_number"
            class="premium-input"
            value="{{ old('batch_number', $batch->batch_number) }}"
            placeholder="Leer lassen für automatische Nummer"
        >
        @error('batch_number') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="quantity">Menge *</label>
        <input
            id="quantity"
            name="quantity"
            type="number"
            step="0.001"
            min="0"
            class="premium-input"
            value="{{ old('quantity', $batch->quantity ?? 0) }}"
            required
        >
        @error('quantity') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="storage_location">Lagerort</label>
        <input
            id="storage_location"
            name="storage_location"
            class="premium-input"
            value="{{ old('storage_location', $batch->storage_location) }}"
        >
        @error('storage_location') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="purchase_price">Einkaufspreis optional</label>
        <input
            id="purchase_price"
            name="purchase_price"
            type="number"
            step="0.01"
            min="0"
            class="premium-input"
            value="{{ old('purchase_price', $batch->purchase_price) }}"
        >
        @error('purchase_price') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="received_at">Wareneingangsdatum *</label>
        <input
            id="received_at"
            name="received_at"
            type="date"
            class="premium-input"
            value="{{ old('received_at', optional($batch->received_at)->format('Y-m-d') ?: now()->toDateString()) }}"
            required
        >
        @error('received_at') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="expires_at">Ablaufdatum optional</label>
        <input
            id="expires_at"
            name="expires_at"
            type="date"
            class="premium-input"
            value="{{ old('expires_at', optional($batch->expires_at)->format('Y-m-d')) }}"
        >
        @error('expires_at') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('batches.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>
