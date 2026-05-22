<x-layouts.premium title="Kunde bearbeiten" subtitle="Kundendaten aktualisieren.">
    <section class="premium-card">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @method('PUT')
            @include('pages.customers._form')
        </form>
    </section>
</x-layouts.premium>
