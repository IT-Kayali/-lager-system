<x-layouts.premium title="Lieferanten" subtitle="Verwalte Lieferanten, Kontakte und Produktzuordnungen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="suppliers-toolbar-card">
        <form method="GET" action="{{ route('suppliers.index') }}" class="suppliers-filter-form">
            <div class="suppliers-search-field">
                <i class="bi bi-search"></i>
                <input
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="premium-input"
                    placeholder="Lieferant, Ansprechpartner, E-Mail oder Stadt suchen..."
                >
            </div>

            <button class="premium-btn dark" type="submit">
                <i class="bi bi-funnel"></i>
                Filtern
            </button>

            @if (! empty($search))
                <a href="{{ route('suppliers.index') }}" class="premium-btn">
                    <i class="bi bi-x-lg"></i>
                    Zurücksetzen
                </a>
            @endif
        </form>

        <a href="{{ route('suppliers.create') }}" class="premium-btn gold suppliers-add-btn">
            <i class="bi bi-truck"></i>
            Lieferant hinzufügen
        </a>
    </section>

    <section class="suppliers-table-card">
        <div class="suppliers-table-header">
            <div>
                <h2>Lieferantenübersicht</h2>
                <p>{{ $suppliers->total() }} Lieferanten im System</p>
            </div>

            <div class="suppliers-table-meta">
                <i class="bi bi-buildings"></i>
            </div>
        </div>

        <div class="premium-table-wrap suppliers-table-wrap">
            <table class="premium-table suppliers-table">
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

                                <div class="premium-muted supplier-contact-person">
                                    {{ $supplier->contact_person ?: 'Kein Ansprechpartner' }}
                                </div>
                            </td>

                            <td>
                                <div class="supplier-contact-stack">
                                    @if ($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}">
                                            <i class="bi bi-envelope"></i>
                                            {{ $supplier->email }}
                                        </a>
                                    @else
                                        <span class="premium-muted">Keine E-Mail</span>
                                    @endif

                                    @if ($supplier->whatsapp ?: $supplier->phone)
                                        <x-whatsapp-link :number="$supplier->whatsapp ?: $supplier->phone" :label="$supplier->whatsapp ?: $supplier->phone" :country-code="$supplier->phone_country_code" />
                                    @endif
                                </div>
                            </td>

                            <td>{{ $supplier->city ?: '—' }}</td>

                            <td>
                                <span class="supplier-products-pill">
                                    <i class="bi bi-box-seam"></i>
                                    {{ $productCount }}
                                </span>
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
                                <div class="suppliers-empty-state">
                                    <i class="bi bi-truck"></i>
                                    <strong>Noch keine Lieferanten vorhanden.</strong>
                                    <span>Lege Lieferanten an, damit Produkte sauber zugeordnet werden können.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="suppliers-pagination">
            {{ $suppliers->links() }}
        </div>
    </section>

    <style>
        .suppliers-toolbar-card,
        .suppliers-table-card {
            background: rgba(255, 255, 255, .86);
            border: 1px solid #d9c9ae;
            border-radius: 18px;
            box-shadow: 0 18px 48px rgba(33, 29, 23, .08);
        }

        .suppliers-toolbar-card {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .suppliers-filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .suppliers-search-field {
            position: relative;
            flex: 1;
            max-width: 560px;
        }

        .suppliers-search-field i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #75694f;
            font-size: 16px;
            pointer-events: none;
        }

        .suppliers-search-field .premium-input {
            width: 100%;
            padding-left: 44px !important;
        }

        .suppliers-add-btn {
            white-space: nowrap;
        }

        .suppliers-table-card {
            overflow: hidden;
        }

        .suppliers-table-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 22px 24px 18px;
            border-bottom: 1px solid #e7dece;
        }

        .suppliers-table-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 950;
            letter-spacing: -.02em;
            color: #121212;
        }

        .suppliers-table-header p {
            margin: 4px 0 0;
            color: #6f665b;
            font-size: 14px;
            font-weight: 700;
        }

        .suppliers-table-meta {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #7b5c00;
            font-size: 20px;
        }

        .suppliers-table-wrap {
            margin-top: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

        .suppliers-table {
            min-width: 980px;
        }

        .supplier-name-link,
        .supplier-number-link {
            color: #111111 !important;
            text-decoration: none !important;
            font-weight: 950 !important;
        }

        .supplier-name-link {
            display: inline-block;
            font-size: 16px;
        }

        .supplier-name-link:hover,
        .supplier-number-link:hover {
            color: #a9871f !important;
            text-decoration: underline !important;
        }

        .supplier-contact-person {
            margin-top: 4px;
        }

        .supplier-contact-stack {
            display: grid;
            gap: 5px;
        }

        .supplier-contact-stack a {
            color: #211d17;
            font-weight: 800;
            text-decoration: none;
        }

        .supplier-contact-stack a:hover {
            color: #9a7300;
        }

        .supplier-contact-stack i {
            margin-right: 6px;
            color: #7b5c00;
        }

        .supplier-products-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 58px;
            min-height: 32px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #f4efe5;
            border: 1px solid #d7c7ab;
            color: #211d17;
            font-weight: 950;
        }

        .suppliers-table th:nth-child(5),
        .suppliers-table td:nth-child(5) {
            text-align: center !important;
        }

        .suppliers-actions {
            justify-content: flex-end !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
        }

        .suppliers-actions form {
            margin: 0 !important;
        }

        .suppliers-empty-state {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 34px 18px;
            color: #6f665b;
            text-align: center;
        }

        .suppliers-empty-state i {
            width: 50px;
            height: 50px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: #f3e8be;
            color: #7b5c00;
            font-size: 22px;
        }

        .suppliers-empty-state strong {
            color: #111;
            font-size: 18px;
        }

        .suppliers-pagination {
            padding: 16px 20px 20px;
            border-top: 1px solid #e7dece;
        }

        @media (max-width: 980px) {
            .suppliers-toolbar-card,
            .suppliers-filter-form {
                align-items: stretch;
                flex-direction: column;
            }

            .suppliers-search-field {
                max-width: none;
            }
        }
    </style>
</x-layouts.premium>
