@php
    $loginBackgroundPath = \App\Models\ApplicationSetting::loginBackgroundPath();
    $loginBackgroundUrl = $loginBackgroundPath ? route('login.background', [], false) : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · {{ config('app.name', 'Lagerverwaltung') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="premium-login-page" @if ($loginBackgroundUrl) style="--premium-login-bg: url('{{ $loginBackgroundUrl }}');" @endif>
    <main class="premium-login-shell">
        <section class="premium-login-showcase" aria-label="Lagerverwaltung Übersicht">
            <div class="premium-login-brand-card">
                <div class="premium-login-mark">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <span>Premium ERP</span>
                    <strong>Lagerverwaltungssystem</strong>
                </div>
            </div>

            <div class="premium-login-copy">
                <span class="premium-login-eyebrow">Sicherer Zugriff</span>
                <h1>Modernes Lager, Angebote und Vertrieb an einem Ort.</h1>
                <p>Einloggen, Bestände prüfen, Angebote steuern und kritische Artikel sofort im Blick behalten.</p>
            </div>

            <div class="premium-login-metrics" aria-label="Systemvorteile">
                <div>
                    <strong>Live</strong>
                    <span>Bestandsstatus</span>
                </div>
                <div>
                    <strong>PDF</strong>
                    <span>Angebote & Rechnungen</span>
                </div>
                <div>
                    <strong>ERP</strong>
                    <span>Premium Workflow</span>
                </div>
            </div>
        </section>

        <section class="premium-login-panel" aria-label="Anmeldung">
            <form method="POST" action="{{ route('login') }}" class="premium-login-card">
                @csrf

                <div class="premium-login-card-header">
                    <div class="premium-login-card-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <span>Anmeldung</span>
                    <h2>Willkommen zurück</h2>
                    <p>Bitte melde dich mit deinem Benutzerkonto an.</p>
                </div>

                <div class="premium-login-field">
                    <label for="email">E-Mail-Adresse</label>
                    <div class="premium-login-input">
                        <i class="bi bi-envelope"></i>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="name@firma.de"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    @error('email')
                        <div class="premium-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-login-field">
                    <label for="password">Passwort</label>
                    <div class="premium-login-input">
                        <i class="bi bi-lock"></i>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Passwort eingeben"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    @error('password')
                        <div class="premium-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="premium-login-submit">
                    <span>Einloggen</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <footer class="premium-login-footer">
                Erstellt von
                <a href="https://it-kayali.de" target="_blank" rel="noopener noreferrer">IT-Kayali</a>
            </footer>
        </section>
    </main>
</body>
</html>
