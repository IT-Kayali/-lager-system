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
        <div class="premium-toolbar">
            <form method="GET" action="{{ route('suppliers.index') }}" class="premium-search">
                <input name="search" value="{{ $search }}" class="premium-input" style="min-width:280px;" placeholder="Lieferant suchen...">

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search)
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
            <table class="premium-table">
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
                            <td><span class="premium-code">{{ $supplier->supplier_number }}</span></td>

                            <td>
                                <strong>{{ $supplier->company_name }}</strong>
                                <div class="premium-muted">{{ $supplier->contact_person ?: '—' }}</div>
                            </td>

                            <td>
                                <div>{{ $supplier->email ?: '—' }}</div>

                                @if ($supplier->phone)
                                    <div class="premium-muted">{{ $supplier->phone }}</div>
                                @endif

                                @if ($supplier->whatsapp ?: $supplier->phone)
                                    <x-whatsapp-link :number="$supplier->whatsapp ?: $supplier->phone" :label="$supplier->whatsapp ?: $supplier->phone" :country-code="$supplier->whatsapp ? $supplier->whatsapp_country_code : $supplier->phone_country_code" />
                                @endif
                            </td>

                            <td>
                                @if ($supplier->fullAddress())
                                    {!! nl2br(e($supplier->fullAddress())) !!}
                                @else
                                    —
                                @endif
                            </td>

                            <td>{{ $supplier->products_count }}</td>

                            <td>
                                <div class="premium-actions">
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
</x-layouts.premium>
