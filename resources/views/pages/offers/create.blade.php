<x-layouts.premium title="Neues Angebot" subtitle="Kunde auswählen, Produkte hinzufügen und Ware automatisch reservieren.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <form method="POST" action="{{ route('offers.store') }}">
            @include('pages.offers._form', ['submitLabel' => 'Angebot erstellen & reservieren'])
        </form>
    </section>

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

    .offer-category-select {
        background: #ffffff !important;
    }

    .offer-category-product-row {
        display: grid !important;
        grid-template-columns: minmax(190px, .75fr) minmax(280px, 1.35fr) minmax(160px, .55fr) auto !important;
        gap: 12px !important;
        align-items: end !important;
    }

    .offer-category-product-row .premium-form-field {
        margin: 0 !important;
    }

    @media (max-width: 1100px) {
        .offer-category-product-row {
            grid-template-columns: 1fr 1fr !important;
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
            return name.includes('product_id') || name.includes('product');
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

        function filterProducts(productSelect, categorySelect, resetProduct = true) {
            const categoryId = categorySelect.value;
            const currentValue = resetProduct ? '' : productSelect.value;
            const originalOptions = getOriginalOptions(productSelect);

            const hasTomSelect = clearTomSelect(productSelect);

            productSelect.innerHTML = '';

            originalOptions.forEach((option) => {
                const isEmpty = option.value === '';
                const categories = productCategoryMap[String(option.value)] || [];

                const allowed = isEmpty || !categoryId || categories.includes(String(categoryId));

                if (!allowed) {
                    return;
                }

                const newOption = document.createElement('option');
                newOption.value = option.value;
                newOption.textContent = option.text;
                newOption.disabled = option.disabled;

                if (!resetProduct && option.value === currentValue) {
                    newOption.selected = true;
                }

                productSelect.appendChild(newOption);

                if (hasTomSelect) {
                    addTomSelectOption(productSelect, option);
                }
            });

            if (resetProduct) {
                productSelect.value = '';

                if (productSelect.tomselect) {
                    productSelect.tomselect.clear(true);
                }
            } else {
                productSelect.value = currentValue;
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
            if (!productField) {
                return;
            }

            let categoryField = productField.parentElement?.querySelector('.offer-category-filter-field');

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

            if (!productSelect.value && categorySelect.value) {
                categorySelect.value = '';
            }

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

</x-layouts.premium>
