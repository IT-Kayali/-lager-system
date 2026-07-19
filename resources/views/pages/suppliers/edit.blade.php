<x-layouts.premium title="Lieferant bearbeiten" subtitle="Lieferantendaten und Adresse bearbeiten.">
    <section class="premium-card supplier-editor-card">
        <form class="supplier-editor-form" method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            @include('pages.suppliers._form')
        </form>
    </section>
</x-layouts.premium>
