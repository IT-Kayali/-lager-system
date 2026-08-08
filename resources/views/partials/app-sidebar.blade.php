@include('partials.application-theme')
@include('partials.unified-app-chrome')
@include('partials.browser-branding-runtime')

@php
    $user = auth()->user();
    $sidebarLogoPath = \App\Models\ApplicationSetting::loginLogoPath();
    $sidebarLogoUrl = $sidebarLogoPath
        ? route('login.logo', [], false)
        : null;

    $offersRoute = $user?->isWarehouse()
        ? 'warehouse.offers.index'
        : 'offers.index';

    $offersActive = $user?->isWarehouse()
        ? 'warehouse.offers.*'
        : 'offers.*';

    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard',
            'icon' => 'bi-speedometer2',
            'roles' => [\App\Models\User::ROLE_MANAGER],
            'section' => 'main',
        ],
        [
            'label' => 'Produkte',
            'route' => 'products.index',
            'active' => 'products.*',
            'icon' => 'bi-box-seam',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE],
            'section' => 'main',
        ],
        [
            'label' => 'Kategorien',
            'route' => 'product-categories.index',
            'active' => 'product-categories.*',
            'icon' => 'bi-tags',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE],
            'section' => 'main',
        ],
        [
            'label' => 'Chargen & FIFO',
            'route' => 'batches.index',
            'active' => 'batches.*',
            'icon' => 'bi-layers',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE],
            'section' => 'main',
        ],
        [
            'label' => 'Filialausgang',
            'route' => 'branch-withdrawals.index',
            'active' => 'branch-withdrawals.*',
            'icon' => 'bi-shop',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE],
            'section' => 'main',
        ],
        [
            'label' => 'Angebote & Rechnungen',
            'route' => $offersRoute,
            'active' => $offersActive,
            'icon' => 'bi-receipt-cutoff',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES, \App\Models\User::ROLE_WAREHOUSE],
            'section' => 'main',
        ],
        [
            'label' => 'Kunden',
            'route' => 'customers.index',
            'active' => 'customers.*',
            'icon' => 'bi-people',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES],
            'section' => 'main',
        ],
        [
            'label' => 'Lieferanten',
            'route' => 'suppliers.index',
            'active' => 'suppliers.*',
            'icon' => 'bi-truck',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES],
            'section' => 'main',
        ],
        [
            'label' => 'Preise',
            'route' => 'prices.index',
            'active' => 'prices.*',
            'icon' => 'bi-currency-euro',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES],
            'section' => 'main',
        ],
        [
            'label' => 'Warnungen',
            'route' => 'warnings.index',
            'active' => 'warnings.*',
            'icon' => 'bi-exclamation-triangle',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE],
            'section' => 'main',
        ],
        [
            'label' => 'Statistik',
            'route' => 'statistics.index',
            'active' => 'statistics.*',
            'icon' => 'bi-bar-chart-line',
            'roles' => [\App\Models\User::ROLE_MANAGER],
            'section' => 'main',
        ],
        [
            'label' => 'Einstellungen',
            'route' => 'settings.index',
            'active' => 'settings.*',
            'icon' => 'bi-gear',
            'roles' => [\App\Models\User::ROLE_ADMIN],
            'section' => 'footer',
        ],
        [
            'label' => 'Rechte & Sicherheit',
            'route' => 'security.index',
            'active' => 'security.*',
            'icon' => 'bi-shield-lock',
            'roles' => [\App\Models\User::ROLE_ADMIN],
            'section' => 'footer',
        ],
    ];
@endphp

<aside class="premium-sidebar" aria-label="Hauptnavigation">
    <a href="{{ route('dashboard') }}" class="premium-brand {{ $sidebarLogoUrl ? 'has-custom-logo' : '' }}" aria-label="Zurück zum Startbereich">
        @if ($sidebarLogoUrl)
            <img class="premium-brand-logo" src="{{ $sidebarLogoUrl }}" alt="Sidebar-Logo">
        @else
            <span class="premium-brand-mark">
                <i class="bi bi-box-seam"></i>
            </span>
            <span class="premium-brand-copy">
                <span class="premium-brand-title">Lagerverwaltung</span>
                <span class="premium-brand-subtitle">Inventory • Sales • PDF</span>
            </span>
        @endif
    </a>

    <div class="premium-sidebar-scroll">
        <nav class="premium-sidebar-nav" aria-label="Menü">
            @foreach ($navItems as $item)
                @if ($user?->canAccessMenu($item['roles']))
                    <a
                        href="{{ route($item['route']) }}"
                        class="premium-sidebar-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                    >
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="premium-sidebar-footer">
        <div class="premium-user-box">
            <div class="premium-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="premium-user-meta">
                <div class="premium-user-name" title="{{ $user->name }}">{{ $user->name }}</div>
                <div class="premium-user-role">{{ $user->roleLabel() }}</div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="premium-logout-btn" type="submit">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Abmelden</span>
                </button>
            </form>
        </div>
    </div>
</aside>
