<x-layouts.premium title="Lieferant hinzufügen" subtitle="Lieferant mit Adresse und Kontaktdaten anlegen.">
    <section class="premium-card supplier-editor-card">
        <form class="supplier-editor-form" method="POST" action="{{ route('suppliers.store') }}">
            @include('pages.suppliers._form')
        </form>
    </section>
</x-layouts.premium>
