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
                        <th>Stadt</th>
                        <th>Produkte</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php
                            $supplierUrl = method_exists($supplier, 'getRouteName')
                                ? $supplier->getRouteName()
                                : $supplier;

                            $productCount = $supplier->products_count ?? (method_exists($supplier, 'products') ? $supplier->products()->count() : 0);
                        @endphp

                        <tr>
                            <td>
                                <a class="premium-code supplier-number-link" href="{{ route('suppliers.show', ['supplier' => $supplierUrl]) }}">
                                    {{ $supplier->supplier_number }}
                                </a>
                            </td>

                            <td>
                                <a class="supplier-name-link" href="{{ route('suppliers.show', ['supplier' => $supplierUrl]) }}">
                                    {{ $supplier->company_name }}
                                </a>

                                <div class="premium-muted">
                                    {{ $supplier->contact_person ?: '—' }}
                                </div>
                            </td>

                            <td>
                                <div>{{ $supplier->email ?: '—' }}</div>

                                @if ($supplier->whatsapp ?: $supplier->phone)
                                    <x-whatsapp-link :number="$supplier->whatsapp ?: $supplier->phone" :label="$supplier->whatsapp ?: $supplier->phone" :country-code="$supplier->phone_country_code" />
                                @endif
                            </td>

                            <td>
                                {{ $supplier->city ?: '—' }}
                            </td>

                            <td>
                                <strong>{{ $productCount }}</strong>
                            </td>

                            <td>
                                <div class="premium-actions suppliers-actions">
                                    <a class="premium-icon-btn" href="{{ route('suppliers.show', ['supplier' => $supplierUrl]) }}" title="Lieferant anzeigen">
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
            width: 14%;
        }

        .suppliers-clean-table th:nth-child(2),
        .suppliers-clean-table td:nth-child(2) {
            width: 22%;
        }

        .suppliers-clean-table th:nth-child(3),
        .suppliers-clean-table td:nth-child(3) {
            width: 24%;
        }

        .suppliers-clean-table th:nth-child(4),
        .suppliers-clean-table td:nth-child(4) {
            width: 16%;
        }

        .suppliers-clean-table th:nth-child(5),
        .suppliers-clean-table td:nth-child(5) {
            width: 10%;
            text-align: center !important;
        }

        .suppliers-clean-table th:nth-child(6),
        .suppliers-clean-table td:nth-child(6) {
            width: 14%;
            text-align: right !important;
        }

        .supplier-name-link,
        .supplier-number-link {
            color: #111111 !important;
            text-decoration: none !important;
            font-weight: 950 !important;
        }

        .supplier-name-link:hover,
        .supplier-number-link:hover {
            color: #a9871f !important;
            text-decoration: underline !important;
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
    
        /* SUPPLIER_SEARCH_INLINE_FIX_START */
        .suppliers-toolbar {
            align-items: flex-start !important;
        }

        .suppliers-search {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 10px !important;
            flex-wrap: nowrap !important;
        }

        .suppliers-search .premium-input {
            width: 360px !important;
            max-width: 360px !important;
        }

        .suppliers-search .premium-btn {
            height: 46px !important;
            white-space: nowrap !important;
        }

        @media (max-width: 700px) {
            .suppliers-search {
                flex-wrap: wrap !important;
            }

            .suppliers-search .premium-input {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        /* SUPPLIER_SEARCH_INLINE_FIX_END */

</style>
</x-layouts.premium>
