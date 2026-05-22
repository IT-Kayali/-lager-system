<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Lagerverwaltung' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="premium-shell">
        @include('partials.app-sidebar')

        <main class="premium-main">
            <header class="premium-topbar">
                <div>
                    <h1 class="premium-page-title">{{ $title ?? 'Dashboard' }}</h1>
                    <p class="premium-page-subtitle">
                        {{ $subtitle ?? 'Modernes Lagerverwaltungs-, Angebots- und Vertriebssystem' }}
                    </p>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('offers.index') }}" class="premium-btn gold">
                        <i class="bi bi-receipt"></i>
                        Neues Angebot
                    </a>
                    <a href="{{ route('warnings.index') }}" class="premium-btn">
                        <i class="bi bi-exclamation-lg"></i>
                        Lager prüfen
                    </a>
                </div>
            </header>

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
