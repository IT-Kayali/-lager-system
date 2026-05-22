<x-layouts.premium title="Produkt bearbeiten" subtitle="Produktdaten bearbeiten. Preise bleiben ausschließlich im Preise-Tab.">
    <section class="premium-card">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('pages.products._form')
        </form>
    </section>
</x-layouts.premium>
