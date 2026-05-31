<x-layouts.premium title="Filialausgang buchen" subtitle="Ware für lokales Geschäft / Filiale abziehen. Mindestbestand darf hier genutzt werden.">
    <section class="premium-card">
        <form method="POST" action="{{ route('branch-withdrawals.store') }}">
            @csrf

            <div class="premium-form-grid">
                <div class="premium-form-field">
                    <label for="product_id">Produkt *</label>
                    <select id="product_id" name="product_id" class="premium-select" data-search="true" required>
                        <option value="">Produkt auswählen</option>
                        @foreach ($products as $product)
                            <option
                                value="{{ $product->id }}"
                                @selected((string) old('product_id', request('product_id')) === (string) $product->id)
                            >
                                {{ $product->name }}
                                @if (!empty($product->serial_number))
                                    — {{ $product->serial_number }}
                                @endif
                                | {{ number_format((float) $product->available_stock, 2, ',', '.') }} verfügbar
                                | Mindestbestand {{ number_format((float) $product->minimum_stock, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="quantity">Menge *</label>
                    <input
                        id="quantity"
                        name="quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="premium-input"
                        value="{{ old('quantity') }}"
                        required
                        placeholder="z. B. 50"
                    >
                    @error('quantity') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="branch_name">Filiale / Zweck</label>
                    <input
                        id="branch_name"
                        name="branch_name"
                        class="premium-input"
                        value="{{ old('branch_name', 'Lokales Geschäft / Filiale') }}"
                    >
                    @error('branch_name') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="note">Notiz / Grund *</label>
                    <textarea
                        id="note"
                        name="note"
                        rows="5"
                        class="premium-textarea"
                        required
                        placeholder="Pflicht: z. B. Ware für lokales Geschäft entnommen, Kunde vor Ort, Regalbestand aufgefüllt..."
                    >{{ old('note') }}</textarea>
                    @error('note') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="premium-alert warning" style="margin-top:18px;">
                <strong>Hinweis:</strong>
                Diese Funktion darf den Mindestbestand unterschreiten. Für normale Angebote bleibt der Mindestbestand weiterhin geschützt.
            </div>

            <div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                <button type="submit" class="premium-btn gold">
                    <i class="bi bi-check2-circle"></i>
                    Filialausgang buchen
                </button>

                <a href="{{ route('branch-withdrawals.index') }}" class="premium-btn">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>
            </div>
        </form>
    </section>
</x-layouts.premium>
