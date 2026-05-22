@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Lagerverwaltung') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="premium-auth-body">
    <main class="premium-auth-page">
        <section class="premium-auth-shell">
            <div class="premium-auth-brand">
                <div class="premium-auth-logo">
                    <i class="bi bi-box-seam"></i>
                </div>

                <div>
                    <div class="premium-auth-brand-title">Lagerverwaltung</div>
                    <div class="premium-auth-brand-subtitle">Inventory • Sales • PDF</div>
                </div>
            </div>

            <div class="premium-auth-card">
                {{ $slot }}
            </div>

            <footer class="premium-auth-footer">
                Erstellt von
                <a href="https://it-kayali.de" target="_blank" rel="noopener noreferrer">
                    IT-Kayali
                </a>
            </footer>
        </section>
    </main>

    @livewireScripts
</body>
</html>
