<x-layouts.premium title="Produkte" subtitle="Produktverwaltung ohne Preisfelder. Preise werden ausschließlich im Tab Preise gepflegt.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar">
            <form method="GET" action="{{ route('products.index') }}" class="premium-search">
                <input
                    name="search"
                    value="{{ $search }}"
                    class="premium-input"
                    style="min-width:280px;"
                    placeholder="Suchen nach Code, Name, Hersteller, Lieferant..."
                >

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if ($search)
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
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Produktcode</th>
                        <th>Produktname</th>
                        <th>Hersteller</th>
                        <th>Einheit</th>
                        <th>Lieferant</th>
                        <th>Lagerort</th>
                        <th>Charge</th>
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
                            $firstBatch = $product->batches->first();
                            $statusLabels = [
                                'ok' => 'OK',
                                'low' => 'Niedrig',
                                'critical' => 'Kritisch',
                            ];
                        @endphp

                        <tr>
                            <td><span class="premium-code">{{ $product->product_code }}</span></td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if ($product->serial_number)
                                    <div class="premium-muted">SN: {{ $product->serial_number }}</div>
                                @endif
                            </td>
                            <td>{{ $product->manufacturer ?: '—' }}</td>
                            <td>
                                @switch($product->unit)
                                    @case('gram') Gramm @break
                                    @case('liter') Liter @break
                                    @case('piece') Stück @break
                                    @default {{ $product->unit }}
                                @endswitch
                            </td>
                            <td>{{ $product->supplierRecord?->company_name ?: $product->supplier ?: '—' }}</td>
                            <td>{{ $product->storage_location ?: '—' }}</td>
                            <td>{{ $firstBatch?->batch_number ?: '—' }}</td>
                            <td>{{ number_format($product->total_stock, 3, ',', '.') }}</td>
                            <td>{{ number_format($product->reserved_stock, 3, ',', '.') }}</td>
                            <td>{{ number_format($product->available_stock, 3, ',', '.') }}</td>
                            <td>{{ number_format((float) $product->minimum_stock, 3, ',', '.') }}</td>
                            <td>
                                <span class="premium-badge {{ $product->stock_status }}">
                                    {{ $statusLabels[$product->stock_status] ?? $product->stock_status }}
                                </span>
                            </td>
                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('batches.create', ['product_id' => $product->id]) }}" title="Buchen">
                                        <i class="bi bi-columns-gap"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('products.edit', $product) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Produkt wirklich löschen? Zugehörige Chargen werden ebenfalls gelöscht.');">
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
                            <td colspan="13">
                                <div class="premium-muted">Noch keine Produkte vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $products->links() }}
        </div>
    </section>
</x-layouts.premium>
