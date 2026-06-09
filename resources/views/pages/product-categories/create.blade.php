<x-layouts.premium title="Kategorie erstellen" subtitle="Neue Produktkategorie anlegen.">
    <div class="premium-card">
        <form method="POST" action="{{ route('product-categories.store') }}">
            @include('pages.product-categories._form', ['submitLabel' => 'Kategorie erstellen'])
        </form>
    </div>
</x-layouts.premium>
