<x-layouts.premium title="Neues Angebot" subtitle="Kunde auswählen, Produkte hinzufügen und Ware automatisch reservieren.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card offer-editor-card">
        <form class="offer-editor-form" method="POST" action="{{ route('offers.store') }}" id="offer-main-form">

{{-- OFFER_SHIPPING_HIDDEN_FIELDS_START --}}
<input type="hidden" name="shipping_method" id="shipping_method_real" value="{{ old('shipping_method', $offer->shipping_method ?? '') }}">
<input type="hidden" name="shipping_price_gross" id="shipping_price_gross_real" value="{{ old('shipping_price_gross', $offer->shipping_price_gross ?? '') }}">
{{-- OFFER_SHIPPING_HIDDEN_FIELDS_END --}}

            @include('pages.offers._form', ['submitLabel' => 'Angebot erstellen & reservieren'])
        </form>
    </section>


{{-- OFFER_SHIPPING_METHOD_START --}}
@php
    $shippingMethodValue = old('shipping_method', $offer->shipping_method ?? '');
    $shippingPriceValue = old('shipping_price_gross', $offer->shipping_price_gross ?? '');
@endphp

<div id="offer-shipping-card" class="premium-card offer-shipping-modern-card" style="box-shadow:none; margin:28px 0 18px; width:100%; max-width:none; grid-column:1 / -1;">
    <h3 style="font-size:18px; font-weight:900; margin:0 0 12px;">Versand</h3>

    <div class="premium-form-grid">
        <div class="premium-form-field full offer-shipping-method-field">
            <label for="shipping_method">Versandart *</label>
            <select id="shipping_method" class="premium-select" required>
                <option value="">Versandart auswählen</option>
                <option value="Lieferung" @selected($shippingMethodValue === 'Lieferung')>Lieferung</option>
                <option value="Abholung" @selected($shippingMethodValue === 'Abholung')>Abholung</option>
            </select>
            @error('shipping_method') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field full offer-shipping-price-field" id="shipping_price_gross_field">
            <label for="shipping_price_gross">Versandpreis brutto</label>
            <input
               
                id="shipping_price_gross"
                type="number"
                step="0.01"
                min="0"
                class="premium-input"
                value="{{ $shippingPriceValue }}"
                placeholder="z. B. 6.90"
            >
            <div class="premium-muted" style="margin-top:6px;">
                Nur bei Lieferung. Wird in Angebot und Rechnung angezeigt, nicht im Lieferschein.
            </div>
            @error('shipping_price_gross') <div class="premium-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<script>
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
</script>
{{-- OFFER_SHIPPING_METHOD_END --}}


{{-- OFFER_CATEGORY_PRODUCT_FILTER_START --}}
@php
    $offerCategoryFilterCategories = \App\Models\ProductCategory::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    $offerCategoryFilterProducts = ($products ?? collect())->mapWithKeys(function ($product) {
        return [
            (string) $product->id => $product->categories->pluck('id')->map(fn ($id) => (string) $id)->values(),
        ];
    });
@endphp

<style>
    .offer-category-filter-field {
        min-width: 220px;
    }

    .offer-category-select,
    .offer-category-product-row select,
    .offer-category-product-row .ts-control {
        background: #ffffff !important;
    }

    .offer-category-product-row {
        display: grid !important;
        grid-template-columns: 190px 520px 220px 300px !important;
        gap: 14px !important;
        align-items: start !important;
        justify-content: start !important;
        width: 100% !important;
    }

    .offer-category-product-row > .premium-form-field {
        min-width: 0 !important;
        width: 100% !important;
    }

    .offer-category-product-row .premium-form-field {
        margin: 0 !important;
    }

    .offer-category-product-row select:disabled,
    .offer-category-product-row .ts-wrapper.disabled .ts-control {
        opacity: .75 !important;
        cursor: not-allowed !important;
        background: #f5f0e7 !important;
    }

    .offer-category-product-row .ts-wrapper.disabled .ts-control input {
        cursor: not-allowed !important;
    }

    @media (max-width: 1350px) {
        .offer-category-product-row {
            grid-template-columns: 170px minmax(0, 1fr) 190px 280px !important;
        }
    }

    @media (max-width: 1050px) {
        .offer-category-product-row {
            grid-template-columns: 1fr 1fr !important;
        }

        .offer-category-product-row > .premium-form-field:last-child {
            padding-top: 0 !important;
        }
    }

    @media (max-width: 700px) {
        .offer-category-product-row {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const offerCategories = @json($offerCategoryFilterCategories->map(fn ($category) => [
            'id' => (string) $category->id,
            'name' => $category->name,
        ])->values());

        const productCategoryMap = @json($offerCategoryFilterProducts);

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
</script>
{{-- OFFER_CATEGORY_PRODUCT_FILTER_END --}}

{{-- OFFER_SHIPPING_MOVE_TOP_START --}}
<script>
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
</script>
{{-- OFFER_SHIPPING_MOVE_TOP_END --}}

{{-- OFFER_SHIPPING_FORCE_FULL_WIDTH_START --}}
<style>
    #offer-shipping-card {
        width: 100% !important;
        max-width: none !important;
        min-width: 100% !important;
        flex: 0 0 100% !important;
        grid-column: 1 / -1 !important;
        align-self: stretch !important;
        box-sizing: border-box !important;
        display: block !important;
    }

    #offer-shipping-card .premium-form-grid {
        width: 100% !important;
        max-width: none !important;
        display: grid !important;
        grid-template-columns: minmax(280px, 480px) minmax(220px, 320px) !important;
        gap: 14px !important;
    }

    #offer-shipping-card .premium-form-field {
        width: 100% !important;
        max-width: none !important;
    }

    #offer-shipping-card select {
        width: 100% !important;
        max-width: 480px !important;
    }

    #offer-shipping-card input {
        width: 100% !important;
        max-width: 320px !important;
    }

    @media (max-width: 900px) {
        #offer-shipping-card .premium-form-grid {
            grid-template-columns: 1fr !important;
        }

        #offer-shipping-card select,
        #offer-shipping-card input {
            max-width: none !important;
        }
    }
</style>
{{-- OFFER_SHIPPING_FORCE_FULL_WIDTH_END --}}


{{-- OFFER_SHIPPING_HIDDEN_SYNC_START --}}
<script>
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
</script>
{{-- OFFER_SHIPPING_HIDDEN_SYNC_END --}}

</x-layouts.premium>
