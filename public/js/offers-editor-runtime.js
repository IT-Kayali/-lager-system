(() => {
    'use strict';

    function getOfferRuntimeConfig() {
        return document.getElementById(
            'offer-editor-runtime-config'
        );
    }

    function parseOfferRuntimeJson(key, fallback) {
        const raw = getOfferRuntimeConfig()?.dataset[key];

        if (!raw) {
            return fallback;
        }

        try {
            return JSON.parse(raw);
        } catch (error) {
            console.error(
                `Invalid offer runtime JSON: ${key}`,
                error
            );

            return fallback;
        }
    }

    // ==============================================
    // Offer item pricing, rows and customer note
    // ==============================================

    document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('offer-items');
            const template = document.getElementById('offer-item-template');
            const customer = document.getElementById('customer_id');
            const customerNoteBox = document.getElementById('offer-customer-note');
            const customerNoteText = customerNoteBox?.querySelector('.offer-customer-note-text');
            const totalOutput = document.getElementById('offer-products-total');
            const pricePreviewUrl = getOfferRuntimeConfig()?.dataset.pricePreviewUrl || '';

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

    // ==============================================
    // Shipping visibility
    // ==============================================

    document.addEventListener('DOMContentLoaded', function () {
            const method = document.getElementById('shipping_method');
            const priceField = document.getElementById('shipping_price_gross_field');
            const priceInput = document.getElementById('shipping_price_gross');

            function syncShippingPrice() {
                if (!method || !priceField || !priceInput) {
                    return;
                }

                const isDelivery = method.value === 'Lieferung';

                priceField.style.display = isDelivery ? '' : 'none';
                priceInput.disabled = !isDelivery;

                if (!isDelivery) {
                    priceInput.value = '';
                }
            }

            if (method) {
                method.addEventListener('change', syncShippingPrice);
                syncShippingPrice();
            }
        });

    // ==============================================
    // Category/product filtering
    // ==============================================

    document.addEventListener('DOMContentLoaded', function () {
            const offerCategories = parseOfferRuntimeJson(
                'offerCategories',
                []
            );

            const productCategoryMap = parseOfferRuntimeJson(
                'productCategoryMap',
                {}
            );

            function isProductSelect(select) {
                const name = select.getAttribute('name') || '';
                return name.includes('product_id') || name.includes('[product]');
            }

            function getFieldWrapper(element) {
                return element.closest('.premium-form-field')
                    || element.closest('.form-group')
                    || element.parentElement;
            }

            function getPositionRow(productSelect) {
                return productSelect.closest('[data-position-row]')
                    || productSelect.closest('.product-position-row')
                    || productSelect.closest('.offer-position-row')
                    || productSelect.closest('.position-row')
                    || productSelect.closest('.premium-form-grid')
                    || productSelect.closest('.premium-card')
                    || productSelect.parentElement;
            }

            function buildCategoryField() {
                const wrapper = document.createElement('div');
                wrapper.className = 'premium-form-field offer-category-filter-field';

                const label = document.createElement('label');
                label.textContent = 'Kategorie';

                const select = document.createElement('select');
                select.className = 'premium-select offer-category-select';
                select.setAttribute('data-offer-category-select', '1');

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Kategorie auswählen';
                select.appendChild(emptyOption);

                offerCategories.forEach((category) => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    select.appendChild(option);
                });

                wrapper.appendChild(label);
                wrapper.appendChild(select);

                return wrapper;
            }

            function rememberOriginalOptions(productSelect) {
                if (productSelect.dataset.originalOptionsSaved === '1') {
                    return;
                }

                productSelect.dataset.originalOptionsSaved = '1';

                const options = Array.from(productSelect.options).map((option) => ({
                    value: option.value,
                    text: option.textContent,
                    selected: option.selected,
                    disabled: option.disabled,
                }));

                productSelect.dataset.originalOptions = JSON.stringify(options);
            }

            function getOriginalOptions(productSelect) {
                rememberOriginalOptions(productSelect);

                try {
                    return JSON.parse(productSelect.dataset.originalOptions || '[]');
                } catch (e) {
                    return [];
                }
            }

            function setProductDisabled(productSelect, disabled) {
                productSelect.disabled = disabled;

                if (productSelect.tomselect) {
                    if (disabled) {
                        productSelect.tomselect.disable();
                    } else {
                        productSelect.tomselect.enable();
                    }
                }
            }

            function clearTomSelect(productSelect) {
                if (!productSelect.tomselect) {
                    return false;
                }

                productSelect.tomselect.clear(true);
                productSelect.tomselect.clearOptions();

                return true;
            }

            function addTomSelectOption(productSelect, option) {
                if (!productSelect.tomselect) {
                    return;
                }

                productSelect.tomselect.addOption({
                    value: option.value,
                    text: option.text,
                });
            }

            function refreshTomSelect(productSelect) {
                if (!productSelect.tomselect) {
                    return;
                }

                productSelect.tomselect.refreshOptions(false);
                productSelect.tomselect.refreshItems();
            }

            function chooseCategoryForExistingProduct(productSelect, categorySelect) {
                if (categorySelect.value || !productSelect.value) {
                    return;
                }

                const categories = productCategoryMap[String(productSelect.value)] || [];

                if (categories.length > 0) {
                    categorySelect.value = String(categories[0]);
                }
            }

            function filterProducts(productSelect, categorySelect, resetProduct = true) {
                const categoryId = categorySelect.value;
                const currentValue = resetProduct ? '' : productSelect.value;
                const originalOptions = getOriginalOptions(productSelect);

                const hasTomSelect = clearTomSelect(productSelect);

                productSelect.innerHTML = '';

                if (!categoryId) {
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Erst Kategorie auswählen';
                    productSelect.appendChild(placeholder);

                    if (hasTomSelect) {
                        addTomSelectOption(productSelect, {
                            value: '',
                            text: 'Erst Kategorie auswählen',
                        });
                    }

                    productSelect.value = '';
                    refreshTomSelect(productSelect);
                    setProductDisabled(productSelect, true);

                    return;
                }

                originalOptions.forEach((option) => {
                    const isEmpty = option.value === '';
                    const categories = productCategoryMap[String(option.value)] || [];
                    const allowed = isEmpty || categories.includes(String(categoryId));

                    if (!allowed) {
                        return;
                    }

                    const newOption = document.createElement('option');
                    newOption.value = option.value;
                    newOption.textContent = isEmpty ? 'Produkt auswählen' : option.text;
                    newOption.disabled = option.disabled;

                    if (!resetProduct && option.value === currentValue) {
                        newOption.selected = true;
                    }

                    productSelect.appendChild(newOption);

                    if (hasTomSelect) {
                        addTomSelectOption(productSelect, {
                            value: option.value,
                            text: isEmpty ? 'Produkt auswählen' : option.text,
                        });
                    }
                });

                setProductDisabled(productSelect, false);

                if (resetProduct) {
                    productSelect.value = '';

                    if (productSelect.tomselect) {
                        productSelect.tomselect.clear(true);
                    }
                } else {
                    productSelect.value = currentValue;

                    if (productSelect.tomselect && currentValue) {
                        productSelect.tomselect.setValue(currentValue, true);
                    }
                }

                refreshTomSelect(productSelect);
                productSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function enhanceProductSelect(productSelect) {
                if (!productSelect || productSelect.dataset.categoryFilterEnhanced === '1') {
                    return;
                }

                rememberOriginalOptions(productSelect);

                const productField = getFieldWrapper(productSelect);
                if (!productField || !productField.parentElement) {
                    return;
                }

                let categoryField = productField.parentElement.querySelector('.offer-category-filter-field');

                if (!categoryField) {
                    categoryField = buildCategoryField();
                    productField.parentElement.insertBefore(categoryField, productField);
                }

                const categorySelect = categoryField.querySelector('[data-offer-category-select]');

                productSelect.dataset.categoryFilterEnhanced = '1';

                const row = getPositionRow(productSelect);
                if (row) {
                    row.classList.add('offer-category-product-row');
                }

                chooseCategoryForExistingProduct(productSelect, categorySelect);

                categorySelect.addEventListener('change', function () {
                    filterProducts(productSelect, categorySelect, true);
                });

                filterProducts(productSelect, categorySelect, false);
            }

            function enhanceAll() {
                document.querySelectorAll('select').forEach((select) => {
                    if (isProductSelect(select)) {
                        enhanceProductSelect(select);
                    }
                });
            }

            enhanceAll();

            const observer = new MutationObserver(function () {
                enhanceAll();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });
        });

    // ==============================================
    // Shipping card positioning
    // ==============================================

    document.addEventListener('DOMContentLoaded', function () {
            const shippingCard = document.getElementById('offer-shipping-card');

            if (!shippingCard) {
                return;
            }

            const productHeadings = Array.from(document.querySelectorAll('h1,h2,h3,h4,strong,div,span'))
                .filter((el) => (el.textContent || '').trim() === 'Produktpositionen');

            const productHeading = productHeadings[0];

            if (!productHeading) {
                return;
            }

            const mainCard = productHeading.closest('.premium-card');
            const notesTextarea = mainCard ? mainCard.querySelector('textarea') : null;

            if (notesTextarea) {
                const notesField = notesTextarea.closest('.premium-form-field') || notesTextarea.parentElement;

                if (notesField && notesField.parentElement) {
                    notesField.parentElement.insertBefore(shippingCard, notesField.nextSibling);
                    return;
                }
            }

            mainCard.insertBefore(shippingCard, productHeading);
        });

    // ==============================================
    // Shipping hidden field synchronization
    // ==============================================

    document.addEventListener('DOMContentLoaded', function () {
            const methodVisible = document.getElementById('shipping_method');
            const priceVisible = document.getElementById('shipping_price_gross');
            const methodReal = document.getElementById('shipping_method_real');
            const priceReal = document.getElementById('shipping_price_gross_real');

            function syncShippingHiddenFields() {
                if (!methodVisible || !methodReal) {
                    return;
                }

                methodReal.value = methodVisible.value || '';

                if (priceReal) {
                    if (methodVisible.value === 'Lieferung' && priceVisible) {
                        priceReal.value = priceVisible.value || '';
                    } else {
                        priceReal.value = '';
                    }
                }
            }

            if (methodVisible) {
                methodVisible.addEventListener('change', syncShippingHiddenFields);
                methodVisible.addEventListener('input', syncShippingHiddenFields);
            }

            if (priceVisible) {
                priceVisible.addEventListener('change', syncShippingHiddenFields);
                priceVisible.addEventListener('input', syncShippingHiddenFields);
            }

            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', syncShippingHiddenFields);
            });

            syncShippingHiddenFields();
        });

    // ==============================================
    // Create-only required field validation
    // ==============================================

    document.addEventListener('DOMContentLoaded', function () {
            const runtimeConfig = getOfferRuntimeConfig();

            if (runtimeConfig?.dataset.requiredValidation !== '1') {
                return;
            }

            const form = document.getElementById('offer-main-form');

            if (!form) {
                return;
            }

            const getFieldContainer = (field) => field?.closest('.premium-form-field') || field?.parentElement || null;

            function getVisualField(field) {
                if (!field) {
                    return null;
                }

                if (field.tomselect?.wrapper) {
                    return field.tomselect.wrapper;
                }

                const wrapper = field.closest('.ts-wrapper');
                return wrapper || field;
            }

            function clearFieldError(field) {
                if (!field) {
                    return;
                }

                const visual = getVisualField(field);
                const container = getFieldContainer(field);

                visual?.classList.remove('offer-required-invalid');
                field.classList.remove('offer-required-invalid');
                container?.classList.remove('offer-required-field-invalid');

                const message = container?.querySelector('.offer-required-message[data-client-required="1"]');
                message?.remove();
            }

            function setFieldError(field, message) {
                if (!field) {
                    return;
                }

                clearFieldError(field);

                const visual = getVisualField(field);
                const container = getFieldContainer(field);

                visual?.classList.add('offer-required-invalid');
                field.classList.add('offer-required-invalid');
                container?.classList.add('offer-required-field-invalid');

                if (container && message) {
                    const error = document.createElement('div');
                    error.className = 'offer-required-message';
                    error.dataset.clientRequired = '1';
                    error.textContent = message;
                    container.appendChild(error);
                }
            }

            function valuePresent(field) {
                return Boolean(field && String(field.value ?? '').trim() !== '');
            }

            function positiveNumber(field) {
                if (!valuePresent(field)) {
                    return false;
                }

                const parser = window.parseGermanNumber || ((value) => Number.parseFloat(String(value).replace(',', '.')));
                const parsed = parser(field.value);

                return Number.isFinite(parsed) && parsed > 0;
            }

            function clearAllClientErrors() {
                document
                    .querySelectorAll('.offer-required-invalid')
                    .forEach((element) => element.classList.remove('offer-required-invalid'));

                document
                    .querySelectorAll('.offer-required-field-invalid')
                    .forEach((element) => element.classList.remove('offer-required-field-invalid'));

                document
                    .querySelectorAll('.offer-required-message[data-client-required="1"]')
                    .forEach((element) => element.remove());
            }

            function validateOfferForm() {
                clearAllClientErrors();

                const invalidFields = [];

                const requireValue = (field, message) => {
                    if (!valuePresent(field)) {
                        setFieldError(field, message);
                        invalidFields.push(field);
                        return false;
                    }

                    return true;
                };

                const customer = document.getElementById('customer_id');
                const template = document.getElementById('template_type');
                const shippingMethod = document.getElementById('shipping_method');

                requireValue(customer, 'Bitte Kunde auswählen.');
                requireValue(template, 'Bitte PDF-Vorlage auswählen.');
                requireValue(shippingMethod, 'Bitte Versandart auswählen.');

                if (shippingMethod?.value === 'Lieferung') {
                    const cartonCount = document.getElementById('carton_count');

                    if (!positiveNumber(cartonCount)) {
                        setFieldError(cartonCount, 'Bitte Anzahl Kartons eingeben.');
                        invalidFields.push(cartonCount);
                    }
                }

                const rows = Array.from(document.querySelectorAll('#offer-items .offer-item-row'));
                let completePositions = 0;
                const partialRows = [];

                rows.forEach((row) => {
                    const product = row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]');
                    const quantity = row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]');

                    const hasProduct = valuePresent(product);
                    const hasQuantity = valuePresent(quantity);

                    if (!hasProduct && !hasQuantity) {
                        return;
                    }

                    partialRows.push({ product, quantity, hasProduct, hasQuantity });

                    if (hasProduct && positiveNumber(quantity)) {
                        completePositions += 1;
                        return;
                    }

                    if (!hasProduct) {
                        setFieldError(product, 'Bitte Produkt auswählen.');
                        invalidFields.push(product);
                    }

                    if (!positiveNumber(quantity)) {
                        setFieldError(quantity, 'Bitte Menge/Gewicht größer als 0 eingeben.');
                        invalidFields.push(quantity);
                    }
                });

                if (completePositions === 0 && partialRows.length === 0) {
                    const firstRow = rows[0];
                    const firstProduct = firstRow?.querySelector('select[name*="[product_id]"], select[data-name="product_id"]');
                    const firstQuantity = firstRow?.querySelector('input[name*="[quantity]"], input[data-name="quantity"]');

                    setFieldError(firstProduct, 'Bitte mindestens ein Produkt auswählen.');
                    setFieldError(firstQuantity, 'Bitte Menge/Gewicht eingeben.');

                    if (firstProduct) invalidFields.push(firstProduct);
                    if (firstQuantity) invalidFields.push(firstQuantity);
                }

                return invalidFields;
            }

            form.addEventListener('submit', function (event) {
                const invalidFields = validateOfferForm();

                if (invalidFields.length === 0) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const firstInvalid = invalidFields.find(Boolean);
                const firstContainer = getFieldContainer(firstInvalid) || getVisualField(firstInvalid);

                firstContainer?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            }, true);

            document.addEventListener('input', function (event) {
                const field = event.target;

                if (!(field instanceof HTMLInputElement) && !(field instanceof HTMLSelectElement)) {
                    return;
                }

                if (
                    field.id === 'customer_id'
                    || field.id === 'template_type'
                    || field.id === 'shipping_method'
                    || field.id === 'carton_count'
                    || field.name?.includes('[product_id]')
                    || field.name?.includes('[quantity]')
                    || field.dataset.name === 'product_id'
                    || field.dataset.name === 'quantity'
                ) {
                    clearFieldError(field);
                }
            }, true);

            document.addEventListener('change', function (event) {
                const field = event.target;

                if (!(field instanceof HTMLInputElement) && !(field instanceof HTMLSelectElement)) {
                    return;
                }

                clearFieldError(field);
            }, true);
        });

})();
