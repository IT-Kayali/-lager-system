@include('partials.application-theme')
@include('partials.unified-app-chrome')
@include('partials.unified-status-colors')
@include('partials.browser-branding-runtime')

@php
    $user = auth()->user();

    $warehouseNotificationItems = collect();
    $warehouseUnreadCount = 0;

    if (
        $user?->isWarehouse()
        && \Illuminate\Support\Facades\Schema::hasTable('warehouse_notifications')
    ) {
        $warehouseNotificationQuery = \App\Models\WarehouseNotification::query()
            ->unreadFor($user);

        $warehouseUnreadCount = (clone $warehouseNotificationQuery)->count();
        $warehouseNotificationItems = (clone $warehouseNotificationQuery)
            ->latest()
            ->limit(8)
            ->get();
    }

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

    $productsRoute = $user?->isSales()
        ? 'sales.products.index'
        : 'products.index';

    $productsActive = $user?->isSales()
        ? 'sales.products.*'
        : 'products.*';

    $branchWithdrawalsRoute = $user?->isSales()
        ? 'sales.branch-withdrawals.index'
        : 'branch-withdrawals.index';

    $branchWithdrawalsActive = $user?->isSales()
        ? 'sales.branch-withdrawals.*'
        : 'branch-withdrawals.*';

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
            'route' => $productsRoute,
            'active' => $productsActive,
            'icon' => 'bi-box-seam',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE, \App\Models\User::ROLE_SALES],
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
            'route' => $branchWithdrawalsRoute,
            'active' => $branchWithdrawalsActive,
            'icon' => 'bi-shop',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE, \App\Models\User::ROLE_SALES],
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
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES, \App\Models\User::ROLE_CRM],
            'section' => 'main',
        ],
        [
            'label' => 'Lieferanten',
            'route' => 'suppliers.index',
            'active' => 'suppliers.*',
            'icon' => 'bi-truck',
            'roles' => [\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE],
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

            @if ($user?->isWarehouse())
                <div class="warehouse-notification-wrap" id="warehouse-notification-wrap">
                    <button
                        type="button"
                        class="warehouse-notification-button"
                        id="warehouse-notification-button"
                        aria-label="Lager-Benachrichtigungen"
                        aria-expanded="false"
                    >
                        <i class="bi bi-bell"></i>
                        <span
                            class="warehouse-notification-badge {{ $warehouseUnreadCount > 0 ? '' : 'is-hidden' }}"
                            id="warehouse-notification-badge"
                        >{{ $warehouseUnreadCount }}</span>
                    </button>

                    <div class="warehouse-notification-panel" id="warehouse-notification-panel" hidden>
                        <div class="warehouse-notification-panel-head">
                            <strong>Neue Lageraufträge</strong>
                            <span id="warehouse-notification-panel-count">{{ $warehouseUnreadCount }}</span>
                        </div>

                        <div class="warehouse-notification-list" id="warehouse-notification-list">
                            @forelse ($warehouseNotificationItems as $notification)
                                <a
                                    href="{{ route('warehouse.notifications.open', $notification) }}"
                                    class="warehouse-notification-item"
                                >
                                    <span class="warehouse-notification-item-icon">
                                        <i class="bi {{ $notification->type === \App\Models\WarehouseNotification::TYPE_OFFER ? 'bi-receipt-cutoff' : 'bi-shop' }}"></i>
                                    </span>
                                    <span class="warehouse-notification-item-copy">
                                        <strong>{{ $notification->title }}</strong>
                                        @if ($notification->message)
                                            <small>{{ $notification->message }}</small>
                                        @endif
                                        <time>{{ $notification->created_at?->format('d.m.Y H:i') }}</time>
                                    </span>
                                </a>
                            @empty
                                <div class="warehouse-notification-empty">Keine neuen Lageraufträge.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

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

@if ($user?->isWarehouse())
    <style>
        .premium-user-box {
            position: relative !important;
            display: grid !important;
            grid-template-columns: 40px minmax(0, 1fr) auto !important;
            align-items: center !important;
            gap: 10px 12px !important;
        }

        .premium-user-box > form {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            margin: 0 !important;
        }

        .premium-user-box .premium-logout-btn {
            margin-top: 0 !important;
        }

        .warehouse-notification-wrap {
            position: relative;
            z-index: 50;
        }

        .warehouse-notification-button {
            position: relative;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(227, 202, 110, .55);
            border-radius: 12px;
            background: rgba(255, 255, 255, .04);
            color: #fff;
            cursor: pointer;
            transition: .16s ease;
        }

        .warehouse-notification-button:hover,
        .warehouse-notification-button[aria-expanded="true"] {
            background: #e3ca6e;
            color: #171716;
            border-color: #e3ca6e;
        }

        .warehouse-notification-button > i {
            font-size: 18px;
        }

        .warehouse-notification-badge {
            position: absolute;
            top: -7px;
            right: -7px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            border: 2px solid #2d2b25;
            font-size: 10px;
            font-weight: 950;
            line-height: 1;
        }

        .warehouse-notification-badge.is-hidden {
            display: none;
        }

        .warehouse-notification-panel {
            position: absolute;
            right: 0;
            bottom: 48px;
            width: 258px;
            max-height: 380px;
            overflow: hidden;
            border: 1px solid rgba(227, 202, 110, .38);
            border-radius: 16px;
            background: #24231f;
            box-shadow: 0 22px 55px rgba(0, 0, 0, .34);
        }

        .warehouse-notification-panel[hidden] {
            display: none !important;
        }

        .warehouse-notification-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 13px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            color: #fff5d0;
            font-size: 12px;
        }

        .warehouse-notification-panel-head span {
            min-width: 24px;
            height: 24px;
            padding: 0 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #e3ca6e;
            color: #171716;
            font-weight: 950;
        }

        .warehouse-notification-list {
            max-height: 325px;
            overflow-y: auto;
        }

        .warehouse-notification-item {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 9px;
            padding: 11px 12px;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            transition: background .15s ease;
        }

        .warehouse-notification-item:hover {
            background: rgba(227, 202, 110, .10);
        }

        .warehouse-notification-item-icon {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(227, 202, 110, .15);
            color: #ffe690;
        }

        .warehouse-notification-item-copy {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        .warehouse-notification-item-copy strong {
            color: #fff;
            font-size: 12px;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .warehouse-notification-item-copy small {
            color: rgba(255, 255, 255, .72);
            font-size: 11px;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .warehouse-notification-item-copy time {
            color: rgba(255, 255, 255, .46);
            font-size: 10px;
            font-weight: 750;
        }

        .warehouse-notification-empty {
            padding: 18px 14px;
            color: rgba(255, 255, 255, .62);
            font-size: 11px;
            text-align: center;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrap = document.getElementById('warehouse-notification-wrap');
            const button = document.getElementById('warehouse-notification-button');
            const panel = document.getElementById('warehouse-notification-panel');
            const badge = document.getElementById('warehouse-notification-badge');
            const countLabel = document.getElementById('warehouse-notification-panel-count');
            const list = document.getElementById('warehouse-notification-list');

            if (!wrap || !button || !panel || !badge || !countLabel || !list) {
                return;
            }

            const endpoint = @json(route('warehouse.notifications.index'));

            function setOpen(open) {
                panel.hidden = !open;
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function renderNotifications(payload) {
                const count = Number(payload?.unread_count || 0);
                const items = Array.isArray(payload?.items) ? payload.items : [];

                badge.textContent = count > 99 ? '99+' : String(count);
                badge.classList.toggle('is-hidden', count < 1);
                countLabel.textContent = String(count);

                list.replaceChildren();

                if (items.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'warehouse-notification-empty';
                    empty.textContent = 'Keine neuen Lageraufträge.';
                    list.appendChild(empty);
                    return;
                }

                items.forEach(function (item) {
                    const link = document.createElement('a');
                    link.className = 'warehouse-notification-item';
                    link.href = item.open_url;

                    const icon = document.createElement('span');
                    icon.className = 'warehouse-notification-item-icon';

                    const iconElement = document.createElement('i');
                    iconElement.className = item.type === 'offer'
                        ? 'bi bi-receipt-cutoff'
                        : 'bi bi-shop';
                    icon.appendChild(iconElement);

                    const copy = document.createElement('span');
                    copy.className = 'warehouse-notification-item-copy';

                    const title = document.createElement('strong');
                    title.textContent = item.title || 'Neuer Lagerauftrag';
                    copy.appendChild(title);

                    if (item.message) {
                        const message = document.createElement('small');
                        message.textContent = item.message;
                        copy.appendChild(message);
                    }

                    if (item.time) {
                        const time = document.createElement('time');
                        time.textContent = item.time;
                        copy.appendChild(time);
                    }

                    link.appendChild(icon);
                    link.appendChild(copy);
                    list.appendChild(link);
                });
            }

            async function refreshNotifications() {
                try {
                    const response = await fetch(endpoint, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    renderNotifications(await response.json());
                } catch (error) {
                    // Eine kurzzeitig fehlgeschlagene Aktualisierung darf die Oberfläche nicht stören.
                }
            }

            button.addEventListener('click', function (event) {
                event.stopPropagation();
                setOpen(panel.hidden);
            });

            panel.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            document.addEventListener('click', function () {
                setOpen(false);
            });

            window.setInterval(refreshNotifications, 20000);
        });
    </script>
@endif
