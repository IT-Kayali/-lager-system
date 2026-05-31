<x-layouts.premium title="Filialausgang" subtitle="Ware für lokales Geschäft / Filiale entnehmen. Mindestbestand darf hierfür genutzt werden.">
    <section class="premium-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Filialausgänge</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    Diese Entnahmen reduzieren den echten Lagerbestand und werden mit Benutzer und Notiz protokolliert.
                </p>
            </div>

            <a href="{{ route('branch-withdrawals.create') }}" class="premium-btn gold">
                <i class="bi bi-shop"></i>
                Filialausgang buchen
            </a>
        </div>

        @if (session('success'))
            <div class="premium-alert success" style="margin-bottom:16px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($withdrawals->isEmpty())
            <div class="premium-placeholder">
                Noch keine Filialausgänge vorhanden.
            </div>
        @else
            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Nummer</th>
                            <th>Produkt</th>
                            <th>Menge</th>
                            <th>Bestand vorher</th>
                            <th>Bestand danach</th>
                            <th>Filiale</th>
                            <th>Benutzer</th>
                            <th>Datum</th>
                            <th>Notiz</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($withdrawals as $withdrawal)
                            <tr>
                                <td><strong>{{ $withdrawal->withdrawal_number }}</strong></td>
                                <td>{{ $withdrawal->product?->name ?? '—' }}</td>
                                <td>{{ number_format((float) $withdrawal->quantity, 3, ',', '.') }}</td>
                                <td>{{ number_format((float) $withdrawal->stock_before, 3, ',', '.') }}</td>
                                <td>{{ number_format((float) $withdrawal->stock_after, 3, ',', '.') }}</td>
                                <td>{{ $withdrawal->branch_name }}</td>
                                <td>{{ $withdrawal->user?->name ?? 'System' }}</td>
                                <td>{{ $withdrawal->created_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ Str::limit($withdrawal->note, 60) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </section>
</x-layouts.premium>
