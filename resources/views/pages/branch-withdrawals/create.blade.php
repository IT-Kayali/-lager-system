@php
    $isSales = auth()->user()?->isSales();
    $storeRoute = $isSales
        ? 'sales.branch-withdrawals.store'
        : 'branch-withdrawals.store';
@endphp

<x-layouts.premium title="Filialausgang erstellen" subtitle="Mehrere Produkte für eine Filiale vorbereiten und den Ausgabestatus verwalten.">
    <form class="branch-editor-form" method="POST" action="{{ route($storeRoute) }}">
        @if ($isSales)
            <input type="hidden" name="status" value="{{ \App\Models\BranchWithdrawal::STATUS_OPEN }}">
        @endif

        @include('pages.branch-withdrawals._form', ['submitLabel' => 'Filialausgang speichern'])
    </form>

    @if ($isSales)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const statusSelect = document.querySelector('.branch-editor-form select[name="status"]');
                if (statusSelect) {
                    statusSelect.value = @json(\App\Models\BranchWithdrawal::STATUS_OPEN);
                    statusSelect.disabled = true;
                }

                document.querySelectorAll('.branch-editor-form a[href="{{ route('branch-withdrawals.index') }}"]').forEach((link) => {
                    link.href = @json(route('sales.branch-withdrawals.index'));
                });
            });
        </script>
    @endif
</x-layouts.premium>
