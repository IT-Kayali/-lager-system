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

{{-- CSP static styles moved to public/css/csp-static-bulk.css: resources/views/pages/offers/create.blade.php --}}


{{-- OFFER_CATEGORY_PRODUCT_FILTER_END --}}

{{-- OFFER_SHIPPING_MOVE_TOP_START --}}

{{-- OFFER_SHIPPING_MOVE_TOP_END --}}

{{-- OFFER_SHIPPING_FORCE_FULL_WIDTH_START --}}
{{-- CSP static styles moved to public/css/csp-static-bulk.css: resources/views/pages/offers/create.blade.php --}}
{{-- OFFER_SHIPPING_FORCE_FULL_WIDTH_END --}}


{{-- OFFER_SHIPPING_HIDDEN_SYNC_START --}}

{{-- OFFER_SHIPPING_HIDDEN_SYNC_END --}}

{{-- OFFER_REQUIRED_FIELDS_VALIDATION_START --}}
{{-- CSP static styles moved to public/css/csp-static-bulk.css: resources/views/pages/offers/create.blade.php --}}


{{-- OFFER_REQUIRED_FIELDS_VALIDATION_END --}}

    <script
        src="{{ asset('js/offers-editor-runtime.js') }}"
        defer
    ></script>

</x-layouts.premium>