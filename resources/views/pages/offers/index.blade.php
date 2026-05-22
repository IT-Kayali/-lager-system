<x-layouts.premium title="Angebote & Rechnungen" subtitle="Angebote mit automatischer Preisberechnung und Reservierung.">
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
            <form method="GET" action="{{ route('offers.index') }}" class="premium-search">
                <input
                    name="search"
                    value="{{ $search }}"
                    class="premium-input"
                    style="min-width:280px;"
                    placeholder="Suchen nach Angebotsnummer oder Kunde..."
                >

                <select name="status" class="premium-select" style="max-width: 230px;">
                    <option value="">Alle Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search || $selectedStatus)
                    <a href="{{ route('offers.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <a href="{{ route('offers.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Neues Angebot
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Angebot</th>
                        <th>Kunde</th>
                        <th>Kundengruppe</th>
                        <th>Status</th>
                        <th>Positionen</th>
                        <th>Gesamtbetrag</th>
                        <th>Reserviert bis</th>
                        <th>Vorlage</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td>
                                <span class="premium-code">{{ $offer->offer_number }}</span>
                                <div class="premium-muted">{{ $offer->created_at?->format('d.m.Y H:i') }}</div>
                            </td>
                            <td>
                                <strong>{{ $offer->customer?->company_name }}</strong>
                                <div class="premium-muted">{{ $offer->customer?->customer_number }}</div>
                            </td>
                            <td>
                                <span class="premium-badge ok">{{ $offer->customer?->group?->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="premium-badge {{ $offer->isReservationActive() ? 'low' : ($offer->status === 'cancelled' ? 'critical' : 'ok') }}">
                                    {{ $offer->statusLabel() }}
                                </span>
                            </td>
                            <td>{{ $offer->items->count() }}</td>
                            <td>{{ number_format((float) $offer->total, 2, ',', '.') }} €</td>
                            <td>
                                @if ($offer->reserved_until && $offer->isReservationActive())
                                    {{ $offer->reserved_until->format('d.m.Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $offer->templateLabel() }}</td>
                            <td>
                                <div class="premium-actions">
                                    @if ($offer->isReservationActive())
                                        <form method="POST" action="{{ route('offers.cancel', $offer) }}" onsubmit="return confirm('Angebot stornieren und Reservierung freigeben?');">
                                            @csrf
                                            <button class="premium-icon-btn premium-danger" type="submit" title="Stornieren">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="premium-muted">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="premium-muted">Noch keine Angebote vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $offers->links() }}
        </div>
    </section>
</x-layouts.premium>
