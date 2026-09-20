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


</x-layouts.premium>