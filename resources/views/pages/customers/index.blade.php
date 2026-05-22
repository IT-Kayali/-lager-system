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
            <form method="GET" action="{{ route('customers.index') }}" class="premium-search">
                <input
                    name="search"
                    value="{{ $search }}"
                    class="premium-input"
                    style="min-width:280px;"
                    placeholder="Suchen nach Nummer, Name, E-Mail, Telefon..."
                >

                <select name="group" class="premium-select" style="max-width: 190px;">
                    <option value="">Alle Gruppen</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->slug }}" @selected($selectedGroup === $group->slug)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search || $selectedGroup)
                    <a href="{{ route('customers.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <a href="{{ route('customers.create') }}" class="premium-btn gold">
                <i class="bi bi-person-plus"></i>
                Kunde hinzufügen
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Kundennummer</th>
                        <th>Name / Firma</th>
                        <th>Gruppe</th>
                        <th>E-Mail</th>
                        <th>Telefon / WhatsApp</th>
                        <th>Stadt</th>
                        <th>USt-Nummer</th>
                        <th>Erstellt</th>
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
                                    <div class="premium-muted">Notiz vorhanden</div>
                                @endif
                            </td>
                            <td>
                                <span class="premium-badge ok">
                                    {{ $customer->group?->name ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $customer->email ?: '—' }}</td>
                            <td>{{ $customer->phone ?: '—' }}</td>
                            <td>{{ $customer->city ?: '—' }}</td>
                            <td>{{ $customer->vat_number ?: '—' }}</td>
                            <td>{{ $customer->created_at?->format('d.m.Y') }}</td>
                            <td>
                                <div class="premium-actions">
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
                            <td colspan="9">
                                <div class="premium-muted">Noch keine Kunden vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $customers->links() }}
        </div>
    </section>
</x-layouts.premium>
