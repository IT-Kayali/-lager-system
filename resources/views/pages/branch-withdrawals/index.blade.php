<x-layouts.premium title="Filialausgang" subtitle="Filialaufträge mit mehreren Produkten, Mengen und Ausgabestatus verwalten.">
    @if (session('success'))
        <div class="premium-alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="branch-header-card">
        <div>
            <div class="branch-eyebrow">Filialworkflow</div>
            <h2>Ausgaben vorbereiten und abschließen</h2>
            <p>Offene Vorgänge verändern den Bestand nicht. Erst der Status „Ausgegeben“ bucht die Ware per FIFO ab.</p>
        </div>

        <a href="{{ route('branch-withdrawals.create') }}" class="premium-btn gold">
            <i class="bi bi-plus-lg"></i>
            Filialausgang erstellen
        </a>
    </section>

    <section class="branch-list-card">
        @forelse ($withdrawals as $withdrawal)
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

            <article class="branch-workflow-row">
                <div class="branch-workflow-top">
                    <div>
                        <div class="branch-number-pill">
                            <i class="bi bi-receipt"></i>
                            {{ $withdrawal->withdrawal_number }}
                        </div>
                        <div class="branch-meta-line">
                            <span><i class="bi bi-shop-window"></i> {{ $withdrawal->branch_name }}</span>
                            <span><i class="bi bi-box-seam"></i> {{ $withdrawal->items->count() }} Produkt(e)</span>
                            <span><i class="bi bi-calendar3"></i> {{ $withdrawal->created_at?->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>

                    <span class="branch-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                <div class="branch-product-list">
                    @foreach ($withdrawal->items as $item)
                        <div class="branch-product-item">
                            <div>
                                <strong>{{ $item->product?->name ?? 'Gelöschtes Produkt' }}</strong>
                                <span>{{ $item->product?->product_code ?: 'Kein Produktcode' }}</span>
                            </div>
                            <b>{{ number_format((float) $item->quantity, 3, ',', '.') }}</b>
                        </div>
                    @endforeach
                </div>

                <div class="branch-workflow-footer">
                    <div class="branch-people">
                        <span>Erstellt von: <strong>{{ $withdrawal->user?->name ?? 'System' }}</strong></span>
                        <span>Ausgegeben von: <strong>{{ $withdrawal->processor?->name ?? 'Noch nicht ausgegeben' }}</strong></span>
                    </div>

                    @if (filled($withdrawal->note))
                        <div class="branch-note"><i class="bi bi-chat-left-text"></i> {{ Str::limit($withdrawal->note, 140) }}</div>
                    @endif

                    <div class="branch-actions">
                        <a href="{{ route('branch-withdrawals.edit', $withdrawal) }}" class="premium-btn">
                            <i class="bi bi-pencil"></i>
                            Bearbeiten
                        </a>

                        @if (auth()->user()?->isManager())
                            <form method="POST" action="{{ route('branch-withdrawals.destroy', $withdrawal) }}" onsubmit="return confirm('Filialausgang wirklich löschen? Bereits ausgegebene Mengen werden automatisch zurückgebucht.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="premium-btn premium-danger">
                                    <i class="bi bi-trash"></i>
                                    Löschen
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="branch-empty-state">
                <i class="bi bi-shop"></i>
                <strong>Noch keine Filialausgänge vorhanden.</strong>
                <span>Erstelle den ersten Vorgang mit einer oder mehreren Produktpositionen.</span>
            </div>
        @endforelse

        @if ($withdrawals->hasPages())
            <div class="branch-pagination">{{ $withdrawals->links() }}</div>
        @endif
    </section>

    <style>
        .branch-header-card { display:flex; align-items:center; justify-content:space-between; gap:18px; margin-bottom:22px; padding:26px; border:1px solid #d8cbb7; border-radius:22px; background:radial-gradient(circle at 92% 10%,rgba(212,170,32,.18),transparent 30%),rgba(255,255,255,.88); box-shadow:0 18px 45px rgba(42,36,25,.08); }
        .branch-eyebrow { color:#8a6a00; font-size:12px; font-weight:950; text-transform:uppercase; letter-spacing:.11em; }
        .branch-header-card h2 { margin:5px 0 6px; color:#111; font-size:30px; font-weight:950; }
        .branch-header-card p { margin:0; color:#665f54; font-weight:700; }
        .branch-list-card { display:grid; gap:14px; }
        .branch-workflow-row { border:1px solid #d8cbb7; border-radius:22px; background:rgba(255,255,255,.9); box-shadow:0 18px 45px rgba(42,36,25,.07); overflow:hidden; }
        .branch-workflow-top { display:flex; justify-content:space-between; gap:16px; padding:20px 22px; border-bottom:1px solid #e7dece; background:#fffdf8; }
        .branch-number-pill { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#f3e8be; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:13px; font-weight:950; }
        .branch-meta-line { display:flex; flex-wrap:wrap; gap:8px 16px; margin-top:12px; color:#665f54; font-size:13px; font-weight:800; }
        .branch-status-badge { align-self:flex-start; display:inline-flex; align-items:center; min-height:36px; padding:7px 12px; border-radius:999px; font-size:13px; font-weight:950; white-space:nowrap; }
        .branch-status-badge.open { background:#fff1c2; color:#7a5600; }
        .branch-status-badge.progress { background:#dbeafe; color:#1d4ed8; }
        .branch-status-badge.issued { background:#dcfce7; color:#166534; }
        .branch-status-badge.cancelled { background:#fee2e2; color:#991b1b; }
        .branch-product-list { display:grid; gap:0; }
        .branch-product-item { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 22px; border-top:1px solid #eee6da; }
        .branch-product-item:first-child { border-top:0; }
        .branch-product-item div { display:grid; gap:3px; }
        .branch-product-item strong { color:#111; font-weight:950; }
        .branch-product-item span { color:#665f54; font-size:12px; font-weight:800; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; }
        .branch-product-item b { color:#991b1b; font-size:15px; font-weight:950; }
        .branch-workflow-footer { display:grid; gap:12px; padding:18px 22px; border-top:1px solid #e7dece; background:#faf7f0; }
        .branch-people { display:flex; flex-wrap:wrap; gap:8px 20px; color:#665f54; font-size:13px; font-weight:750; }
        .branch-people strong { color:#111; }
        .branch-note { display:flex; gap:8px; color:#3a332a; font-weight:750; }
        .branch-actions { display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
        .branch-actions form { margin:0; }
        .branch-empty-state { display:grid; place-items:center; gap:8px; padding:64px 16px; text-align:center; color:#665f54; border:1px solid #d8cbb7; border-radius:22px; background:#fff; }
        .branch-empty-state i { font-size:38px; color:#8a6a00; }
        .branch-empty-state strong { color:#111; font-size:18px; }
        .branch-pagination { padding:16px 18px; border:1px solid #d8cbb7; border-radius:18px; background:#f8f2e7; }
        @media (max-width:900px) { .branch-header-card,.branch-workflow-top { display:grid; } .branch-header-card .premium-btn { justify-self:start; } .branch-status-badge { justify-self:start; } }
        @media (max-width:640px) { .branch-product-item,.branch-actions { align-items:stretch; display:grid; } .branch-product-item b { justify-self:start; } .branch-actions .premium-btn { width:100%; } }
    </style>
</x-layouts.premium>
