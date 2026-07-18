<x-layouts.premium title="Charge hinzufügen" subtitle="Neue Warenlieferung als eigene Charge erfassen.">
    <section class="premium-card batch-editor-card">
        <form class="batch-editor-form" method="POST" action="{{ route('batches.store') }}">
            @include('pages.batches._form')
        </form>
    </section>
</x-layouts.premium>
