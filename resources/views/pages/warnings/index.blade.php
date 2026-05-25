<x-layouts.premium title="Warnungen" subtitle="Nur Produkte mit niedrigem oder kritischem Bestand werden hier angezeigt.">
    <section class="premium-grid premium-grid-4" style="margin-bottom:22px;">
        <a href="{{ route('products.index') }}" class="premium-stat-card" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="premium-stat-value">{{ $summary['all'] }}</div>
            <div class="premium-stat-label">Alle Produkte</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="premium-stat-card" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-exclamation-lg"></i></div>
            <div class="premium-stat-value">{{ $summary['warning'] }}</div>
            <div class="premium-stat-label">Warnungen gesamt</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="premium-stat-card" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="premium-stat-value">{{ $summary['low'] }}</div>
            <div class="premium-stat-label">Niedrig</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="premium-stat-card" style="text-decoration:none;color:inherit;">
            <div class="premium-stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="premium-stat-value">{{ $summary['critical'] }}</div>
            <div class="premium-stat-label">Kritisch</div>
        </a>
    </section>

    <section class="premium-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Bestandswarnungen</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    OK-Produkte werden hier nicht angezeigt. Statuslogik: Niedrig unter Mindestbestand, Kritisch bei maximal 40% vom Mindestbestand.
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('warnings.index', ['filter' => 'warning']) }}" class="premium-btn {{ $filter === 'warning' ? 'gold' : '' }}">
                    Warnungen
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="premium-btn {{ $filter === 'low' ? 'gold' : '' }}">
                    Niedrig
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="premium-btn {{ $filter === 'critical' ? 'gold' : '' }}">
                    Kritisch
                </a>

                <a href="{{ route('products.index') }}" class="premium-btn">
                    <i class="bi bi-box-seam"></i>
                    Alle Produkte öffnen
                </a>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="premium-placeholder">
                Keine Bestandswarnungen vorhanden. Alle Produkte sind aktuell im grünen Bereich.
            </div>
        @else
            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Hersteller</th>
                            <th>Gesamt</th>
                            <th></th>
                            <th>Verfügbar</th>
                            <th>Mindestbestand</th>
                            <th>Max. reservierbar</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $row)
                            @php
                                $product = $row['product'];
                                $labels = [
                                    'ok' => 'OK',
                                    'low' => 'Niedrig',
                                    'critical' => 'Kritisch',
                                ];
                            @endphp

                            <tr>
                                <td>{{ number_format($row['total_stock'], 3, ',', '.') }}</td>
                                <td>{{ number_format($row['reserved_stock'], 3, ',', '.') }}</td>
                                <td>{{ number_format($row['available_stock'], 3, ',', '.') }}</td>
                                <td>{{ number_format($row['minimum_stock'], 3, ',', '.') }}</td>
                                <td>{{ number_format($row['max_reservable'], 3, ',', '.') }}</td>
                                <td>
                                    <span class="premium-badge {{ $row['status'] }}">
                                        {{ $labels[$row['status']] ?? $row['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="premium-actions">
                                        <a class="premium-icon-btn" href="{{ route('products.edit', $product) }}" title="Produkt bearbeiten">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <a class="premium-icon-btn" href="{{ route('batches.create', ['product_id' => $product->id]) }}" title="Bestand buchen">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.premium>
