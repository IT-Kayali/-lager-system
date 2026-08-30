<x-layouts.premium title="Produkt bearbeiten" subtitle="Produktdaten bearbeiten. Preise bleiben ausschließlich im Preise-Tab.">
    <section class="premium-card product-editor-card">
        <form class="product-editor-form" method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('pages.products._form')
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const label = document.querySelector('label[for="manufacturer_designation"]');

            if (label) {
                label.textContent = 'Fake Name';
            }
        });
    </script>
</x-layouts.premium>
