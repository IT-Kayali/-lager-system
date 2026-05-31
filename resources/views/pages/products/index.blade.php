<x-layouts.premium title="Produkte" subtitle="Produktverwaltung ohne Produktcode, Hersteller und Charge. Preise werden ausschließlich im Tab Preise gepflegt.">
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
            <form method="GET" action="{{ route('products.index') }}" class="premium-search">
                <input
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="premium-input"
                    style="min-width:320px;"
                    placeholder="Suchen nach Bezeichnung, Code-Nummer, Lieferant..."
                >

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if (! empty($search))
                    <a href="{{ route('products.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <a href="{{ route('products.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Produkt hinzufügen
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table premium-wide-table">
                <thead>
                    <tr>
                        <th>Bezeichnung</th>
                        <th>Bezeichnung durch Hersteller</th>
                        <th>Code-Nummer</th>
                        <th>Einheit</th>
                        <th>Lieferant</th>
                        <th>Gesamt</th>
                        <th>Reserviert</th>
                        <th>Verfügbar</th>
                        <th>Mindestbestand</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        @php
                            $statusLabels = [
                                'ok' => 'OK',
                                'low' => 'Niedrig',
                                'critical' => 'Kritisch',
                            ];
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                            </td>

                            <td>{{ $product->manufacturer_designation ?: '—' }}</td>

                            <td>{{ $product->serial_number ?: '—' }}</td>

                            <td>
                                @switch($product->unit)
                                    @case('gram')
                                        Gramm
                                        @break
                                    @case('liter')
                                        Liter
                                        @break
                                    @case('piece')
                                        Stück
                                        @break
                                    @default
                                        {{ $product->unit }}
                                @endswitch
                            </td>

                            <td>{{ $product->supplierRecord?->company_name ?: $product->supplier ?: '—' }}</td>

                            <td>{{ number_format((float) $product->total_stock, 2, ',', '.') }}</td>

                            <td>{{ number_format((float) $product->reserved_stock, 2, ',', '.') }}</td>

                            <td>{{ number_format((float) $product->available_stock, 2, ',', '.') }}</td>

                            <td>{{ number_format((float) $product->minimum_stock, 2, ',', '.') }}</td>

                            <td>
                                <span class="premium-badge {{ $product->stock_status }}">
                                    {{ $statusLabels[$product->stock_status] ?? $product->stock_status }}
                                </span>
                            </td>

                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('branch-withdrawals.create', ['product_id' => $product->id]) }}" title="Filialausgang buchen">
                                            <i class="bi bi-shop"></i>
                                        </a>

                                        <a class="premium-icon-btn" href="{{ route('batches.create', ['product_id' => $product->id]) }}" title="Bestand buchen">
                                        <i class="bi bi-grid"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('products.edit', $product) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Produkt wirklich löschen?');">
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
                            <td colspan="11">
                                <div class="premium-muted">Noch keine Produkte vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $products->links() }}
        </div>
    </section>
</x-layouts.premium>
