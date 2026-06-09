<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Lagerverwaltung' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

<!-- PREMIUM_SELECT_STYLE_START -->
<style>
    /* Native Selects */
    select.premium-select {
        background-color: #ffffff !important;
        border: 1px solid #ded6c8 !important;
        border-radius: 14px !important;
        color: #111111 !important;
        cursor: pointer !important;
        min-height: 44px !important;
        padding-right: 46px !important;
        box-shadow: 0 1px 0 rgba(0,0,0,.03) !important;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5.5 7.5L10 12L14.5 7.5' stroke='%23443b31' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 15px center !important;
        background-size: 18px 18px !important;
    }

    select.premium-select:hover {
        background-color: #ffffff !important;
        border-color: #c9a63d !important;
        box-shadow: 0 0 0 3px rgba(220, 184, 68, .12) !important;
    }

    select.premium-select:focus {
        background-color: #ffffff !important;
        border-color: #d5a824 !important;
        box-shadow: 0 0 0 4px rgba(220, 184, 68, .22) !important;
        outline: none !important;
    }

    /* TomSelect Wrapper */
    .ts-wrapper,
    .ts-wrapper.single,
    .ts-wrapper.multi {
        background: transparent !important;
    }

    /* Das ist die sichtbare Dropdown-Fläche */
    .ts-wrapper .ts-control,
    .ts-wrapper.single .ts-control,
    .ts-wrapper.multi .ts-control {
        background: #ffffff !important;
        background-color: #ffffff !important;
        border: 1px solid #ded6c8 !important;
        border-radius: 14px !important;
        color: #111111 !important;
        min-height: 44px !important;
        padding: 10px 42px 10px 14px !important;
        box-shadow: 0 1px 0 rgba(0,0,0,.03) !important;
        cursor: pointer !important;
    }

    .ts-wrapper .ts-control input {
        background: transparent !important;
        color: #111111 !important;
    }

    .ts-wrapper .ts-control .item {
        background: transparent !important;
        color: #111111 !important;
        font-weight: 700;
    }

    .ts-wrapper:hover .ts-control {
        background: #ffffff !important;
        border-color: #c9a63d !important;
        box-shadow: 0 0 0 3px rgba(220, 184, 68, .12) !important;
    }

    .ts-wrapper.focus .ts-control,
    .ts-wrapper.dropdown-active .ts-control {
        background: #ffffff !important;
        border-color: #d5a824 !important;
        box-shadow: 0 0 0 4px rgba(220, 184, 68, .22) !important;
    }

    .ts-dropdown {
        background: #ffffff !important;
        border: 1px solid #ded6c8 !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow: 0 18px 40px rgba(0,0,0,.12) !important;
    }

    .ts-dropdown .option {
        background: #ffffff !important;
        color: #111111 !important;
        padding: 10px 14px !important;
    }

    .ts-dropdown .option:hover,
    .ts-dropdown .active {
        background: #fff5d6 !important;
        color: #111111 !important;
    }
</style>
<!-- PREMIUM_SELECT_STYLE_END -->

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
