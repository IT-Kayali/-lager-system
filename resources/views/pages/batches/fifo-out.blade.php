<x-layouts.premium title="FIFO-Warenausgang" subtitle="Bestand aus den ältesten Chargen zuerst abbuchen.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <form method="POST" action="{{ route('batches.fifo-out.store') }}">
            @csrf

            <div class="premium-form-grid">
                <div class="premium-form-field">
                    <label for="product_id">Produkt *</label>
                    <select id="product_id" name="product_id" class="premium-select" required>
                        <option value="">Produkt auswählen</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                {{ $product->product_code }} — {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="quantity">Menge abbuchen *</label>
                    <input
                        id="quantity"
                        name="quantity"
                        type="number"
                        step="0.001"
                        min="0.001"
                        class="premium-input"
                        value="{{ old('quantity') }}"
                        required
                    >
                    @error('quantity') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="note">Notiz optional</label>
                    <textarea id="note" name="note" rows="4" class="premium-textarea">{{ old('note') }}</textarea>
                    @error('note') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-box-arrow-up"></i>
                    FIFO abbuchen
                </button>

                <a href="{{ route('batches.index') }}" class="premium-btn">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>
            </div>
        </form>
    </section>
</x-layouts.premium>
