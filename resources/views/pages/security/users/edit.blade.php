<x-layouts.premium
    title="Benutzer bearbeiten"
    subtitle="Benutzerdaten, Rolle, Aktivstatus und Passwort sicher verwalten."
>
    <section class="security-user-edit-layout">
        <div class="premium-card security-user-edit-main">
            <div class="security-user-edit-head">
                <div class="security-user-edit-icon">
                    <i class="bi bi-person-gear"></i>
                </div>

                <div>
                    <p class="security-user-edit-kicker">Benutzerkonto</p>
                    <h2>{{ $user->name }}</h2>
                    <p>
                        Bearbeite Stammdaten, Rolle, Kontostatus und bei Bedarf das Passwort.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('security.users.update', $user) }}">
                @method('PUT')

                @include('pages.security.users._form')
            </form>
        </div>

        <aside class="premium-card security-user-edit-status">
            <div class="security-user-status-head">
                <span><i class="bi bi-shield-check"></i></span>
                <div>
                    <h2>Kontostatus</h2>
                    <p>Aktueller Sicherheits- und Zugriffsstatus.</p>
                </div>
            </div>

            <div class="security-user-status-list">
                <div class="security-user-status-row">
                    <span class="security-user-status-icon {{ $user->is_active ? 'success' : 'danger' }}">
                        <i class="bi {{ $user->is_active ? 'bi-person-check-fill' : 'bi-person-x-fill' }}"></i>
                    </span>
                    <div>
                        <strong>{{ $user->is_active ? 'Aktiv' : 'Deaktiviert' }}</strong>
                        <small>
                            {{ $user->is_active
                                ? 'Der Benutzer kann sich anmelden.'
                                : 'Der Benutzer kann sich nicht anmelden.' }}
                        </small>
                    </div>
                </div>

                <div class="security-user-status-row">
                    <span class="security-user-status-icon neutral">
                        <i class="bi bi-person-badge"></i>
                    </span>
                    <div>
                        <strong>{{ $user->roleLabel() }}</strong>
                        <small>Aktuell zugewiesene Rolle.</small>
                    </div>
                </div>

                <div class="security-user-status-row">
                    <span class="security-user-status-icon neutral">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <div>
                        <strong>Sitzungsschutz aktiv</strong>
                        <small>
                            Status-, Rollen- und Passwortänderungen beenden bestehende Sitzungen.
                        </small>
                    </div>
                </div>
            </div>
        </aside>
    </section>

    <style>
        .security-user-edit-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(320px, .75fr);
            gap: 22px;
            align-items: start;
        }

        .security-user-edit-main,
        .security-user-edit-status {
            padding: 26px;
        }

        .security-user-edit-head {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            padding-bottom: 22px;
            margin-bottom: 22px;
            border-bottom: 1px solid #e7dece;
        }

        .security-user-edit-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #8a6a00;
            border: 1px solid #dfc96f;
            font-size: 21px;
        }

        .security-user-edit-kicker {
            margin: 0 0 5px;
            color: #8a6a00;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .security-user-edit-head h2,
        .security-user-status-head h2 {
            margin: 0;
            color: #111;
            font-size: 22px;
            font-weight: 950;
        }

        .security-user-edit-head p:not(.security-user-edit-kicker),
        .security-user-status-head p {
            margin: 6px 0 0;
            color: #665f54;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.55;
        }

        .security-user-edit-main .premium-form-grid {
            width: 100% !important;
            max-width: none !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 16px !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .security-user-edit-main .premium-form-field {
            min-width: 0;
            display: grid !important;
            gap: 8px !important;
        }

        .security-user-edit-main .premium-form-field label {
            color: #211d17 !important;
            font-size: 13px !important;
            font-weight: 950 !important;
        }

        .security-user-edit-main .premium-input,
        .security-user-edit-main .premium-select {
            width: 100% !important;
            min-height: 48px !important;
            background-color: #fffdf8 !important;
            color: #111 !important;
            font-weight: 750 !important;
        }

        .security-user-edit-main .premium-alert {
            margin-top: 18px !important;
            border-radius: 14px !important;
            box-shadow: none !important;
        }

        .security-user-edit-main form > div:last-child {
            margin-top: 22px !important;
        }

        .security-user-edit-status {
            position: sticky;
            top: 24px;
        }

        .security-user-status-head {
            display: flex;
            align-items: center;
            gap: 13px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e7dece;
        }

        .security-user-status-head > span {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: #171716;
            color: #ffe690;
            font-size: 18px;
        }

        .security-user-status-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .security-user-status-row {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            padding: 14px;
            border: 1px solid #e7dece;
            border-radius: 14px;
            background: #fffdf8;
        }

        .security-user-status-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
        }

        .security-user-status-icon.success {
            background: #dcfce7;
            color: #166534;
        }

        .security-user-status-icon.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .security-user-status-icon.neutral {
            background: #f3e8be;
            color: #8a6a00;
        }

        .security-user-status-row strong {
            display: block;
            color: #111;
            font-size: 13px;
            font-weight: 950;
        }

        .security-user-status-row small {
            display: block;
            margin-top: 4px;
            color: #665f54;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.45;
        }

        @media (max-width: 1050px) {
            .security-user-edit-layout {
                grid-template-columns: 1fr;
            }

            .security-user-edit-status {
                position: static;
            }
        }

        @media (max-width: 700px) {
            .security-user-edit-main,
            .security-user-edit-status {
                padding: 18px;
            }

            .security-user-edit-main .premium-form-grid {
                grid-template-columns: 1fr !important;
            }

            .security-user-edit-head {
                display: grid;
            }
        }
    </style>
</x-layouts.premium>
