<x-layouts.premium title="Angebot bearbeiten" subtitle="Positionen, Kunde und Vorlage bearbeiten, solange das Angebot offen ist.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <form method="POST" action="{{ route('offers.update', $offer) }}">
            @method('PUT')
            @include('pages.offers._form', ['submitLabel' => 'Angebot speichern'])
        </form>
    </section>
</x-layouts.premium>
