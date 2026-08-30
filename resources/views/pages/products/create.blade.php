<x-layouts.premium title="Produkt hinzufügen" subtitle="Produktdaten anlegen. Preise werden nicht hier gepflegt.">
    <section class="premium-card product-editor-card">
        <form class="product-editor-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
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
