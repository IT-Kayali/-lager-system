@php
    $storeRoute = auth()->user()?->isSales()
        ? 'sales.branch-withdrawals.store'
        : 'branch-withdrawals.store';
@endphp

<x-layouts.premium title="Filialausgang erstellen" subtitle="Mehrere Produkte für eine Filiale vorbereiten und den Ausgabestatus verwalten.">
    <form class="branch-editor-form" method="POST" action="{{ route($storeRoute) }}">
        @include('pages.branch-withdrawals._form', ['submitLabel' => 'Filialausgang speichern'])
    </form>
</x-layouts.premium>
