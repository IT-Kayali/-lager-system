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
        Sobald du ein Produkt und eine Menge einträgst, erscheint automatisch die nächste Position.
    </p>
</div>

@php
    $oldItems = old('items');
    $itemsForForm = $oldItems ? collect($oldItems) : collect($formItems);
    if ($itemsForForm->isEmpty()) {
        $itemsForForm = collect([['product_id' => '', 'quantity' => '']]);
    }

    $unitLabels = [
        'gram' => 'g',
        'liter' => 'L',
        'piece' => 'Stk.',
    ];
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
                                {{ $product->name }}
                                | {{ number_format($product->available_stock, 2, ',', '.') }} {{ $unitLabels[$product->unit] ?? $product->unit }} verfügbar
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-form-field">
                    <label>Menge/Gewicht</label>
                    <input
                        name="items[{{ $index }}][quantity]"
                        type="number"
                        step="0.01"
                        min="0.01"
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
        </div>
    @endforeach
</div>

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
                            {{ $product->name }}
                            | {{ number_format($product->available_stock, 2, ',', '.') }} {{ $unitLabels[$product->unit] ?? $product->unit }} verfügbar
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="premium-form-field">
                <label>Menge/Gewicht</label>
                <input data-name="quantity" type="number" step="0.01" min="0.01" max="5000" class="premium-input" placeholder="z. B. 50">
            </div>

            <div class="premium-form-field" style="display:flex; align-items:end;">
                <button type="button" class="premium-icon-btn premium-danger remove-offer-item" title="Position entfernen">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('offer-items');
        const template = document.getElementById('offer-item-template');

        function getRows() {
            return Array.from(wrapper.querySelectorAll('.offer-item-row'));
        }

        function rowHasData(row) {
            const product = row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]');
            const quantity = row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]');

            return Boolean(product && product.value) || Boolean(quantity && quantity.value);
        }

        function rowIsComplete(row) {
            const product = row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]');
            const quantity = row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]');

            return Boolean(product && product.value) && Boolean(quantity && quantity.value);
        }

        function reindexRows() {
            getRows().forEach((row, index) => {
                row.querySelectorAll('[data-name], select[name], input[name]').forEach((field) => {
                    const key = field.dataset.name || field.name.match(/\[(product_id|quantity)\]/)?.[1];

                    if (key) {
                        field.name = `items[${index}][${key}]`;
                    }
                });
            });
        }

        function addEmptyRow() {
            const clone = template.content.cloneNode(true);
            wrapper.appendChild(clone);
            reindexRows();
            bindRowEvents();
        }

        function ensureTrailingEmptyRow() {
            const rows = getRows();
            const lastRow = rows[rows.length - 1];

            if (!lastRow || rowIsComplete(lastRow)) {
                addEmptyRow();
            }
        }

        function removeExtraEmptyRows() {
            const rows = getRows();

            rows.forEach((row, index) => {
                const isLast = index === rows.length - 1;

                if (!isLast && !rowHasData(row) && rows.length > 1) {
                    row.remove();
                }
            });

            reindexRows();
        }

        function bindRowEvents() {
            getRows().forEach((row) => {
                const product = row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]');
                const quantity = row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]');
                const removeButton = row.querySelector('.remove-offer-item');

                [product, quantity].forEach((field) => {
                    if (!field || field.dataset.autoBound === '1') {
                        return;
                    }

                    field.dataset.autoBound = '1';

                    field.addEventListener('change', function () {
                        ensureTrailingEmptyRow();
                        removeExtraEmptyRows();
                    });

                    field.addEventListener('input', function () {
                        ensureTrailingEmptyRow();
                        removeExtraEmptyRows();
                    });
                });

                if (removeButton && removeButton.dataset.autoBound !== '1') {
                    removeButton.dataset.autoBound = '1';

                    removeButton.addEventListener('click', function () {
                        const rows = getRows();

                        if (rows.length > 1) {
                            row.remove();
                            reindexRows();
                            ensureTrailingEmptyRow();
                            removeExtraEmptyRows();
                        }
                    });
                }
            });
        }

        bindRowEvents();
        reindexRows();
        ensureTrailingEmptyRow();
        removeExtraEmptyRows();
    });
</script>
