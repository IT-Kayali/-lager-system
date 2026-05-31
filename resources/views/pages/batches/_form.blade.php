@csrf

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="product_id">Produkt *</label>
        <select id="product_id" name="product_id" class="premium-select" required>
            <option value="">Produkt auswählen</option>
            @foreach (($products ?? collect()) as $product)
                <option value="{{ $product->id }}" @selected((string) old('product_id', $batch->product_id) === (string) $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_id') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="batch_number">Batchnummer</label>
        <input id="batch_number" name="batch_number" class="premium-input" value="{{ old('batch_number', $batch->batch_number) }}">
        @error('batch_number') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="quantity">Menge *</label>
        <input id="quantity" name="quantity" type="number" step="0.01" min="0" class="premium-input" value="{{ old('quantity', $batch->quantity) }}" required>
        @error('quantity') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="received_at">Wareneingangsdatum *</label>
        <input id="received_at" name="received_at" type="date" class="premium-input" value="{{ old('received_at', optional($batch->received_at)->format('Y-m-d')) }}" required>
        @error('received_at') <div class="premium-error">{{ $message }}</div> @enderror
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
