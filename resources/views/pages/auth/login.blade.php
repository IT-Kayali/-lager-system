@php
    $loginBackgroundPath = \App\Models\ApplicationSetting::loginBackgroundPath();
    $loginBackgroundUrl = $loginBackgroundPath ? route('login.background', [], false) : null;
    $loginLogoPath = \App\Models\ApplicationSetting::loginLogoPath();
    $loginLogoUrl = $loginLogoPath ? route('login.logo', [], false) : null;
    $loginEyebrow = \App\Models\ApplicationSetting::loginEyebrow();
    $loginTitle = \App\Models\ApplicationSetting::loginTitle();
    $loginSubtitle = \App\Models\ApplicationSetting::loginSubtitle();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.browser-branding', ['pageTitle' => 'Login'])

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/login-page.css') }}">
    <link rel="stylesheet" href="{{ route('login.theme.css') }}">
</head>
<body>
    <main class="login-page">
        <div class="login-shell">
            <section class="login-info" aria-label="Lagerverwaltung Übersicht">
                @if ($loginLogoUrl)
                    <img class="login-brand-logo" src="{{ $loginLogoUrl }}" alt="Logo">
                @else
                    <div class="login-brand">
                        <div class="login-brand-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <small>Premium ERP</small>
                            <strong>Lagerverwaltungssystem</strong>
                        </div>
                    </div>
                @endif
                <span class="login-kicker">{{ $loginEyebrow }}</span>
                <h1>{{ $loginTitle }}</h1>
                <p>{{ $loginSubtitle }}</p>
            </section>

            <section class="login-card-wrap" aria-label="Anmeldung">
                <form method="POST" action="{{ route('login') }}" class="login-card">
                    @csrf

                    <div class="login-card-head">
                        <div class="login-card-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <span class="login-card-kicker">Anmeldung</span>
                        <h2>Willkommen zurück</h2>
                        <p>Bitte melde dich mit deinem Benutzerkonto an.</p>
                    </div>

                    <div class="login-field">
                        <label for="email">E-Mail-Adresse</label>
                        <div class="login-input">
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
                            <div class="login-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="login-field">
                        <label for="password">Passwort</label>
                        <div class="login-input">
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
                            <div class="login-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="login-submit">
                        <span>Einloggen</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <footer class="login-footer">
                    Erstellt von
                    <a href="https://it-kayali.de" target="_blank" rel="noopener noreferrer">IT-Kayali</a>
                </footer>
            </section>
        </div>
    </main>
</body>
</html>