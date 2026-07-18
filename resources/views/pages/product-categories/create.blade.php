<x-layouts.premium title="Kategorie erstellen" subtitle="Neue Produktkategorie anlegen.">
    <section class="premium-card category-editor-card">
        <form class="category-editor-form" method="POST" action="{{ route('product-categories.store') }}">
            @include('pages.product-categories._form', ['submitLabel' => 'Kategorie erstellen'])
        </form>
    </section>
</x-layouts.premium>
