<x-layouts.premium title="Kunden" subtitle="Kundenverwaltung mit Kundengruppen Gold, Silber und Diamond.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar">
            <form method="GET" action="{{ route('customers.index') }}" class="premium-search customers-search-inline">
                <input name="search" value="{{ $search }}" class="premium-input" style="min-width:280px;" placeholder="Kunde suchen...">

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search)
                    <a href="{{ route('customers.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <a href="{{ route('customers.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Kunde hinzufügen
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Kundennummer</th>
                        <th>Kunde</th>
                        <th>Gruppe</th>
                        <th>Kontakt</th>
                        <th>Stadt</th>
                        <th>USt-Nummer</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td><span class="premium-code">{{ $customer->customer_number }}</span></td>

                            <td>
                                <strong>{{ $customer->company_name }}</strong>
                                @if ($customer->notes)
                                    <div class="premium-muted">{{ \Illuminate\Support\Str::limit($customer->notes, 60) }}</div>
                                @endif
                            </td>

                            <td>
                                <span class="premium-badge ok">{{ $customer->group?->name ?? '—' }}</span>
                            </td>

                            <td>
                                <div>{{ $customer->email ?: '—' }}</div>

                                @if ($customer->phone)
                                    <x-whatsapp-link :number="$customer->phone" :label="$customer->phone" :country-code="$customer->phone_country_code" />
                                @endif
                            </td>

                            <td>{{ $customer->billing_city ?: $customer->delivery_city ?: $customer->city ?: '—' }}</td>

                            <td>{{ $customer->vat_number ?: '—' }}</td>

                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('customers.show', $customer) }}" title="Kundenprofil anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('customers.edit', $customer) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Kunde wirklich löschen?');">
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
                            <td colspan="7">
                                <div class="premium-muted">Noch keine Kunden vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $customers->links() }}
        </div>
    </section>
<style>
        /* CUSTOMERS_SEARCH_INLINE_FIX_START */
        .customers-search-inline {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 10px !important;
            flex-wrap: nowrap !important;
        }

        .customers-search-inline .premium-input {
            width: 360px !important;
            max-width: 360px !important;
        }

        .customers-search-inline .premium-btn {
            height: 46px !important;
            white-space: nowrap !important;
        }

        @media (max-width: 700px) {
            .customers-search-inline {
                flex-wrap: wrap !important;
            }

            .customers-search-inline .premium-input {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        /* CUSTOMERS_SEARCH_INLINE_FIX_END */

</style>
</x-layouts.premium>
