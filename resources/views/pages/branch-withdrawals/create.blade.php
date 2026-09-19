@php
    $isSales = auth()->user()?->isSales();
    $storeRoute = $isSales
        ? 'sales.branch-withdrawals.store'
        : 'branch-withdrawals.store';
@endphp

<x-layouts.premium title="Filialausgang erstellen" subtitle="Mehrere Produkte für eine Filiale vorbereiten und den Ausgabestatus verwalten.">
    <form
        class="branch-editor-form branch-editor-form-create"
        method="POST"
        action="{{ route($storeRoute) }}"
        @if ($isSales)
            data-sales-branch-create
            data-open-status="{{ \App\Models\BranchWithdrawal::STATUS_OPEN }}"
            data-default-index-url="{{ route('branch-withdrawals.index') }}"
            data-sales-index-url="{{ route('sales.branch-withdrawals.index') }}"
        @endif
    >
        @if ($isSales)
            <input type="hidden" name="status" value="{{ \App\Models\BranchWithdrawal::STATUS_OPEN }}">
        @endif

        @include('pages.branch-withdrawals._form', ['submitLabel' => 'Filialausgang speichern'])
    </form>

    {{-- CSP static styles moved to public/css/csp-static-bulk.css: resources/views/pages/branch-withdrawals/create.blade.php --}}
</x-layouts.premium>