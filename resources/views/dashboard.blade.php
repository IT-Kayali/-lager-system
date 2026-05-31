<x-layouts.premium
    title="Dashboard"
    subtitle="Übersicht über Lager, Reservierungen, Angebote und kritische Artikel."
>
    <section class="premium-grid premium-grid-4">
        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="premium-stat-value">{{ $stats['products'] }}</div>
            <div class="premium-stat-label">Produkte</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="premium-stat-value">{{ $stats['reservations'] }}</div>
            <div class="premium-stat-label">Aktive Reservierungen</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="premium-stat-value">{{ $stats['offers'] }}</div>
            <div class="premium-stat-label">Angebote gesamt</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-exclamation-lg"></i></div>
            <div class="premium-stat-value">{{ $stats['critical'] }}</div>
            <div class="premium-stat-label"><span class="dashboard-kpi-critical-label dashboard-stock-status-critical">Kritische Artikel</span></div>
        </div>
    </section>

    <section class="premium-grid" style="grid-template-columns: 1.3fr .7fr; margin-top:22px;">
        <div class="premium-card">
            <div class="premium-toolbar">
                <div>
                    <h2 style="font-size:20px; font-weight:900; margin:0;">Letzte Aktivitäten</h2>
                    <p class="premium-muted" style="margin:4px 0 0;">Preisänderungen, Angebote, PDFs, Statusänderungen und Lagerbewegungen.</p>
                </div>
                <a href="{{ route('security.index') }}" class="premium-btn">
                    <i class="bi bi-shield-lock"></i>
                    Logs öffnen
                </a>
            </div>

            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Zeit</th>
                            <th>Benutzer</th>
                            <th>Aktion</th>
                            <th>Entity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestActivities as $activity)
                            <tr>
                                <td>{{ $activity->created_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ $activity->user?->name ?? 'System' }}</td>
                                <td><span class="premium-code">{{ $activity->action }}</span></td>
                                <td>{{ $activity->entity ?: '—' }} #{{ $activity->entity_id ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="premium-muted">Noch keine Aktivitäten vorhanden.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="premium-card">
            <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Lagerstatus</h2>

            <div style="display:grid; gap:12px;">
                <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="premium-btn" style="justify-content:space-between;">
                    <span><i class="bi bi-exclamation-triangle"></i> Kritisch</span>
                    <strong>{{ $stats['critical'] }}</strong>
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="premium-btn" style="justify-content:space-between;">
                    <span><i class="bi bi-arrow-down-circle"></i> Niedrig</span>
                    <strong>{{ $stats['low'] }}</strong>
                </a>

                <a href="{{ route('products.index') }}" class="premium-btn gold" style="justify-content:space-between;">
                    <span><i class="bi bi-box-seam"></i> Produkte prüfen</span>
                    <strong>{{ $stats['products'] }}</strong>
                </a>
            </div>
        </div>
    </section>
</x-layouts.premium>
