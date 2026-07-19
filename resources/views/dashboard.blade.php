<x-layouts.premium
    title="Dashboard"
    subtitle="Übersicht über Lager, Reservierungen, Angebote und kritische Artikel."
>

    <section class="dashboard-kpi-grid">
        <a href="{{ route('products.index') }}" class="dashboard-kpi-card">
            <div class="dashboard-kpi-top">
                <span class="dashboard-kpi-icon">
                    <i class="bi bi-box-seam"></i>
                </span>
                <span class="dashboard-kpi-pill">Inventar</span>
            </div>

            <div class="dashboard-kpi-value">{{ $stats['products'] }}</div>
            <div class="dashboard-kpi-label">Produkte</div>
        </a>

        <a href="{{ route('offers.index') }}" class="dashboard-kpi-card">
            <div class="dashboard-kpi-top">
                <span class="dashboard-kpi-icon">
                    <i class="bi bi-calendar-check"></i>
                </span>
                <span class="dashboard-kpi-pill">Reserviert</span>
            </div>

            <div class="dashboard-kpi-value">{{ $stats['reservations'] }}</div>
            <div class="dashboard-kpi-label">Aktive Reservierungen</div>
        </a>

        <a href="{{ route('offers.index') }}" class="dashboard-kpi-card">
            <div class="dashboard-kpi-top">
                <span class="dashboard-kpi-icon">
                    <i class="bi bi-receipt-cutoff"></i>
                </span>
                <span class="dashboard-kpi-pill">Vertrieb</span>
            </div>

            <div class="dashboard-kpi-value">{{ $stats['offers'] }}</div>
            <div class="dashboard-kpi-label">Angebote gesamt</div>
        </a>

        <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="dashboard-kpi-card dashboard-kpi-card-danger">
            <div class="dashboard-kpi-top">
                <span class="dashboard-kpi-icon dashboard-kpi-icon-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </span>
                <span class="dashboard-kpi-pill dashboard-kpi-pill-danger">Achtung</span>
            </div>

            <div class="dashboard-kpi-value">{{ $stats['critical'] }}</div>
            <div class="dashboard-kpi-label">Kritische Artikel</div>
        </a>
    </section>

    <section class="dashboard-main-grid">
        <div class="dashboard-panel dashboard-activities-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h2>Letzte Aktivitäten</h2>
                    <p>Preisänderungen, Angebote, PDFs, Statusänderungen und Lagerbewegungen.</p>
                </div>

                <a href="{{ route('security.index') }}" class="dashboard-panel-link">
                    Logs öffnen
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="dashboard-activity-list">
                @forelse ($latestActivities as $activity)
                    <div class="dashboard-activity-row">
                        <div class="dashboard-activity-time">
                            <strong>{{ $activity->created_at?->format('d.m.Y') }}</strong>
                            <span>{{ $activity->created_at?->format('H:i') }}</span>
                        </div>

                        <div class="dashboard-activity-user">
                            <span class="dashboard-avatar">
                                {{ strtoupper(mb_substr($activity->user?->name ?? 'S', 0, 1)) }}
                            </span>
                            <span>{{ $activity->user?->name ?? 'System' }}</span>
                        </div>

                        <div class="dashboard-activity-action">
                            {{ $activity->action }}
                        </div>

                        <div class="dashboard-activity-entity">
                            {{ $activity->entity ?: '—' }} #{{ $activity->entity_id ?: '—' }}
                        </div>
                    </div>
                @empty
                    <div class="dashboard-empty-state">
                        <i class="bi bi-clock-history"></i>
                        <strong>Noch keine Aktivitäten vorhanden.</strong>
                        <span>Sobald Aktionen ausgeführt werden, erscheinen sie hier.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="dashboard-panel dashboard-stock-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h2>Lagerstatus</h2>
                    <p>Bestandslage und Warnungen.</p>
                </div>

                <span class="dashboard-panel-menu">
                    <i class="bi bi-three-dots-vertical"></i>
                </span>
            </div>

            <div class="dashboard-stock-list">
                <a href="{{ route('warnings.index', ['filter' => 'critical']) }}" class="dashboard-stock-item dashboard-stock-critical">
                    <span class="dashboard-stock-marker"></span>

                    <span class="dashboard-stock-copy">
                        <strong>Kritisch</strong>
                        <small>Sofortiges Handeln erforderlich</small>
                    </span>

                    <span class="dashboard-stock-count">{{ $stats['critical'] }}</span>
                </a>

                <a href="{{ route('warnings.index', ['filter' => 'low']) }}" class="dashboard-stock-item dashboard-stock-low">
                    <span class="dashboard-stock-marker"></span>

                    <span class="dashboard-stock-copy">
                        <strong>Niedrig</strong>
                        <small>Baldige Nachbestellung</small>
                    </span>

                    <span class="dashboard-stock-count">{{ $stats['low'] }}</span>
                </a>

                <a href="{{ route('products.index') }}" class="dashboard-stock-item dashboard-stock-products">
                    <span class="dashboard-stock-marker"></span>

                    <span class="dashboard-stock-copy">
                        <strong>Produkte prüfen</strong>
                        <small>Reguläre Bestandskontrolle</small>
                    </span>

                    <span class="dashboard-stock-count">{{ $stats['products'] }}</span>
                </a>
            </div>

            <a href="{{ route('warnings.index') }}" class="dashboard-detail-link">
                Detailbericht öffnen
                <i class="bi bi-arrow-right"></i>
            </a>
        </aside>
    </section>

    <style>

        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .dashboard-kpi-card {
            min-height: 172px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px;
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background: rgba(255, 255, 255, .86);
            color: #111111;
            text-decoration: none;
            box-shadow: 0 16px 36px rgba(42, 36, 25, .07);
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .dashboard-kpi-card:hover {
            transform: translateY(-2px);
            border-color: #c9a227;
            box-shadow: 0 22px 44px rgba(42, 36, 25, .12);
            color: #111111;
        }

        .dashboard-kpi-card-danger {
            border-left: 5px solid #c91f1f;
        }

        .dashboard-kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .dashboard-kpi-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #111111;
            font-size: 22px;
        }

        .dashboard-kpi-icon-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .dashboard-kpi-pill {
            padding: 7px 11px;
            border-radius: 999px;
            background: #f6f1e7;
            color: #665f54;
            font-size: 11px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .dashboard-kpi-pill-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .dashboard-kpi-value {
            margin-top: 22px;
            font-size: 35px;
            font-weight: 950;
            line-height: 1;
            letter-spacing: -.04em;
        }

        .dashboard-kpi-label {
            margin-top: 9px;
            color: #665f54;
            font-size: 15px;
            font-weight: 750;
        }

        .dashboard-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(340px, .55fr);
            gap: 22px;
            margin-top: 22px;
            align-items: stretch;
        }

        .dashboard-panel {
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
            overflow: hidden;
        }

        .dashboard-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 26px 18px;
        }

        .dashboard-panel-header h2 {
            margin: 0;
            color: #111111;
            font-size: 24px;
            font-weight: 950;
            letter-spacing: -.035em;
        }

        .dashboard-panel-header p {
            margin: 7px 0 0;
            color: #665f54;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
        }

        .dashboard-panel-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #8a6a00;
            font-size: 14px;
            font-weight: 950;
            text-decoration: none;
            white-space: nowrap;
        }

        .dashboard-panel-link:hover {
            color: #111111;
        }

        .dashboard-activity-list {
            padding: 0 18px 18px;
        }

        .dashboard-activity-row {
            display: grid;
            grid-template-columns: 150px 190px minmax(0, 1fr) 130px;
            gap: 18px;
            align-items: center;
            padding: 15px 12px;
            border-top: 1px solid #e7dece;
        }

        .dashboard-activity-row:first-child {
            border-top-color: #8d8069;
        }

        .dashboard-activity-time {
            display: grid;
            gap: 2px;
            color: #111111;
            font-size: 13px;
            font-weight: 850;
        }

        .dashboard-activity-time span {
            color: #665f54;
            font-weight: 750;
        }

        .dashboard-activity-user {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            font-weight: 850;
        }

        .dashboard-avatar {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 32px;
            border-radius: 999px;
            background: #eee7dc;
            color: #111111;
            font-size: 12px;
            font-weight: 950;
        }

        .dashboard-activity-action {
            min-width: 0;
            color: #111111;
            font-weight: 950;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dashboard-activity-entity {
            color: #111111;
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
        }

        .dashboard-empty-state {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 42px 16px;
            text-align: center;
            color: #665f54;
        }

        .dashboard-empty-state i {
            font-size: 30px;
            color: #8a6a00;
        }

        .dashboard-stock-panel {
            padding-bottom: 24px;
        }

        .dashboard-panel-menu {
            color: #111111;
            font-size: 20px;
        }

        .dashboard-stock-list {
            display: grid;
            gap: 14px;
            padding: 0 24px 24px;
        }

        .dashboard-stock-item {
            display: grid;
            grid-template-columns: 8px minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            padding: 18px;
            border-radius: 16px;
            border: 1px solid #e2d6c5;
            background: #f8f2e7;
            color: #111111;
            text-decoration: none;
            transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease;
        }

        .dashboard-stock-item:hover {
            transform: translateY(-1px);
            border-color: #c9a227;
            box-shadow: 0 14px 26px rgba(42, 36, 25, .10);
            color: #111111;
        }

        .dashboard-stock-marker {
            width: 8px;
            height: 48px;
            border-radius: 999px;
            background: #d4aa20;
        }

        .dashboard-stock-critical .dashboard-stock-marker {
            background: #c91f1f;
        }

        .dashboard-stock-low .dashboard-stock-marker {
            background: #e07a00;
        }

        .dashboard-stock-products .dashboard-stock-marker {
            background: #d4aa20;
        }

        .dashboard-stock-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .dashboard-stock-copy strong {
            font-size: 18px;
            font-weight: 930;
        }

        .dashboard-stock-copy small {
            color: #665f54;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.35;
        }

        .dashboard-stock-count {
            min-width: 42px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, .72);
            color: #111111;
            font-weight: 950;
        }

        .dashboard-detail-link {
            margin: 0 24px;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 14px;
            border: 1px solid #c9b895;
            color: #111111;
            text-decoration: none;
            font-weight: 900;
        }

        .dashboard-detail-link:hover {
            border-color: #8a6a00;
            background: #fff8df;
            color: #111111;
        }

        @media (max-width: 1250px) {
            .dashboard-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 800px) {

            .dashboard-kpi-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-activity-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .dashboard-activity-entity {
                text-align: left;
            }
        }
    </style>
</x-layouts.premium>
