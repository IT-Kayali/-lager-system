<x-layouts.premium title="Chargen & FIFO" subtitle="Wareneingänge, Batchnummern und FIFO-Lagerlogik.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="batches-page-actions erp-list-toolbar">
        <div class="batches-search-card erp-list-filter-card">
            <form method="GET" action="{{ route('batches.index') }}" class="batches-search-modern erp-list-filter-form">
                <div class="batches-search-field erp-list-search">
                    <i class="bi bi-search"></i>
                    <input name="search" value="{{ $search ?? '' }}" class="premium-input" placeholder="Charge, Produkt, Code oder Fake Name suchen...">
                </div>

                <div class="batches-search-filter">
                    <select name="search_field" class="premium-input batches-search-select erp-list-select" aria-label="Suchfeld auswählen">
                        <option value="all" @selected(($searchField ?? 'all') === 'all')>Alle</option>
                        <option value="batch" @selected(($searchField ?? 'all') === 'batch')>Batchnummer</option>
                        <option value="product" @selected(($searchField ?? 'all') === 'product')>Produkt</option>
                        <option value="code" @selected(($searchField ?? 'all') === 'code')>Code-Nummer</option>
                        <option value="manufacturer" @selected(($searchField ?? 'all') === 'manufacturer')>Fake Name</option>
                    </select>
                </div>

                <label class="batches-exact-search erp-list-exact">
                    <input type="checkbox" name="exact" value="1" @checked($exact ?? false)>
                    <span>Exakter Wert</span>
                </label>

                <button class="premium-btn" type="submit"><i class="bi bi-search"></i> Suchen</button>

                @if (! empty($search) || ($searchField ?? 'all') !== 'all' || ($exact ?? false))
                    <a href="{{ route('batches.index') }}" class="premium-btn"><i class="bi bi-x-lg"></i> Zurücksetzen</a>
                @endif
            </form>
        </div>
        <a href="{{ route('batches.create') }}" class="premium-btn gold batches-create-btn erp-list-actions"><i class="bi bi-plus-lg"></i> Charge hinzufügen</a>
    </section>

    <section class="batches-modern-card erp-list-card">
        <div class="premium-table-wrap batches-table-shell erp-list-table-shell">
            <table class="premium-table batches-table">
                <thead><tr><th>Batchnummer</th><th>Produkt</th><th>Menge</th><th>Ablaufdatum</th><th>Wareneingang</th><th>FIFO-Status</th><th>Aktionen</th></tr></thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $expiresAt = $batch->expires_at;
                            $isExpired = $expiresAt && $expiresAt->isPast();
                            $isExpiringSoon = $expiresAt && ! $isExpired && $expiresAt->lte(now()->addDays(30));
                            $quantity = (float) $batch->quantity;
                            $unitLabels = ['gram' => 'Gramm', 'liter' => 'Liter', 'piece' => 'Stück'];
                            $unitLabel = $unitLabels[$batch->product?->unit] ?? ($batch->product?->unit ?? '');
                        @endphp
                        <tr>
                            <td><span class="batch-number-pill"><i class="bi bi-upc-scan"></i>{{ $batch->batch_number }}</span></td>
                            <td><div class="batch-product-cell"><strong>{{ $batch->product?->name ?: '—' }}</strong><span>{{ $batch->product?->product_code ?: 'Kein Produktcode' }}</span></div></td>
                            <td><span class="batch-quantity {{ $quantity <= 0 ? 'empty' : '' }}">{{ number_format($quantity, 2, ',', '.') }}</span><span class="batch-unit">{{ $unitLabel }}</span></td>
                            <td>
                                @if ($expiresAt)
                                    <span class="batch-expiry {{ $isExpired ? 'expired' : ($isExpiringSoon ? 'soon' : 'regular') }}"><i class="bi {{ $isExpired ? 'bi-x-circle' : ($isExpiringSoon ? 'bi-exclamation-circle' : 'bi-calendar-check') }}"></i>{{ $expiresAt->format('d.m.Y') }}</span>
                                @else
                                    <span class="batch-muted">—</span>
                                @endif
                            </td>
                            <td><span class="batch-date">{{ $batch->received_at?->format('d.m.Y') ?: '—' }}</span></td>
                            <td><span class="fifo-status-pill"><i class="bi bi-layers"></i>Ältere zuerst</span></td>
                            <td><div class="premium-actions batches-actions"><a class="premium-icon-btn" href="{{ route('batches.edit', $batch) }}" title="Bearbeiten"><i class="bi bi-pencil-square"></i></a><form method="POST" action="{{ route('batches.destroy', $batch) }}" onsubmit="return confirm('Charge wirklich löschen?');">@csrf @method('DELETE')<button class="premium-icon-btn premium-danger" type="submit" title="Löschen"><i class="bi bi-trash"></i></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="batches-empty-state"><i class="bi bi-layers"></i><strong>Noch keine Chargen vorhanden.</strong><span>Lege eine neue Charge an, um FIFO-Bestand aufzubauen.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="batches-pagination erp-list-pagination">{{ $batches->links() }}</div>
    </section>

    <style>
        .batches-page-actions{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:center;margin-bottom:22px}.batches-search-card{min-width:0;padding:14px;border:1px solid #d8cbb7;border-radius:18px;background:rgba(255,255,255,.82);box-shadow:0 12px 28px rgba(42,36,25,.06)}.batches-search-modern{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.batches-search-field{position:relative;min-width:320px;flex:1}.batches-search-field i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#665f54;font-size:17px;pointer-events:none}.batches-search-field .premium-input{width:100%;min-height:48px;padding-left:42px!important;background:#fffdf8!important}.batches-search-filter{min-width:190px}.batches-search-select{width:100%;min-height:48px;background:#fffdf8!important;font-weight:850;color:#211d17}.batches-exact-search{display:inline-flex;align-items:center;gap:8px;min-height:48px;padding:0 14px;border:1px solid #d8cbb7;border-radius:12px;background:#fffdf8;color:#211d17;font-weight:850;white-space:nowrap;cursor:pointer}.batches-exact-search input{width:18px;height:18px;accent-color:#c9a227}.batches-create-btn{white-space:nowrap}.batches-modern-card{border:1px solid #d8cbb7;border-radius:22px;background:rgba(255,255,255,.86);box-shadow:0 18px 45px rgba(42,36,25,.08);overflow:hidden}.batches-table-shell{margin-top:0!important;border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important}.batches-table{min-width:1080px}.batches-table thead th{padding:18px!important;background:#eee7dc!important;color:#3a332a!important;border-bottom:2px solid #8d8069!important}.batches-table tbody td{padding:18px!important;color:#111!important}.batch-number-pill{display:inline-flex;align-items:center;gap:8px;min-height:34px;padding:7px 11px;border-radius:999px;background:#f3e8be;color:#111;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace;font-size:13px;font-weight:950;white-space:nowrap}.batch-product-cell{display:grid;gap:4px;min-width:0}.batch-product-cell strong{color:#111;font-size:15px;font-weight:950}.batch-product-cell span{color:#665f54;font-size:12px;font-weight:850;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace}.batch-quantity{color:#111;font-size:15px;font-weight:950}.batch-quantity.empty{color:#991b1b}.batch-unit{margin-left:4px;color:#665f54;font-size:12px;font-weight:850}.batch-expiry{display:inline-flex;align-items:center;gap:7px;min-height:32px;padding:7px 11px;border-radius:999px;font-size:13px;font-weight:900;white-space:nowrap}.batch-expiry.regular{background:#dcfce7;color:#166534}.batch-expiry.soon{background:#fef3c7;color:#b45309}.batch-expiry.expired{background:#fee2e2;color:#991b1b}.batch-muted{color:#665f54;font-weight:850}.batch-date{color:#3a332a;font-weight:850;white-space:nowrap}.fifo-status-pill{display:inline-flex;align-items:center;gap:7px;min-height:32px;padding:7px 11px;border-radius:999px;background:#f8f2e7;border:1px solid #d8cbb7;color:#3a332a;font-size:13px;font-weight:950;white-space:nowrap}.batches-actions{justify-content:flex-end;flex-wrap:nowrap;gap:8px}.batches-actions form{margin:0}.premium-icon-btn{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #d8cbb7;border-radius:10px;background:#fffdf8;color:#111;text-decoration:none;cursor:pointer;transition:transform .16s ease,border-color .16s ease,background .16s ease}.premium-icon-btn:hover{transform:translateY(-1px);border-color:#c9a227;background:#fff7dc;color:#111}.premium-icon-btn.premium-danger,.premium-danger{color:#991b1b}.premium-icon-btn.premium-danger:hover,.premium-danger:hover{border-color:#ef4444;background:#fee2e2;color:#991b1b}.batches-empty-state{display:grid;place-items:center;gap:8px;padding:52px 16px;text-align:center;color:#665f54}.batches-empty-state i{font-size:34px;color:#8a6a00}.batches-empty-state strong{color:#111;font-size:17px}.batches-pagination{padding:16px 18px;border-top:1px solid #e7dece;background:#f8f2e7}.batches-table th:nth-child(3),.batches-table td:nth-child(3),.batches-table th:nth-child(4),.batches-table td:nth-child(4),.batches-table th:nth-child(5),.batches-table td:nth-child(5),.batches-table th:nth-child(6),.batches-table td:nth-child(6){text-align:center}.batches-table th:nth-child(7),.batches-table td:nth-child(7){text-align:right}@media(max-width:1050px){.batches-page-actions{grid-template-columns:1fr}.batches-create-btn{justify-self:start}}@media(max-width:700px){.batches-search-modern{display:grid}.batches-search-field{min-width:0}}
    </style>
</x-layouts.premium>
