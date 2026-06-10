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


<!-- GLOBAL_UNIFIED_TABLE_STYLE_START -->
<style>
    /*
     * Einheitliches Tabellen-Design für alle Bereiche:
     * Produkte, Kategorien, Chargen, Filialausgang, Angebote,
     * Kunden, Lieferanten, Preise, Warnungen.
     */

    .premium-table-wrap,
    .products-table-shell,
    .category-table-shell,
    .category-products-table-shell {
        margin-top: 22px !important;
        overflow-x: auto !important;
        border: 1px solid #e7dece !important;
        background: #ffffff !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .premium-table,
    .products-clean-table,
    .category-clean-table,
    .category-products-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        background: #ffffff !important;
    }

    .premium-table thead th,
    .products-clean-table thead th,
    .category-clean-table thead th,
    .category-products-table thead th {
        padding: 14px 12px !important;
        color: #7a7064 !important;
        font-size: 11px !important;
        font-weight: 950 !important;
        text-transform: uppercase !important;
        letter-spacing: .06em !important;
        white-space: nowrap !important;
        text-align: left !important;
        border-bottom: 1px solid #e7dece !important;
        background: #fffdf8 !important;
    }

    .premium-table tbody td,
    .products-clean-table tbody td,
    .category-clean-table tbody td,
    .category-products-table tbody td {
        padding: 16px 12px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #e7dece !important;
        background: #ffffff !important;
        white-space: nowrap !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
    }

    .premium-table tbody tr:last-child td,
    .products-clean-table tbody tr:last-child td,
    .category-clean-table tbody tr:last-child td,
    .category-products-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .premium-table tbody tr:hover td,
    .products-clean-table tbody tr:hover td,
    .category-clean-table tbody tr:hover td,
    .category-products-table tbody tr:hover td {
        background: #fffaf0 !important;
    }

    .premium-table tbody tr,
    .products-clean-table tbody tr,
    .category-clean-table tbody tr,
    .category-products-table tbody tr {
        border-radius: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .premium-table td:first-child,
    .premium-table th:first-child,
    .products-clean-table td:first-child,
    .products-clean-table th:first-child,
    .category-clean-table td:first-child,
    .category-clean-table th:first-child,
    .category-products-table td:first-child,
    .category-products-table th:first-child {
        border-left: 0 !important;
        border-radius: 0 !important;
    }

    .premium-table td:last-child,
    .premium-table th:last-child,
    .products-clean-table td:last-child,
    .products-clean-table th:last-child,
    .category-clean-table td:last-child,
    .category-clean-table th:last-child,
    .category-products-table td:last-child,
    .category-products-table th:last-child {
        border-right: 0 !important;
        border-radius: 0 !important;
        text-align: right !important;
    }

    .premium-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        flex-wrap: nowrap !important;
    }

    .premium-actions form {
        margin: 0 !important;
    }

    .premium-code {
        color: #111111 !important;
        font-weight: 950 !important;
        text-decoration: none !important;
    }

    .premium-code:hover {
        color: #a9871f !important;
        text-decoration: underline !important;
    }

    .premium-table .premium-muted,
    .products-clean-table .premium-muted,
    .category-clean-table .premium-muted,
    .category-products-table .premium-muted {
        color: #7a7064 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    @media (max-width: 900px) {
        .premium-table,
        .products-clean-table,
        .category-clean-table,
        .category-products-table {
            min-width: 850px !important;
        }
    }
</style>
<!-- GLOBAL_UNIFIED_TABLE_STYLE_END -->

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
