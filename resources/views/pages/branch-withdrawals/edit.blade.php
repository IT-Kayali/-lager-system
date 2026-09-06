<x-layouts.premium title="Filialausgang bearbeiten" subtitle="Produkte, Mengen, Filiale und Status sicher aktualisieren.">
    <form class="branch-editor-form" method="POST" action="{{ route(auth()->user()?->isSales() ? 'sales.branch-withdrawals.update' : 'branch-withdrawals.update', $withdrawal) }}">
        @method('PUT')
        @include('pages.branch-withdrawals._form', ['submitLabel' => 'Änderungen speichern'])
    </form>
</x-layouts.premium>
