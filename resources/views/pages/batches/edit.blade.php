<x-layouts.premium title="Charge bearbeiten" subtitle="Chargendaten und Menge bearbeiten. Jede Mengenänderung wird als Lagerbewegung protokolliert.">
    <section class="premium-card">
        <form method="POST" action="{{ route('batches.update', $batch) }}">
            @method('PUT')
            @include('pages.batches._form')
        </form>
    </section>
</x-layouts.premium>
