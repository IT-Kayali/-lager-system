<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Lagerverwaltung' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/unified-status-colors.css') }}">
    <link rel="stylesheet" href="{{ asset('css/csp/premium-layout.css') }}">
    <link rel="stylesheet" href="{{ route('application.theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/csp/application-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/csp/unified-app-chrome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/csp/app-sidebar.css') }}">

    @if (request()->routeIs('offers.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/offers.css') }}">
    @endif

    @if (request()->routeIs('products.*', 'sales.products.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/products.css') }}">
    @endif

    @if (request()->routeIs('suppliers.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/suppliers.css') }}">
    @endif

    @if (request()->routeIs('batches.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/batches.css') }}">
    @endif

    @if (request()->routeIs('branch-withdrawals.*', 'sales.branch-withdrawals.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/branch-withdrawals.css') }}">
    @endif

    @if (request()->routeIs('warehouse.offers.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/warehouse-offers.css') }}">
    @endif

    @if (request()->routeIs('product-categories.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/product-categories.css') }}">
    @endif

    @if (request()->routeIs('settings.index'))
        <link rel="stylesheet" href="{{ asset('css/csp/settings-admin.css') }}">
    @endif

    @if (request()->routeIs('security.users.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/security-users.css') }}">
    @endif

    @if (request()->routeIs('security.edit'))
        <link rel="stylesheet" href="{{ asset('css/csp/account-security.css') }}">
    @endif

    @if (request()->routeIs('document-templates.*'))
        <link rel="stylesheet" href="{{ asset('css/csp/document-templates.css') }}">
    @endif

    @if (request()->routeIs('customers.index'))
        <link rel="stylesheet" href="{{ asset('css/csp/customers.css') }}">
    @endif

    @if (request()->routeIs('warnings.index'))
        <link rel="stylesheet" href="{{ asset('css/csp/warnings.css') }}">
    @endif

    @if (request()->routeIs('prices.index'))
        <link rel="stylesheet" href="{{ asset('css/csp/prices.css') }}">
    @endif

<!-- PREMIUM_SELECT_STYLE_START -->

<!-- PREMIUM_SELECT_STYLE_END -->


<!-- GLOBAL_UNIFIED_TABLE_STYLE_START -->

<!-- GLOBAL_UNIFIED_TABLE_STYLE_END -->


<!-- PREMIUM_LAYOUT_REFRESH_START -->

<!-- PREMIUM_LAYOUT_REFRESH_END -->

<!-- ERP_UNIFIED_LIST_UI_START -->

<!-- ERP_UNIFIED_LIST_UI_END -->



<!-- PREMIUM_SIDEBAR_POLISH_START -->

<!-- PREMIUM_SIDEBAR_POLISH_END -->


<!-- PREMIUM_BRAND_OVERFLOW_FIX_START -->

<!-- PREMIUM_BRAND_OVERFLOW_FIX_END -->


<!-- PREMIUM_LOGO_PLACEHOLDER_START -->

<!-- PREMIUM_LOGO_PLACEHOLDER_END -->


    {{ $head ?? '' }}
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

                @php
                    $premiumUser = auth()->user();
                @endphp
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    @if ($premiumUser?->hasRole([\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES]))
                        <a href="{{ route('offers.create') }}" class="premium-btn gold">
                            <i class="bi bi-receipt"></i>
                            Neues Angebot
                        </a>
                    @endif
                    @if ($premiumUser?->hasRole([\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE]))
                        <a href="{{ route('warnings.index') }}" class="premium-btn">
                            <i class="bi bi-exclamation-lg"></i>
                            Lager prüfen
                        </a>
                    @endif
                </div>
            </header>

            {{ $slot }}
        </main>
    </div>

    @livewireScripts

<!-- PREMIUM_GLOBAL_TOASTS_START -->
@php
    $premiumToastMessages = [];

    if (session('success')) {
        $premiumToastMessages[] = [
            'type' => 'success',
            'title' => 'Erfolgreich',
            'message' => session('success'),
        ];
    }

    if (session('status')) {
        $premiumToastMessages[] = [
            'type' => 'success',
            'title' => 'Hinweis',
            'message' => session('status'),
        ];
    }

    if (session('error')) {
        $premiumToastMessages[] = [
            'type' => 'error',
            'title' => 'Fehler',
            'message' => session('error'),
        ];
    }

    if (isset($errors) && $errors->any()) {
        foreach ($errors->all() as $error) {
            $premiumToastMessages[] = [
                'type' => 'error',
                'title' => 'Bitte prüfen',
                'message' => $error,
            ];
        }
    }
@endphp

<div id="premium-toast-stack" class="premium-toast-stack" aria-live="polite" aria-atomic="true"></div>



<div
    id="premium-layout-runtime-config"
    hidden
    data-server-toasts="{{ json_encode($premiumToastMessages ?? []) }}"
></div>

<script
    src="{{ asset('js/premium-layout-runtime.js') }}"
    defer
></script>
<!-- PREMIUM_GLOBAL_TOASTS_END -->

</body>
</html>