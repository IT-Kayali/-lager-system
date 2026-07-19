<x-layouts.premium title="Kategorie bearbeiten" subtitle="Produktkategorie aktualisieren.">
    <section class="premium-card category-editor-card">
        <form class="category-editor-form" method="POST" action="{{ route('product-categories.update', $category) }}">
            @method('PUT')
            @include('pages.product-categories._form', ['submitLabel' => 'Kategorie speichern'])
        </form>
    </section>
</x-layouts.premium>
