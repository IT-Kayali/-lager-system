<x-layouts.premium title="Filialausgang" subtitle="Ware für lokales Geschäft / Filiale entnehmen. Mindestbestand darf hierfür genutzt werden.">
    @if (session('success'))
        <div class="premium-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="branch-header-card">
        <div>
            <div class="branch-eyebrow">Filialbestand</div>
            <h2>Entnahmen protokollieren</h2>
            <p>Diese Buchungen reduzieren den echten Lagerbestand und werden mit Benutzer, Menge und Notiz gespeichert.</p>
        </div>

        <a href="{{ route('branch-withdrawals.create') }}" class="premium-btn gold">
            <i class="bi bi-shop"></i>
            Filialausgang buchen
        </a>
    </section>

    <section class="branch-modern-card">
        @if ($withdrawals->isEmpty())
            <div class="branch-empty-state">
                <i class="bi bi-shop"></i>
                <strong>Noch keine Filialausgänge vorhanden.</strong>
                <span>Sobald Ware für eine Filiale entnommen wird, erscheint die Buchung hier.</span>
            </div>
        @else
            <div class="premium-table-wrap branch-table-shell">
                <table class="premium-table branch-table">
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
                                <td>
                                    <span class="branch-number-pill">
                                        <i class="bi bi-receipt"></i>
                                        {{ $withdrawal->withdrawal_number }}
                                    </span>
                                </td>

                                <td>
                                    <div class="branch-product-cell">
                                        <strong>{{ $withdrawal->product?->name ?? '—' }}</strong>
                                        <span>{{ $withdrawal->product?->product_code ?: 'Kein Produktcode' }}</span>
                                    </div>
                                </td>

                                <td>
                                    <span class="branch-quantity">
                                        {{ number_format((float) $withdrawal->quantity, 2, ',', '.') }}
                                    </span>
                                </td>

                                <td>{{ number_format((float) $withdrawal->stock_before, 2, ',', '.') }}</td>

                                <td>
                                    <span class="branch-stock-after">
                                        {{ number_format((float) $withdrawal->stock_after, 2, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="branch-name-pill">
                                        <i class="bi bi-shop-window"></i>
                                        {{ $withdrawal->branch_name }}
                                    </span>
                                </td>

                                <td>{{ $withdrawal->user?->name ?? 'System' }}</td>

                                <td>
                                    <span class="branch-date">
                                        {{ $withdrawal->created_at?->format('d.m.Y') }}
                                        <small>{{ $withdrawal->created_at?->format('H:i') }}</small>
                                    </span>
                                </td>

                                <td>
                                    <span class="branch-note">
                                        {{ Str::limit($withdrawal->note, 60) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="branch-pagination">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </section>

    <style>
        .branch-header-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
            padding: 26px;
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background:
                radial-gradient(circle at 92% 10%, rgba(212, 170, 32, .18), transparent 30%),
                rgba(255, 255, 255, .86);
            box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
        }

        .branch-eyebrow {
            color: #8a6a00;
            font-size: 12px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .11em;
        }

        .branch-header-card h2 {
            margin: 5px 0 6px;
            color: #111111;
            font-size: 30px;
            font-weight: 950;
            letter-spacing: -.045em;
        }

        .branch-header-card p {
            margin: 0;
            color: #665f54;
            font-weight: 700;
        }

        .branch-modern-card {
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background: rgba(255, 255, 255, .86);
            box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
            overflow: hidden;
        }

        .branch-table-shell {
            margin-top: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .branch-table {
            min-width: 1280px;
        }

        .branch-table thead th {
            padding: 18px 18px !important;
            background: #eee7dc !important;
            color: #3a332a !important;
            border-bottom: 2px solid #8d8069 !important;
        }

        .branch-table tbody td {
            padding: 18px 18px !important;
            color: #111111 !important;
        }

        .branch-number-pill,
        .branch-name-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #f3e8be;
            color: #111111;
            font-size: 13px;
            font-weight: 950;
            white-space: nowrap;
        }

        .branch-number-pill {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        .branch-name-pill {
            background: #f8f2e7;
            border: 1px solid #d8cbb7;
        }

        .branch-product-cell {
            display: grid;
            gap: 4px;
        }

        .branch-product-cell strong {
            font-weight: 950;
        }

        .branch-product-cell span {
            color: #665f54;
            font-size: 12px;
            font-weight: 850;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        .branch-quantity {
            color: #991b1b;
            font-size: 15px;
            font-weight: 950;
        }

        .branch-stock-after {
            color: #166534;
            font-weight: 950;
        }

        .branch-date {
            display: grid;
            gap: 2px;
            font-weight: 900;
        }

        .branch-date small {
            color: #665f54;
            font-size: 12px;
            font-weight: 750;
        }

        .branch-note {
            display: inline-block;
            max-width: 260px;
            color: #3a332a;
            font-weight: 750;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .branch-pagination {
            padding: 16px 18px;
            border-top: 1px solid #e7dece;
            background: #f8f2e7;
        }

        .branch-empty-state {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 64px 16px;
            text-align: center;
            color: #665f54;
        }

        .branch-empty-state i {
            font-size: 38px;
            color: #8a6a00;
        }

        .branch-empty-state strong {
            color: #111111;
            font-size: 18px;
        }

        @media (max-width: 900px) {
            .branch-header-card {
                display: grid;
            }

            .branch-header-card .premium-btn {
                justify-self: start;
            }
        }
    </style>
</x-layouts.premium>
