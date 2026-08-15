<x-layouts.premium title="Filialausgang" subtitle="Filialaufträge mit mehreren Produkten, Mengen und Ausgabestatus verwalten.">
    @if (session('success'))
        <div class="premium-alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @php
        $isManager = auth()->user()?->isManager();
        $isSales = auth()->user()?->isSales();
        $isWarehouse = auth()->user()?->isWarehouse();
        $canCreate = $isManager || $isSales;
        $createRoute = $isSales ? 'sales.branch-withdrawals.create' : 'branch-withdrawals.create';
    @endphp

    <section class="branch-header-card">
        <div>
            <div class="branch-eyebrow">Filialworkflow</div>
            <h2>Ausgaben vorbereiten und abschließen</h2>
            <p>Offene Vorgänge verändern den Bestand nicht. Erst der Status „Ausgegeben“ bucht die Ware per FIFO ab.</p>
        </div>

        @if ($canCreate)
            <a href="{{ route($createRoute) }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Filialausgang erstellen
            </a>
        @endif
    </section>

    <section class="branch-table-card">
        @if ($withdrawals->isEmpty())
            <div class="branch-empty-state">
                <i class="bi bi-shop"></i>
                <strong>Noch keine Filialausgänge vorhanden.</strong>
                <span>{{ $canCreate ? 'Erstelle den ersten Vorgang mit einer oder mehreren Produktpositionen.' : 'Aktuell liegen keine Filialausgänge zur Bearbeitung vor.' }}</span>
            </div>
        @else
            <div class="premium-table-wrap branch-table-wrap">
                <table class="premium-table branch-table">
                    <thead>
                        <tr>
                            <th>Filialausgang</th>
                            <th>Filiale</th>
                            <th>Status</th>
                            <th>Mitarbeiter</th>
                            <th>Notiz</th>
                            <th>Lieferschein</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($withdrawals as $withdrawal)
                            @php
                                $statusLabel = \App\Models\BranchWithdrawal::statusLabels()[$withdrawal->status] ?? $withdrawal->status;
                                $statusClass = match ($withdrawal->status) {
                                    \App\Models\BranchWithdrawal::STATUS_OPEN => 'open',
                                    \App\Models\BranchWithdrawal::STATUS_IN_PROGRESS => 'progress',
                                    \App\Models\BranchWithdrawal::STATUS_ISSUED => 'issued',
                                    \App\Models\BranchWithdrawal::STATUS_CANCELLED => 'cancelled',
                                    default => '',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="branch-number-cell">
                                        <strong>{{ $withdrawal->withdrawal_number }}</strong>
                                        <span>{{ $withdrawal->created_at?->format('d.m.Y H:i') }}</span>
                                    </div>
                                </td>

                                <td>
                                    <span class="branch-name-pill"><i class="bi bi-shop-window"></i>{{ $withdrawal->branch_name }}</span>
                                </td>

                                <td>
                                    @if ($isWarehouse && ! $isManager)
                                        <form method="POST" action="{{ route('branch-withdrawals.update', $withdrawal) }}" class="branch-status-form">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="premium-select branch-status-select" aria-label="Status für {{ $withdrawal->withdrawal_number }} ändern">
                                                @foreach (\App\Models\BranchWithdrawal::statusLabels() as $statusValue => $statusText)
                                                    <option value="{{ $statusValue }}" @selected($withdrawal->status === $statusValue)>{{ $statusText }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="premium-icon-btn" title="Status speichern" aria-label="Status speichern"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    @else
                                        <span class="branch-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    @endif
                                </td>

                                <td><strong class="branch-employee-name">{{ $withdrawal->user?->name ?? 'System' }}</strong></td>

                                <td>
                                    <span class="branch-note-text" title="{{ $withdrawal->note }}">{{ filled($withdrawal->note) ? Str::limit($withdrawal->note, 70) : '—' }}</span>
                                </td>

                                <td>
                                    @if (! $isSales)
                                        <a href="{{ route('branch-withdrawals.delivery-note', $withdrawal) }}" class="premium-icon-btn" target="_blank" rel="noopener" title="Filial-Lieferschein öffnen" aria-label="Filial-Lieferschein öffnen">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    @else
                                        <span class="branch-role-note">—</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="branch-actions">
                                        @if ($isManager)
                                            <a href="{{ route('branch-withdrawals.edit', $withdrawal) }}" class="premium-icon-btn" title="Bearbeiten" aria-label="Filialausgang bearbeiten"><i class="bi bi-pencil"></i></a>

                                            <form method="POST" action="{{ route('branch-withdrawals.destroy', $withdrawal) }}" onsubmit="return confirm('Filialausgang wirklich löschen? Bereits ausgegebene Mengen werden automatisch zurückgebucht.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="premium-icon-btn premium-danger" title="Löschen" aria-label="Filialausgang löschen"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @elseif($isWarehouse)
                                            <span class="branch-role-note">Nur Statusänderung</span>
                                        @else
                                            <span class="branch-role-note">Nur Erstellen</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($withdrawals->hasPages())
                <div class="branch-pagination">{{ $withdrawals->links() }}</div>
            @endif
        @endif
    </section>

    <style>
        .branch-header-card{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:22px;padding:26px;border:1px solid #d8cbb7;border-radius:22px;background:radial-gradient(circle at 92% 10%,rgba(212,170,32,.18),transparent 30%),rgba(255,255,255,.88);box-shadow:0 18px 45px rgba(42,36,25,.08)}.branch-eyebrow{color:#8a6a00;font-size:12px;font-weight:950;text-transform:uppercase;letter-spacing:.11em}.branch-header-card h2{margin:5px 0 6px;color:#111;font-size:30px;font-weight:950}.branch-header-card p{margin:0;color:#665f54;font-weight:700}.branch-table-card{border:1px solid #d8cbb7;border-radius:22px;background:rgba(255,255,255,.9);box-shadow:0 18px 45px rgba(42,36,25,.07);overflow:hidden}.branch-table-wrap{margin:0!important;border:0!important;border-radius:0!important;box-shadow:none!important}.branch-table{min-width:1080px}.branch-table thead th{padding:16px 14px!important;background:#eee7dc!important;color:#3a332a!important;border-bottom:2px solid #8d8069!important}.branch-table tbody td{padding:15px 14px!important;vertical-align:middle;color:#111!important}.branch-number-cell{display:grid;gap:4px;min-width:170px}.branch-number-cell strong{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;font-weight:950}.branch-number-cell span{color:#665f54;font-size:12px;font-weight:750}.branch-name-pill{display:inline-flex;align-items:center;gap:7px;min-height:34px;padding:7px 11px;border:1px solid #d8cbb7;border-radius:999px;background:#f8f2e7;font-size:13px;font-weight:900;white-space:nowrap}.branch-status-badge{display:inline-flex;align-items:center;min-height:34px;padding:7px 11px;border-radius:999px;font-size:13px;font-weight:950;white-space:nowrap}.branch-status-badge.open{background:#fff1c2;color:#7a5600}.branch-status-badge.progress{background:#dbeafe;color:#1d4ed8}.branch-status-badge.issued{background:#dcfce7;color:#166534}.branch-status-badge.cancelled{background:#fee2e2;color:#991b1b}.branch-status-form{display:flex;align-items:center;gap:7px;margin:0;min-width:220px}.branch-status-select{min-width:165px;min-height:38px}.branch-role-note{color:#665f54;font-size:12px;font-weight:850;white-space:nowrap}.branch-employee-name{display:inline-block;min-width:120px;color:#111;font-size:13px;font-weight:950}.branch-note-text{display:inline-block;max-width:260px;color:#3a332a;font-weight:750}.branch-actions{display:flex;gap:8px;align-items:center}.branch-actions form{margin:0}.branch-pagination{padding:16px 18px;border-top:1px solid #e7dece;background:#f8f2e7}.branch-empty-state{display:grid;place-items:center;gap:8px;padding:64px 16px;text-align:center;color:#665f54}.branch-empty-state i{font-size:38px;color:#8a6a00}.branch-empty-state strong{color:#111;font-size:18px}@media(max-width:900px){.branch-header-card{display:grid}.branch-header-card .premium-btn{justify-self:start}}
    </style>
</x-layouts.premium>
