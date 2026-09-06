@csrf

<input
    type="hidden"
    name="carton_count"
    id="carton_count_real"
    value="{{ old('carton_count', $offer->carton_count ?? '') }}"
>
@error('carton_count')
    <div class="premium-error" data-carton-count-error hidden>{{ $message }}</div>
@enderror

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="customer_id">Kunde *</label>
        <select id="customer_id" name="customer_id" class="premium-select" required>
            <option value="">Kunde auswählen</option>
            @foreach ($customers as $customer)
                <option
                    value="{{ $customer->id }}"
                    data-customer-note="{{ $customer->notes }}"
                    @selected((string) old('customer_id', $offer->customer_id) === (string) $customer->id)
                >
                    {{ $customer->customer_number }} — {{ $customer->company_name }} — {{ $customer->group?->name }}
                </option>
            @endforeach
        </select>
        @error('customer_id') <div class="premium-error">{{ $message }}</div> @enderror

        <div id="offer-customer-note" class="offer-customer-note" hidden>
            <div class="offer-customer-note-title">
                <i class="bi bi-exclamation-circle"></i>
                Kundenhinweis
            </div>
            <div class="offer-customer-note-text"></div>
        </div>
    </div>

    <div class="premium-form-field">
        <label for="template_type">PDF-Vorlage *</label>
        @php
            $selectedTemplateType = old('template_type', $offer->template_type ?? '');
        @endphp
        <select id="template_type" name="template_type" class="premium-select" required>
            <option value="">PDF-Vorlage auswählen</option>
            @foreach ($templates as $value => $label)
                <option value="{{ $value }}" @selected((string) $selectedTemplateType === (string) $value)>
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
        $itemsForForm = collect([['product_id' => '', 'quantity' => '', 'line_total' => '']]);
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
            <div class="premium-form-grid" style="grid-template-columns: 1.8fr .8fr .8fr auto;">
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

                <div class="premium-form-field offer-line-total-field">
                    <div class="offer-line-total-label-row">
                        <label>Gesamtpreis</label>
                        <span class="offer-auto-price-badge">
                            Auto: <strong class="offer-auto-price-value">—</strong>
                        </span>
                    </div>
                    <div class="offer-line-total-control-row">
                        <div class="offer-line-total-input-wrap">
                            <input
                                name="items[{{ $index }}][line_total]"
                                type="number"
                                step="0.01"
                                min="0"
                                class="premium-input offer-line-total-input"
                                value="{{ $item['line_total'] ?? '' }}"
                                placeholder="0,00"
                            >
                            <span class="offer-line-total-currency">€</span>
                        </div>
                        <button type="button" class="premium-icon-btn premium-danger remove-offer-item offer-line-delete-btn" title="Position entfernen">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="premium-muted offer-line-total-hint">Automatisch berechnet · manuell änderbar</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="offer-price-summary">
    <span class="premium-muted">Summe (netto)</span>
    <strong id="offer-products-total">0,00 €</strong>
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
        <div class="premium-form-grid" style="grid-template-columns: 1.8fr .8fr .8fr auto;">
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

            <div class="premium-form-field offer-line-total-field">
                <div class="offer-line-total-label-row">
                    <label>Gesamtpreis</label>
                    <span class="offer-auto-price-badge">
                        Auto: <strong class="offer-auto-price-value">—</strong>
                    </span>
                </div>
                <div class="offer-line-total-control-row">
                    <div class="offer-line-total-input-wrap">
                        <input data-name="line_total" type="number" step="0.01" min="0" class="premium-input offer-line-total-input" placeholder="0,00">
                        <span class="offer-line-total-currency">€</span>
                    </div>
                    <button type="button" class="premium-icon-btn premium-danger remove-offer-item offer-line-delete-btn" title="Position entfernen">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="premium-muted offer-line-total-hint">Automatisch berechnet · manuell änderbar</div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('offer-items');
        const template = document.getElementById('offer-item-template');
        const customer = document.getElementById('customer_id');
        const customerNoteBox = document.getElementById('offer-customer-note');
        const customerNoteText = customerNoteBox?.querySelector('.offer-customer-note-text');
        const totalOutput = document.getElementById('offer-products-total');
        const pricePreviewUrl = @json(route('offers.price-preview'));

        function refreshCustomerNote() {
            if (!customer || !customerNoteBox || !customerNoteText) return;

            const selectedOption = customer.querySelector(`option[value="${CSS.escape(customer.value || '')}"]`);
            const note = (selectedOption?.dataset.customerNote || '').trim();

            customerNoteText.textContent = note;
            customerNoteBox.hidden = note === '';
        }

        function getRows() {
            return Array.from(wrapper.querySelectorAll('.offer-item-row'));
        }

        function getRowFields(row) {
            return {
                product: row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]'),
                quantity: row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]'),
                total: row.querySelector('input[name*="[line_total]"], input[data-name="line_total"]'),
                autoValue: row.querySelector('.offer-auto-price-value'),
                badge: row.querySelector('.offer-auto-price-badge'),
                hint: row.querySelector('.offer-line-total-hint'),
            };
        }

        function rowHasData(row) {
            const { product, quantity } = getRowFields(row);
            return Boolean(product && product.value) || Boolean(quantity && quantity.value);
        }

        function rowIsComplete(row) {
            const { product, quantity } = getRowFields(row);
            return Boolean(product && product.value) && Boolean(quantity && Number(quantity.value) > 0);
        }

        function formatMoney(value) {
            return new Intl.NumberFormat('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(Number(value || 0)) + ' €';
        }

        function updateGrandTotal() {
            const total = getRows().reduce((sum, row) => {
                const field = getRowFields(row).total;
                const value = field && field.value !== '' ? Number(field.value) : 0;
                return sum + (Number.isFinite(value) ? value : 0);
            }, 0);

            if (totalOutput) totalOutput.textContent = formatMoney(total);
        }

        function markInitialState(row) {
            if (row.dataset.priceStateInitialized === '1') return;
            const { product, quantity, total } = getRowFields(row);
            row.dataset.priceStateInitialized = '1';
            row.dataset.initialProduct = product?.value || '';
            row.dataset.initialQuantity = quantity?.value || '';
            row.dataset.initialTotal = total?.value || '';
            row.dataset.keepSavedTotal = total?.value !== '' ? '1' : '0';
            if (total) total.dataset.manualOverride = '0';
        }

        function setWaitingState(row) {
            const { autoValue, badge, hint } = getRowFields(row);
            if (autoValue) autoValue.textContent = '—';

            if (!customer?.value) {
                if (badge) badge.classList.add('is-waiting');
                if (hint) hint.textContent = 'Kunde auswählen, damit der automatische Preis berechnet wird.';
            } else {
                if (badge) badge.classList.remove('is-waiting');
                if (hint) hint.textContent = 'Produkt und Menge auswählen · manuell änderbar';
            }
        }

        async function refreshAutomaticPrice(row, forceOverwrite = false) {
            const { product, quantity, total, autoValue, badge, hint } = getRowFields(row);
            if (!product || !quantity || !total) return;

            markInitialState(row);

            if (!customer?.value || !product.value || !quantity.value || Number(quantity.value) <= 0) {
                if (total.dataset.manualOverride !== '1' && row.dataset.keepSavedTotal !== '1') total.value = '';
                setWaitingState(row);
                updateGrandTotal();
                return;
            }

            const requestId = String(Number(row.dataset.priceRequestId || 0) + 1);
            row.dataset.priceRequestId = requestId;

            try {
                const url = new URL(pricePreviewUrl, window.location.origin);
                url.searchParams.set('customer_id', customer.value);
                url.searchParams.set('product_id', product.value);
                url.searchParams.set('quantity', quantity.value);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Preis konnte nicht berechnet werden.');

                const data = await response.json();
                if (row.dataset.priceRequestId !== requestId) return;

                const automaticTotal = Number(data.line_total || 0);
                if (autoValue) autoValue.textContent = formatMoney(automaticTotal);
                if (badge) badge.classList.remove('is-waiting');

                const savedTotal = row.dataset.keepSavedTotal === '1' && row.dataset.initialTotal !== ''
                    ? Number(row.dataset.initialTotal)
                    : null;

                const shouldKeepSavedTotal = savedTotal !== null
                    && product.value === row.dataset.initialProduct
                    && quantity.value === row.dataset.initialQuantity
                    && !forceOverwrite;

                if (shouldKeepSavedTotal) {
                    total.value = Number(savedTotal).toFixed(2);
                    const differs = Math.abs(Number(savedTotal) - automaticTotal) > 0.004;
                    total.dataset.manualOverride = differs ? '1' : '0';
                    if (hint) {
                        hint.textContent = differs
                            ? 'Manuell angepasst · Automatik bleibt rechts sichtbar'
                            : `${formatMoney(data.unit_price)} je Einheit · ${data.tier_label || 'Preisregel'}`;
                    }
                } else if (total.dataset.manualOverride !== '1' || forceOverwrite) {
                    total.value = automaticTotal.toFixed(2);
                    total.dataset.manualOverride = '0';
                    row.dataset.keepSavedTotal = '0';
                    if (hint) hint.textContent = `${formatMoney(data.unit_price)} je Einheit · ${data.tier_label || 'Preisregel'}`;
                }

                updateGrandTotal();
            } catch (error) {
                if (autoValue) autoValue.textContent = '—';
                if (badge) badge.classList.add('is-waiting');
                if (hint) hint.textContent = 'Automatischer Preis konnte nicht geladen werden. Gesamtpreis kann manuell eingetragen werden.';
            }
        }

        function reindexRows() {
            getRows().forEach((row, index) => {
                row.querySelectorAll('[data-name], select[name], input[name]').forEach((field) => {
                    const key = field.dataset.name || field.name.match(/\[(product_id|quantity|line_total)\]/)?.[1];
                    if (key) field.name = `items[${index}][${key}]`;
                });
            });
        }

        function addEmptyRow() {
            wrapper.appendChild(template.content.cloneNode(true));
            reindexRows();
            bindRowEvents();
        }

        function ensureTrailingEmptyRow() {
            const rows = getRows();
            const lastRow = rows[rows.length - 1];
            if (!lastRow || rowIsComplete(lastRow)) addEmptyRow();
        }

        function removeExtraEmptyRows() {
            const rows = getRows();
            rows.forEach((row, index) => {
                if (index !== rows.length - 1 && !rowHasData(row) && rows.length > 1) row.remove();
            });
            reindexRows();
        }

        function bindRowEvents() {
            getRows().forEach((row) => {
                markInitialState(row);
                const { product, quantity, total, hint } = getRowFields(row);
                const removeButton = row.querySelector('.remove-offer-item');

                [product, quantity].forEach((field) => {
                    if (!field || field.dataset.autoBound === '1') return;
                    field.dataset.autoBound = '1';

                    const changed = function () {
                        row.dataset.keepSavedTotal = '0';
                        if (total) total.dataset.manualOverride = '0';
                        ensureTrailingEmptyRow();
                        removeExtraEmptyRows();
                        refreshAutomaticPrice(row, true);
                    };

                    field.addEventListener('change', changed);
                    field.addEventListener('input', changed);
                });

                if (total && total.dataset.totalBound !== '1') {
                    total.dataset.totalBound = '1';
                    total.addEventListener('input', function () {
                        total.dataset.manualOverride = '1';
                        row.dataset.keepSavedTotal = '0';
                        if (hint) hint.textContent = 'Manuell angepasst · Automatik bleibt rechts sichtbar';
                        updateGrandTotal();
                    });
                }

                if (removeButton && removeButton.dataset.autoBound !== '1') {
                    removeButton.dataset.autoBound = '1';
                    removeButton.addEventListener('click', function () {
                        if (getRows().length > 1) {
                            row.remove();
                            reindexRows();
                            ensureTrailingEmptyRow();
                            removeExtraEmptyRows();
                            updateGrandTotal();
                        }
                    });
                }

                setWaitingState(row);
                if (rowIsComplete(row) && customer?.value) refreshAutomaticPrice(row, false);
            });
        }

        if (customer && customer.dataset.priceBound !== '1') {
            customer.dataset.priceBound = '1';
            customer.addEventListener('change', function () {
                refreshCustomerNote();

                getRows().forEach((row) => {
                    row.dataset.keepSavedTotal = '0';
                    const total = getRowFields(row).total;
                    if (total) total.dataset.manualOverride = '0';
                    refreshAutomaticPrice(row, true);
                });
            });
        }

        refreshCustomerNote();
        bindRowEvents();
        reindexRows();
        ensureTrailingEmptyRow();
        removeExtraEmptyRows();
        updateGrandTotal();
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

    .offer-customer-note {
        margin-top: 10px;
        padding: 12px 14px;
        border: 1px solid #e8c861;
        border-radius: 12px;
        background: #fff8d8;
        color: #5f4900;
    }

    .offer-customer-note-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 5px;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .offer-customer-note-text {
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
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
        grid-template-columns: 560px 220px 300px !important;
        gap: 14px !important;
        align-items: start !important;
        justify-content: start !important;
        width: 100% !important;
    }

    .offer-item-row > .premium-form-grid > .premium-form-field {
        min-width: 0 !important;
        width: 100% !important;
    }

    .offer-item-row .premium-select,
    .offer-item-row .premium-input,
    .offer-item-row .ts-wrapper,
    .offer-item-row .ts-control {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
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

    .offer-line-total-field {
        min-width: 0;
    }

    .offer-line-total-label-row {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        min-height:18px;
        margin-bottom:9px;
    }

    .offer-line-total-label-row label {
        margin-bottom:0 !important;
    }

    .offer-auto-price-badge {
        display:inline-flex;
        align-items:center;
        gap:4px;
        padding:4px 8px;
        border-radius:999px;
        background:#f3e8be;
        color:#66510c;
        font-size:11px;
        font-weight:850;
        white-space:nowrap;
    }

    .offer-auto-price-badge.is-waiting {
        background:#f2eee7;
        color:#7a7368;
    }

    .offer-line-total-control-row {
        display:flex;
        align-items:center;
        gap:8px;
        width:100%;
    }

    .offer-line-total-input-wrap {
        position:relative;
        margin-top:0;
        flex:1 1 auto;
        min-width:0;
    }

    .offer-line-delete-btn {
        flex:0 0 40px;
        width:40px !important;
        height:40px !important;
        margin:0 !important;
        align-self:center !important;
    }

    .offer-line-total-input {
        padding-right:38px !important;
        font-weight:900 !important;
    }

    .offer-line-total-currency {
        position:absolute;
        top:50%;
        right:14px;
        transform:translateY(-50%);
        color:#665f54;
        font-size:14px;
        font-weight:900;
        pointer-events:none;
    }

    .offer-line-total-hint {
        min-height:18px;
        margin-top:5px !important;
        font-size:11px !important;
        line-height:1.25 !important;
    }

    .offer-price-summary {
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:16px;
        min-height:62px;
        padding:14px 20px;
        border:1px solid #d8cbb7;
        border-radius:16px;
        background:#fffdf8;
    }

    .offer-price-summary .premium-muted {
        font-size:13px !important;
        font-weight:850 !important;
        color:#665f54 !important;
    }

    .offer-price-summary strong {
        font-size:24px;
        font-weight:950;
        color:#111;
    }

    .offer-line-total-input[data-manual-override="1"] {
        border-color:#b99119 !important;
        box-shadow:0 0 0 3px rgba(185,145,25,.10);
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

    @media (max-width: 1250px) {
        .offer-editor-form > .premium-form-grid,
        #offer-shipping-card .premium-form-grid {
            grid-template-columns: 1fr;
        }

        #offer-shipping-card .premium-form-field.full {
            grid-column: 1 / -1;
        }

        .offer-item-row > .premium-form-grid {
            grid-template-columns: minmax(0, 1fr) 190px 280px !important;
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

        .offer-line-total-control-row {
            display:grid;
            grid-template-columns:minmax(0, 1fr) 40px;
            gap:8px;
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
