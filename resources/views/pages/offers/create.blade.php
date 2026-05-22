<x-layouts.premium title="Neues Angebot" subtitle="Kunde auswählen, Produkte hinzufügen und Ware automatisch reservieren.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <form method="POST" action="{{ route('offers.store') }}">
            @include('pages.offers._form', ['submitLabel' => 'Angebot erstellen & reservieren'])
        </form>
    </section>
</x-layouts.premium>
