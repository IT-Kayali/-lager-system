<x-layouts.premium title="Neues Angebot" subtitle="Kunde auswählen, Produkte hinzufügen und Ware automatisch reservieren.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card offer-editor-card">
        <form class="offer-editor-form" method="POST" action="{{ route('offers.store') }}" id="offer-main-form" novalidate>

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

<div
    id="offer-editor-runtime-config"
    hidden
    data-price-preview-url="{{ route('offers.price-preview') }}"
    data-offer-categories="{{ json_encode(
        $offerCategoryFilterCategories
            ->map(fn ($category) => [
                'id' => (string) $category->id,
                'name' => $category->name,
            ])
            ->values()
    ) }}"
    data-product-category-map="{{ json_encode(
        $offerCategoryFilterProducts
    ) }}"
    data-required-validation="1"
></div>

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


{{-- OFFER_CATEGORY_PRODUCT_FILTER_END --}}

{{-- OFFER_SHIPPING_MOVE_TOP_START --}}

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

{{-- OFFER_SHIPPING_HIDDEN_SYNC_END --}}

{{-- OFFER_REQUIRED_FIELDS_VALIDATION_START --}}
<style>
    #offer-main-form .offer-required-invalid,
    #offer-shipping-card .offer-required-invalid {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .10) !important;
    }

    #offer-main-form .ts-wrapper.offer-required-invalid .ts-control,
    #offer-shipping-card .ts-wrapper.offer-required-invalid .ts-control {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .10) !important;
    }

    .offer-required-message {
        margin-top: 6px;
        color: #b91c1c;
        font-size: 12px;
        font-weight: 850;
        line-height: 1.35;
    }

    .offer-required-field-invalid > label,
    .offer-required-field-invalid .offer-line-total-label-row label {
        color: #b91c1c !important;
    }
</style>


{{-- OFFER_REQUIRED_FIELDS_VALIDATION_END --}}

    <script
        src="{{ asset('js/offers-editor-runtime.js') }}"
        defer
    ></script>

</x-layouts.premium>
