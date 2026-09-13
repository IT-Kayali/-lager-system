<x-layouts.premium title="Produkt hinzufügen" subtitle="Produktdaten anlegen. Preise werden nicht hier gepflegt.">
    <section class="premium-card product-editor-card">
        <form
            class="product-editor-form"
            method="POST"
            action="{{ route('products.store') }}"
            enctype="multipart/form-data"
            data-product-editor-runtime
            data-product-create-runtime
        >
            @include('pages.products._form')
        </form>
    </section>
</x-layouts.premium>
