<x-layouts.premium title="Produkt hinzufügen" subtitle="Produktdaten anlegen. Preise werden nicht hier gepflegt.">
    <section class="premium-card product-editor-card">
        <form class="product-editor-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @include('pages.products._form')
        </form>
    </section>
</x-layouts.premium>
