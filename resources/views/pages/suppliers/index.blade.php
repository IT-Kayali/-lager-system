<x-layouts.premium title="Lieferanten" subtitle="Lieferanten verwalten und Produkten zuordnen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar suppliers-toolbar">
            <form method="GET" action="{{ route('suppliers.index') }}" class="suppliers-search">
                <input
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="premium-input"
                    placeholder="Lieferant suchen..."
                >

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if (! empty($search))
                    <a href="{{ route('suppliers.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <a href="{{ route('suppliers.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Lieferant hinzufügen
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table suppliers-clean-table">
                <thead>
                    <tr>
                        <th>Nummer</th>
                        <th>Lieferant</th>
                        <th>Kontakt</th>
                        <th>Adresse</th>
                        <th>Produkte</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <span class="premium-code">{{ $supplier->supplier_number }}</span>
                            </td>

                            <td>
                                <strong>{{ $supplier->company_name }}</strong>
                                <div class="premium-muted">{{ $supplier->contact_person ?: '—' }}</div>
                            </td>

                            <td>
                                <div>{{ $supplier->email ?: '—' }}</div>

                                @if ($supplier->whatsapp ?: $supplier->phone)
                                    <x-whatsapp-link :number="$supplier->whatsapp ?: $supplier->phone" label="WhatsApp" :country-code="$supplier->phone_country_code" />
                                @else
                                    <div class="premium-muted">—</div>
                                @endif
                            </td>

                            <td>
                                @if ($supplier->fullAddress())
                                    {!! nl2br(e($supplier->fullAddress())) !!}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                <strong>{{ $supplier->products_count }}</strong>
                            </td>

                            <td>
                                <div class="premium-actions suppliers-actions">
                                    <a class="premium-icon-btn" href="{{ route('suppliers.show', ['supplier' => $supplier->getRouteName()]) }}" title="Lieferant anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('suppliers.edit', $supplier) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Lieferant wirklich löschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="premium-icon-btn premium-danger" type="submit" title="Löschen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="premium-muted">Noch keine Lieferanten vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $suppliers->links() }}
        </div>
    </section>

    <style>
        .suppliers-toolbar {
            align-items: flex-start;
            gap: 16px;
        }

        .suppliers-search {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .suppliers-search .premium-input {
            width: 390px;
            max-width: 100%;
        }

        .suppliers-clean-table {
            min-width: 980px;
        }

        .suppliers-clean-table th:nth-child(1),
        .suppliers-clean-table td:nth-child(1) {
            width: 13%;
        }

        .suppliers-clean-table th:nth-child(2),
        .suppliers-clean-table td:nth-child(2) {
            width: 18%;
        }

        .suppliers-clean-table th:nth-child(3),
        .suppliers-clean-table td:nth-child(3) {
            width: 22%;
        }

        .suppliers-clean-table th:nth-child(4),
        .suppliers-clean-table td:nth-child(4) {
            width: 27%;
        }

        .suppliers-clean-table th:nth-child(5),
        .suppliers-clean-table td:nth-child(5) {
            width: 8%;
            text-align: center !important;
        }

        .suppliers-clean-table th:nth-child(6),
        .suppliers-clean-table td:nth-child(6) {
            width: 12%;
            text-align: right !important;
        }

        .suppliers-actions {
            justify-content: flex-end !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
        }

        .suppliers-actions form {
            margin: 0 !important;
        }

        @media (max-width: 900px) {
            .suppliers-toolbar {
                display: grid;
            }

            .suppliers-search,
            .suppliers-search .premium-input {
                width: 100%;
            }
        }
    </style>
</x-layouts.premium>
