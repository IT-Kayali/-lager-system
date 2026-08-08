@include('partials.application-theme')
@include('partials.unified-app-chrome')

@php
    $sidebarLogoPath = \App\Models\ApplicationSetting::loginLogoPath();
    $sidebarLogoUrl = $sidebarLogoPath
        ? route('login.logo', [], false)
        : null;

    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard',
            'icon' => 'bi-speedometer2',
            'roles' => ['manager', 'wholesale', 'warehouse'],
            'section' => 'main',
        ],
        [
            'label' => 'Produkte',
            'route' => 'products.index',
            'active' => 'products.*',
            'icon' => 'bi-box-seam',
            'roles' => ['manager', 'wholesale', 'warehouse'],
            'section' => 'main',
        ],
        [
            'label' => 'Kategorien',
            'route' => 'product-categories.index',
            'active' => 'product-categories.*',
            'icon' => 'bi-tags',
            'roles' => ['manager', 'wholesale', 'warehouse'],
            'section' => 'main',
        ],
        [
            'label' => 'Chargen & FIFO',
            'route' => 'batches.index',
            'active' => 'batches.*',
            'icon' => 'bi-layers',
            'roles' => ['manager', 'warehouse'],
            'section' => 'main',
        ],
        [
            'label' => 'Filialausgang',
            'route' => 'branch-withdrawals.index',
            'active' => 'branch-withdrawals.*',
            'icon' => 'bi-shop',
            'roles' => ['manager', 'warehouse'],
            'section' => 'main',
        ],
        [
            'label' => 'Angebote & Rechnungen',
            'route' => 'offers.index',
            'active' => 'offers.*',
            'icon' => 'bi-receipt-cutoff',
            'roles' => ['manager', 'wholesale'],
            'section' => 'main',
        ],
        [
            'label' => 'Kunden',
            'route' => 'customers.index',
            'active' => 'customers.*',
            'icon' => 'bi-people',
            'roles' => ['manager', 'wholesale'],
            'section' => 'main',
        ],
        [
            'label' => 'Lieferanten',
            'route' => 'suppliers.index',
            'active' => 'suppliers.*',
            'icon' => 'bi-truck',
            'roles' => ['manager'],
            'section' => 'main',
        ],
        [
            'label' => 'Preise',
            'route' => 'prices.index',
            'active' => 'prices.*',
            'icon' => 'bi-currency-euro',
            'roles' => ['manager', 'wholesale'],
            'section' => 'main',
        ],
        [
            'label' => 'Warnungen',
            'route' => 'warnings.index',
            'active' => 'warnings.*',
            'icon' => 'bi-exclamation-triangle',
            'roles' => ['manager', 'warehouse'],
            'section' => 'main',
        ],
        [
            'label' => 'Statistik',
            'route' => 'statistics.index',
            'active' => 'statistics.*',
            'icon' => 'bi-bar-chart-line',
            'roles' => ['manager'],
            'section' => 'main',
        ],
        [
            'label' => 'Einstellungen',
            'route' => 'settings.index',
            'active' => 'settings.*',
            'icon' => 'bi-gear',
            'roles' => ['manager'],
            'section' => 'footer',
        ],
        [
            'label' => 'Rechte & Sicherheit',
            'route' => 'security.index',
            'active' => 'security.*',
            'icon' => 'bi-shield-lock',
            'roles' => ['manager'],
            'section' => 'footer',
        ],
    ];

    $mainNavItems = collect($navItems)->where('section', 'main');
    $footerNavItems = collect($navItems)->where('section', 'footer');
@endphp

<aside class="premium-sidebar" aria-label="Hauptnavigation">
    <a href="{{ route('dashboard') }}" class="premium-brand {{ $sidebarLogoUrl ? 'has-custom-logo' : '' }}" aria-label="Zurück zum Dashboard">
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
                @if (auth()->user()?->canAccessMenu($item['roles']))
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
            <div class="premium-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="premium-user-meta">
                <div class="premium-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
                <div class="premium-user-role">{{ auth()->user()->role }}</div>
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
