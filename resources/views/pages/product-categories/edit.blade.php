<x-layouts.premium title="Kategorie bearbeiten" subtitle="Produktkategorie aktualisieren.">
    <div class="premium-card">
        <form method="POST" action="{{ route('product-categories.update', $category) }}">
            @method('PUT')
            @include('pages.product-categories._form', ['submitLabel' => 'Kategorie speichern'])
        </form>
    </div>
</x-layouts.premium>
