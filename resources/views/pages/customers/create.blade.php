<x-layouts.premium title="Kunde hinzufügen" subtitle="Kundendaten und Kundengruppe erfassen.">
    <section class="premium-card">
        <form method="POST" action="{{ route('customers.store') }}">
            @include('pages.customers._form')
        </form>
    </section>
</x-layouts.premium>
