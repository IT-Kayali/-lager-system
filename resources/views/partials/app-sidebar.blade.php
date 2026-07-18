@php
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

<aside class="premium-sidebar">
    <div class="premium-sidebar-inner">
        <div class="premium-brand">
            <div class="premium-logo-placeholder">
                <div class="premium-logo-placeholder-mark">
                    <i class="bi bi-archive"></i>
                </div>

                <div class="premium-logo-placeholder-copy">
                    <div class="premium-logo-placeholder-title">Logo</div>
                    <div class="premium-logo-placeholder-subtitle">Lagerverwaltung</div>
                </div>
            </div>
        </div>

        <nav class="premium-sidebar-nav" aria-label="Hauptnavigation">
            @foreach ($mainNavItems as $item)
                @if (auth()->user()?->canAccessMenu($item['roles']))
                    <a
                        href="{{ route($item['route']) }}"
                        class="premium-sidebar-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                    >
                        <span class="premium-sidebar-icon">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="premium-sidebar-footer">
            <nav class="premium-sidebar-nav premium-sidebar-nav-footer" aria-label="Systemnavigation">
                @foreach ($footerNavItems as $item)
                    @if (auth()->user()?->canAccessMenu($item['roles']))
                        <a
                            href="{{ route($item['route']) }}"
                            class="premium-sidebar-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                        >
                            <span class="premium-sidebar-icon">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="premium-sidebar-link premium-sidebar-button" type="submit">
                        <span class="premium-sidebar-icon">
                            <i class="bi bi-box-arrow-right"></i>
                        </span>
                        <span>Abmelden</span>
                    </button>
                </form>
            </nav>

            <div class="premium-user-box">
                <div class="premium-user-avatar">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>

                <div class="premium-user-meta">
                    <div class="premium-user-name">{{ auth()->user()->name }}</div>
                    <div class="premium-user-role">{{ strtoupper(auth()->user()->role) }}</div>
                </div>
            </div>
        </div>
    </div>
</aside>
