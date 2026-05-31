@php
    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard',
            'icon' => 'bi-house-door',
            'roles' => ['manager', 'wholesale', 'warehouse'],
        ],
        [
            'label' => 'Produkte',
            'route' => 'products.index',
            'active' => 'products.*',
            'icon' => 'bi-box-seam',
            'roles' => ['manager', 'wholesale', 'warehouse'],
        ],
        [
            'label' => 'Chargen & FIFO',
            'route' => 'batches.index',
            'active' => 'batches.*',
            'icon' => 'bi-columns-gap',
            'roles' => ['manager', 'warehouse'],
        ],
        [
            'label' => 'Filialausgang',
            'route' => 'branch-withdrawals.index',
            'active' => 'branch-withdrawals.*',
            'icon' => 'bi-shop',
            'roles' => ['manager', 'warehouse'],
        ],
        [
            'label' => 'Angebote & Rechnungen',
            'route' => 'offers.index',
            'active' => 'offers.*',
            'icon' => 'bi-receipt',
            'roles' => ['manager', 'wholesale'],
        ],
        [
            'label' => 'Kunden',
            'route' => 'customers.index',
            'active' => 'customers.*',
            'icon' => 'bi-people',
            'roles' => ['manager', 'wholesale'],
        ],
        [
            'label' => 'Lieferanten',
            'route' => 'suppliers.index',
            'active' => 'suppliers.*',
            'icon' => 'bi-truck',
            'roles' => ['manager'],
        ],
        [
            'label' => 'Preise',
            'route' => 'prices.index',
            'active' => 'prices.*',
            'icon' => 'bi-currency-euro',
            'roles' => ['manager', 'wholesale'],
        ],
        [
            'label' => 'Warnungen',
            'route' => 'warnings.index',
            'active' => 'warnings.*',
            'icon' => 'bi-exclamation-lg',
            'roles' => ['manager', 'warehouse'],
        ],
        [
            'label' => 'Statistik',
            'route' => 'statistics.index',
            'active' => 'statistics.*',
            'icon' => 'bi-bar-chart',
            'roles' => ['manager'],
        ],
        [
            'label' => 'Einstellungen',
            'route' => 'settings.index',
            'active' => 'settings.*',
            'icon' => 'bi-gear',
            'roles' => ['manager'],
        ],
        [
            'label' => 'Rechte & Sicherheit',
            'route' => 'security.index',
            'active' => 'security.*',
            'icon' => 'bi-shield-lock',
            'roles' => ['manager'],
        ],
    ];
@endphp

<aside class="premium-sidebar">
    <div class="premium-brand">
        <div class="premium-brand-mark">
            <i class="bi bi-box-seam"></i>
        </div>
        <div>
            <div class="premium-brand-title">Lagerverwaltung</div>
            <div class="premium-brand-subtitle">Inventory • Sales • PDF</div>
        </div>
    </div>

    <nav class="premium-sidebar-nav">
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

    <div class="premium-sidebar-footer">
        <div class="premium-user-box">
            <div class="premium-user-name">{{ auth()->user()->name }}</div>
            <div class="premium-user-role">{{ auth()->user()->role }}</div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="premium-logout-btn" type="submit">
                    <i class="bi bi-box-arrow-right"></i>
                    Abmelden
                </button>
            </form>
        </div>
    </div>
</aside>
