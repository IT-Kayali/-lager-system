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

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            width: 100%;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #16130f;
            color: #171511;
        }

        .login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            background:
                linear-gradient(135deg, rgba(16, 14, 10, .78), rgba(16, 14, 10, .30) 42%, rgba(255, 244, 221, .68)),
                @if ($loginBackgroundUrl)
                    url('{{ $loginBackgroundUrl }}')
                @else
                    radial-gradient(circle at 50% 20%, rgba(239, 202, 86, .35), transparent 30%),
                    linear-gradient(135deg, #1d1710, #5b4314 46%, #fff2d4)
                @endif;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }

        .login-page::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 18% 18%, rgba(239, 202, 86, .35), transparent 28%),
                radial-gradient(circle at 88% 78%, rgba(255, 255, 255, .30), transparent 30%);
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(100%, 1060px);
            display: grid;
            grid-template-columns: minmax(0, 1fr) 420px;
            gap: clamp(28px, 5vw, 70px);
            align-items: center;
        }

        .login-info {
            color: #fff;
            text-shadow: 0 20px 70px rgba(0, 0, 0, .42);
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 34px;
            padding: 13px 16px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 22px;
            background: rgba(20, 18, 14, .48);
            backdrop-filter: blur(16px);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .24);
        }

        .login-brand-icon,
        .login-card-icon {
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #f8df7a, #d4aa16);
            color: #171511;
            box-shadow: 0 16px 40px rgba(212, 170, 22, .34);
        }

        .login-brand-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            font-size: 26px;
        }

        .login-brand small,
        .login-kicker,
        .login-card-kicker {
            display: block;
            color: #f8df7a;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .login-brand strong {
            display: block;
            margin-top: 4px;
            color: #fff;
            font-size: 18px;
            font-weight: 950;
            line-height: 1.05;
        }

        .login-info h1 {
            max-width: 680px;
            margin: 14px 0 18px;
            color: #fff;
            font-size: clamp(42px, 6vw, 76px);
            font-weight: 950;
            letter-spacing: -.065em;
            line-height: .95;
        }

        .login-info p {
            max-width: 590px;
            margin: 0;
            color: rgba(255, 255, 255, .86);
            font-size: clamp(16px, 1.4vw, 20px);
            font-weight: 750;
            line-height: 1.65;
        }

        .login-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .login-pills span {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 14px;
            border: 1px solid rgba(255, 255, 255, .20);
            border-radius: 999px;
            background: rgba(20, 18, 14, .44);
            color: rgba(255, 255, 255, .90);
            font-size: 13px;
            font-weight: 900;
            backdrop-filter: blur(14px);
        }

        .login-card-wrap {
            width: 100%;
        }

        .login-card {
            width: 100%;
            padding: 34px;
            border: 1px solid rgba(240, 211, 126, .48);
            border-radius: 32px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .97), rgba(255, 249, 237, .94));
            box-shadow: 0 36px 110px rgba(13, 11, 8, .34);
            backdrop-filter: blur(18px);
        }

        .login-card-head {
            margin-bottom: 26px;
            text-align: center;
        }

        .login-card-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 15px;
            border-radius: 20px;
            font-size: 25px;
        }

        .login-card h2 {
            margin: 8px 0;
            color: #171511;
            font-size: 34px;
            font-weight: 950;
            letter-spacing: -.045em;
            line-height: 1.05;
        }

        .login-card p {
            margin: 0;
            color: #675d4f;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.45;
        }

        .login-field {
            margin-bottom: 17px;
        }

        .login-field label {
            display: block;
            margin-bottom: 8px;
            color: #1d1912;
            font-size: 14px;
            font-weight: 950;
        }

        .login-input {
            position: relative;
        }

        .login-input i {
            position: absolute;
            top: 50%;
            left: 17px;
            transform: translateY(-50%);
            color: #a77b10;
            font-size: 18px;
            pointer-events: none;
        }

        .login-input input {
            width: 100%;
            min-height: 58px;
            padding: 0 18px 0 50px;
            border: 1px solid rgba(190, 155, 58, .42);
            border-radius: 18px;
            background: #fffdf8;
            color: #171511;
            font-size: 15px;
            font-weight: 850;
            outline: none;
            box-shadow: 0 12px 30px rgba(42, 33, 18, .06) inset;
        }

        .login-input input::placeholder {
            color: rgba(29, 25, 18, .44);
        }

        .login-input input:focus {
            border-color: #e3bd35;
            box-shadow: 0 0 0 4px rgba(227, 189, 53, .24);
        }

        .login-error {
            margin-top: 8px;
            color: #b91c1c;
            font-size: 13px;
            font-weight: 850;
        }

        .login-submit {
            width: 100%;
            min-height: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            margin-top: 8px;
            border: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, #171511, #2d271a);
            color: #f8df7a;
            font-size: 16px;
            font-weight: 950;
            cursor: pointer;
            box-shadow: 0 20px 45px rgba(23, 21, 17, .25);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }

        .login-submit:hover {
            filter: brightness(1.06);
            transform: translateY(-2px);
            box-shadow: 0 28px 55px rgba(23, 21, 17, .30);
        }

        .login-footer {
            margin-top: 18px;
            color: rgba(255, 255, 255, .82);
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            text-shadow: 0 10px 30px rgba(0, 0, 0, .34);
        }

        .login-footer a {
            color: #f97316;
            font-weight: 950;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 920px) {
            .login-shell {
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .login-info {
                text-align: center;
            }

            .login-brand,
            .login-pills {
                justify-content: center;
            }

            .login-info h1 {
                font-size: clamp(34px, 10vw, 54px);
            }
        }

        @media (max-width: 520px) {
            .login-page {
                padding: 22px 14px;
                place-items: start center;
            }

            .login-brand {
                width: 100%;
            }

            .login-info p,
            .login-pills {
                display: none;
            }

            .login-card {
                padding: 26px;
                border-radius: 26px;
            }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="login-shell">
            <section class="login-info" aria-label="Lagerverwaltung Übersicht">
                <div class="login-brand">
                    <div class="login-brand-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <small>Premium ERP</small>
                        <strong>Lagerverwaltungssystem</strong>
                    </div>
                </div>

                <span class="login-kicker">Sicherer Zugriff</span>
                <h1>Alles im Lager sofort im Blick.</h1>
                <p>Modernes Dashboard für Bestände, Angebote, Rechnungen und Warnungen — schnell, klar und sicher.</p>

                <div class="login-pills" aria-label="Systemvorteile">
                    <span><i class="bi bi-box-seam"></i> Live Bestand</span>
                    <span><i class="bi bi-file-earmark-pdf"></i> PDF Workflow</span>
                    <span><i class="bi bi-shield-check"></i> Sicherer Login</span>
                </div>
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
