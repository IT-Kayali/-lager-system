<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · {{ config('app.name', 'Lagerverwaltung') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="itk-auth-v2">
    <main class="itk-auth-v2-page">
        <section class="itk-auth-v2-shell">
            <div class="itk-auth-v2-hero">
                <div class="itk-auth-v2-logo">
                    <i class="bi bi-box-seam"></i>
                </div>

                <h1>Lagerverwaltungssystem</h1>
                <p>Sicherer Login für Lager, Angebote und Vertrieb.</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="itk-auth-v2-card">
                @csrf

                <div class="itk-auth-v2-field">
                    <label for="email">E-Mail-Adresse</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="E-Mail"
                        autocomplete="email"
                        required
                        autofocus
                    >

                    @error('email')
                        <div class="itk-auth-v2-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="itk-auth-v2-field">
                    <label for="password">Passwort</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Passwort"
                        autocomplete="current-password"
                        required
                    >

                    @error('password')
                        <div class="itk-auth-v2-error">{{ $message }}</div>
                    @enderror
                </div>

                <label class="itk-auth-v2-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Angemeldet bleiben</span>
                </label>

                <button type="submit" class="itk-auth-v2-submit">
                    Einloggen
                </button>
            </form>

            <footer class="itk-auth-v2-footer">
                Erstellt von
                <a href="https://it-kayali.de" target="_blank" rel="noopener noreferrer">IT-Kayali</a>
            </footer>
        </section>
    </main>
</body>
</html>
