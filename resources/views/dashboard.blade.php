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
            <div class="premium-stat-label">Reservierungen</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="premium-stat-value">{{ $stats['offers'] }}</div>
            <div class="premium-stat-label">Angebote</div>
        </div>

        <div class="premium-stat-card">
            <div class="premium-stat-icon"><i class="bi bi-exclamation-lg"></i></div>
            <div class="premium-stat-value">{{ $stats['critical'] }}</div>
            <div class="premium-stat-label">Kritische Artikel</div>
        </div>
    </section>

    <section class="premium-card" style="margin-top: 22px;">
        <h2 style="font-size: 20px; font-weight: 900; margin: 0 0 10px;">Phase 1 aktiv</h2>
        <p style="margin: 0; color: rgba(33,33,33,0.66);">
            Auth, Rollenbasis, Premium-Sidebar und geschützte Bereiche sind vorbereitet.
            Als Nächstes bauen wir Produkte, Chargen, Bestand und Mindestbestand-Schutz.
        </p>
    </section>
</x-layouts.premium>
