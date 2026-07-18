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

<div class="offer-items-header">
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

<div id="offer-items" class="offer-items-list">
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

<div class="offer-form-actions">
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


<!-- OFFER_EDITOR_UI_REFRESH_START -->
<style>
    .offer-editor-card {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .offer-editor-form {
        display: grid;
        gap: 22px;
    }

    .offer-editor-form > .premium-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
        padding: 26px;
        border: 1px solid #d8cbb7;
        border-radius: 22px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
    }

    .offer-editor-form > .premium-form-grid > .premium-form-field.full {
        grid-column: 1 / -1;
    }

    .offer-editor-form label,
    .offer-shipping-modern-card label {
        display: inline-flex;
        align-items: center;
        margin-bottom: 9px;
        color: #111111;
        font-size: 14px;
        font-weight: 900;
    }

    .offer-editor-form .premium-input,
    .offer-editor-form .premium-select,
    .offer-editor-form .premium-textarea,
    .offer-shipping-modern-card .premium-input,
    .offer-shipping-modern-card .premium-select {
        width: 100%;
        min-height: 52px;
        border: 1px solid #c9b895 !important;
        border-radius: 14px !important;
        background: #fffdf8 !important;
        color: #111111 !important;
        font-size: 16px;
        font-weight: 750;
    }

    .offer-editor-form .premium-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .offer-items-header {
        margin-top: 0;
        padding: 24px 26px 18px;
        border: 1px solid #d8cbb7;
        border-bottom: 0;
        border-radius: 22px 22px 0 0;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
    }

    .offer-items-header h2 {
        margin: 0 0 7px !important;
        color: #111111;
        font-size: 25px !important;
        font-weight: 950 !important;
        letter-spacing: -.035em;
    }

    .offer-items-header p {
        color: #665f54 !important;
        font-size: 14px;
        font-weight: 700;
    }

    .offer-items-list {
        display: grid;
        gap: 0;
        border: 1px solid #d8cbb7;
        border-top: 0;
        border-radius: 0 0 22px 22px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
        overflow: hidden;
    }

    .offer-item-row,
    .offer-items-list .offer-item-row {
        margin: 0 !important;
        padding: 18px 26px !important;
        border: 0 !important;
        border-radius: 0 !important;
        border-top: 1px solid #e7dece !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .offer-item-row:first-child {
        border-top-color: #8d8069 !important;
    }

    .offer-item-row > .premium-form-grid {
        grid-template-columns: minmax(220px, .75fr) minmax(320px, 1.45fr) minmax(150px, .55fr) auto !important;
        gap: 14px !important;
        align-items: end !important;
    }

    .offer-category-filter-field {
        min-width: 0 !important;
    }

    .offer-shipping-modern-card,
    #offer-shipping-card.offer-shipping-modern-card {
        margin: 22px 0 !important;
        padding: 26px !important;
        border: 1px solid #d8cbb7 !important;
        border-radius: 22px !important;
        background: rgba(255, 255, 255, .88) !important;
        box-shadow: 0 18px 45px rgba(42, 36, 25, .08) !important;
    }

    #offer-shipping-card h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 18px !important;
        color: #111111;
        font-size: 24px !important;
        font-weight: 950 !important;
        letter-spacing: -.035em;
    }

    #offer-shipping-card h3::before {
        content: "\F5EA";
        font-family: "bootstrap-icons";
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #f3e8be;
        color: #111111;
        font-size: 19px;
        font-weight: 400;
    }

    #offer-shipping-card .premium-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    #offer-shipping-card .premium-form-field.full {
        grid-column: auto;
    }

    .offer-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 0;
    }

    .offer-form-actions .premium-btn {
        min-width: 190px;
    }

    .offer-editor-form .premium-error,
    .offer-shipping-modern-card .premium-error {
        margin-top: 7px;
        color: #991b1b;
        font-size: 13px;
        font-weight: 850;
    }

    .offer-editor-form .premium-muted,
    .offer-shipping-modern-card .premium-muted {
        color: #665f54 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    @media (max-width: 1150px) {
        .offer-editor-form > .premium-form-grid,
        #offer-shipping-card .premium-form-grid {
            grid-template-columns: 1fr;
        }

        #offer-shipping-card .premium-form-field.full {
            grid-column: 1 / -1;
        }

        .offer-item-row > .premium-form-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }

    @media (max-width: 700px) {
        .offer-editor-form > .premium-form-grid,
        .offer-items-header,
        .offer-item-row,
        .offer-shipping-modern-card,
        #offer-shipping-card.offer-shipping-modern-card {
            padding: 20px !important;
        }

        .offer-item-row > .premium-form-grid {
            grid-template-columns: 1fr !important;
        }

        .offer-form-actions {
            display: grid;
        }

        .offer-form-actions .premium-btn {
            width: 100%;
        }
    }
</style>
<!-- OFFER_EDITOR_UI_REFRESH_END -->



<!-- OFFER_SHIPPING_LAYOUT_FIX_START -->
<style>
    #offer-shipping-card.offer-shipping-modern-card {
        padding: 26px 28px !important;
    }

    #offer-shipping-card .premium-form-grid {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
        gap: 22px !important;
        align-items: start !important;
    }

    #offer-shipping-card .premium-form-field,
    #offer-shipping-card .premium-form-field.full,
    #shipping_price_gross_field,
    .offer-shipping-method-field,
    .offer-shipping-price-field {
        grid-column: auto !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }

    #offer-shipping-card label {
        display: inline-flex !important;
        margin: 0 0 9px !important;
        color: #111111 !important;
        font-size: 14px !important;
        font-weight: 900 !important;
    }

    #offer-shipping-card .premium-select,
    #offer-shipping-card .premium-input,
    #shipping_price_gross {
        width: 100% !important;
        max-width: none !important;
        min-height: 52px !important;
        border: 1px solid #c9b895 !important;
        border-radius: 14px !important;
        background-color: #fffdf8 !important;
        color: #111111 !important;
        font-size: 16px !important;
        font-weight: 800 !important;
    }

    #shipping_price_gross_field .premium-muted {
        margin-top: 8px !important;
        max-width: none !important;
        color: #665f54 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.35 !important;
    }

    @media (max-width: 900px) {
        #offer-shipping-card .premium-form-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
<!-- OFFER_SHIPPING_LAYOUT_FIX_END -->

