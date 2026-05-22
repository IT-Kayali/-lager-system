@csrf

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="customer_id">Kunde *</label>
        <select id="customer_id" name="customer_id" class="premium-select" required>
            <option value="">Kunde auswählen</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) old('customer_id', $offer->customer_id) === (string) $customer->id)>
                    {{ $customer->customer_number }} — {{ $customer->company_name }} — {{ $customer->group?->name }}
                </option>
            @endforeach
        </select>
        @error('customer_id') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="template_type">PDF-Vorlage *</label>
        <select id="template_type" name="template_type" class="premium-select" required>
            @foreach ($templates as $value => $label)
                <option value="{{ $value }}" @selected(old('template_type', $offer->template_type ?: 'with_company') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('template_type') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label for="notes">Notizen optional</label>
        <textarea id="notes" name="notes" rows="3" class="premium-textarea">{{ old('notes', $offer->notes) }}</textarea>
        @error('notes') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div style="margin-top:24px;">
    <h2 style="font-size:20px; font-weight:900; margin:0 0 12px;">Produktpositionen</h2>
    <p class="premium-muted" style="margin-top:0;">
        Starte mit einer Position. Weitere Produkte kannst du über das Plus hinzufügen.
    </p>
</div>

@php
    $oldItems = old('items');
    $itemsForForm = $oldItems ? collect($oldItems) : collect($formItems);
    if ($itemsForForm->isEmpty()) {
        $itemsForForm = collect([['product_id' => '', 'quantity' => '']]);
    }
@endphp

<div id="offer-items" style="display:grid; gap:12px;">
    @foreach ($itemsForForm as $index => $item)
        <div class="premium-card offer-item-row" style="padding:14px; box-shadow:none;">
            <div class="premium-form-grid" style="grid-template-columns: 1.8fr .8fr auto;">
                <div class="premium-form-field">
                    <label>Produkt</label>
                    <select name="items[{{ $index }}][product_id]" class="premium-select">
                        <option value="">Produkt auswählen</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->id)>
                                {{ $product->product_code }} — {{ $product->name }}
                                | Verfügbar: {{ number_format($product->available_stock, 3, ',', '.') }}
                                | Max: {{ number_format($product->max_reservable, 3, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-form-field">
                    <label>Menge/Gewicht</label>
                    <input
                        name="items[{{ $index }}][quantity]"
                        type="number"
                        step="0.001"
                        min="0.001"
                        max="5000"
                        class="premium-input"
                        value="{{ $item['quantity'] ?? '' }}"
                        placeholder="z. B. 50"
                    >
                </div>

                <div class="premium-form-field" style="display:flex; align-items:end;">
                    <button type="button" class="premium-icon-btn premium-danger remove-offer-item" title="Position entfernen">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <div class="premium-muted" style="margin-top:8px;">
                50–90g = 50g, 91–239g = 100g, 240–460g = 250g, 461–750g = 500g, 751–5000g = 1000g
            </div>
        </div>
    @endforeach
</div>

<button type="button" id="add-offer-item" class="premium-btn" style="margin-top:14px;">
    <i class="bi bi-plus-lg"></i>
    Produkt hinzufügen
</button>

<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        {{ $submitLabel }}
    </button>

    <a href="{{ route('offers.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>

<template id="offer-item-template">
    <div class="premium-card offer-item-row" style="padding:14px; box-shadow:none;">
        <div class="premium-form-grid" style="grid-template-columns: 1.8fr .8fr auto;">
            <div class="premium-form-field">
                <label>Produkt</label>
                <select data-name="product_id" class="premium-select">
                    <option value="">Produkt auswählen</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->product_code }} — {{ $product->name }}
                            | Verfügbar: {{ number_format($product->available_stock, 3, ',', '.') }}
                            | Max: {{ number_format($product->max_reservable, 3, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="premium-form-field">
                <label>Menge/Gewicht</label>
                <input data-name="quantity" type="number" step="0.001" min="0.001" max="5000" class="premium-input" placeholder="z. B. 50">
            </div>

            <div class="premium-form-field" style="display:flex; align-items:end;">
                <button type="button" class="premium-icon-btn premium-danger remove-offer-item" title="Position entfernen">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <div class="premium-muted" style="margin-top:8px;">
            50–90g = 50g, 91–239g = 100g, 240–460g = 250g, 461–750g = 500g, 751–5000g = 1000g
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('offer-items');
        const addButton = document.getElementById('add-offer-item');
        const template = document.getElementById('offer-item-template');

        function reindexRows() {
            wrapper.querySelectorAll('.offer-item-row').forEach((row, index) => {
                row.querySelectorAll('[data-name], select[name], input[name]').forEach((field) => {
                    const key = field.dataset.name || field.name.match(/\[(product_id|quantity)\]/)?.[1];

                    if (key) {
                        field.name = `items[${index}][${key}]`;
                    }
                });
            });
        }

        function bindRemoveButtons() {
            wrapper.querySelectorAll('.remove-offer-item').forEach((button) => {
                button.onclick = function () {
                    if (wrapper.querySelectorAll('.offer-item-row').length > 1) {
                        button.closest('.offer-item-row').remove();
                        reindexRows();
                    }
                };
            });
        }

        addButton.addEventListener('click', function () {
            const clone = template.content.cloneNode(true);
            wrapper.appendChild(clone);
            reindexRows();
            bindRemoveButtons();
        });

        bindRemoveButtons();
        reindexRows();
    });
</script>
