<x-layouts.premium title="Chargen & FIFO" subtitle="Wareneingänge, Batchnummern und FIFO-Lagerlogik.">
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
            <form method="GET" action="{{ route('batches.index') }}" class="premium-search">
                <input
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="premium-input"
                    style="min-width:320px;"
                    placeholder="Suchen nach Charge, Produkt, Hersteller..."
                >

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if (! empty($search))
                    <a href="{{ route('batches.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('batches.create') }}" class="premium-btn gold">
                    <i class="bi bi-plus-lg"></i>
                    Charge hinzufügen
                </a>
            </div>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table premium-wide-table">
                <thead>
                    <tr>
                        <th>Batchnummer</th>
                        <th>Produkt</th>
                        <th>Menge</th>
                        <th>Ablaufdatum</th>
                        <th>Wareneingang</th>
                        <th>FIFO-Reihenfolge</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td>
                                <span class="premium-code">{{ $batch->batch_number }}</span>
                            </td>

                            <td>
                                <strong>{{ $batch->product?->name }}</strong>
                                <div class="premium-muted">{{ $batch->product?->product_code }}</div>
                            </td>

                            <td>
                                {{ number_format((float) $batch->quantity, 2, ',', '.') }}
                                {{ $batch->product?->unit ?? '' }}
                            </td>
                            <td>
                                {{ $batch->expires_at ? $batch->expires_at->format('d.m.Y') : '—' }}
                            </td>

                            <td>{{ $batch->received_at?->format('d.m.Y') ?: '—' }}</td>

                            <td>
                                <span class="premium-badge ok">Ältere zuerst</span>
                            </td>

                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('batches.edit', $batch) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('batches.destroy', $batch) }}" onsubmit="return confirm('Charge wirklich löschen?');">
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
                                <div class="premium-muted">Noch keine Chargen vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $batches->links() }}
        </div>
    </section>
</x-layouts.premium>
