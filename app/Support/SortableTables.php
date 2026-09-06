<?php

namespace App\Support;

use App\Models\BranchWithdrawal;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;

class SortableTables
{
    public static function boot(): void
    {
        self::registerScopes();
        self::registerHeaderEnhancer();
    }

    private static function registerScopes(): void
    {
        Product::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['products*', 'verkauf/produkte*'])) {
                return;
            }

            self::apply($builder, [
                'manufacturer' => 'manufacturer_designation',
                'code' => 'product_code',
                'supplier' => 'supplier',
            ]);
        });

        Offer::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['offers*', 'angebote*', 'lager/angebote*'])) {
                return;
            }

            self::apply($builder, [
                'number' => 'offer_number',
                'date' => 'created_at',
                'status' => 'status',
                'total' => 'total',
            ]);
        });

        BranchWithdrawal::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['branch-withdrawals*', 'filialausgaenge*', 'verkauf/filialausgaenge*'])) {
                return;
            }

            self::apply($builder, [
                'number' => 'withdrawal_number',
                'date' => 'created_at',
                'branch' => 'branch_name',
                'status' => 'status',
            ]);
        });

        ProductBatch::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['batches*', 'chargen*'])) {
                return;
            }

            self::apply($builder, [
                'batch' => 'batch_number',
                'received' => 'received_at',
                'expires' => 'expires_at',
                'quantity' => 'quantity',
            ]);
        });

        Customer::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['customers*', 'kunden*', 'crm/kunden*'])) {
                return;
            }

            self::apply($builder, [
                'name' => 'company_name',
                'number' => 'customer_number',
                'group' => 'customer_group_id',
                'city' => 'city',
            ]);
        });

        ProductCategory::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['product-categories*', 'kategorien*'])) {
                return;
            }

            self::apply($builder, [
                'name' => 'name',
                'priority' => 'priority',
            ]);
        });

        Supplier::addGlobalScope('table_sort', function (Builder $builder): void {
            if (! self::matchesPath(['suppliers*', 'lieferanten*'])) {
                return;
            }

            self::apply($builder, [
                'name' => 'company_name',
                'number' => 'supplier_number',
                'contact' => 'contact_person',
                'city' => 'city',
            ]);
        });
    }

    private static function apply(Builder $builder, array $allowed): void
    {
        if (! app()->bound('request') || ! request()->isMethod('GET')) {
            return;
        }

        $sort = trim((string) request()->query('sort', ''));
        $direction = strtolower(trim((string) request()->query('direction', 'asc')));

        if (! isset($allowed[$sort]) || ! in_array($direction, ['asc', 'desc'], true)) {
            return;
        }

        $column = $allowed[$sort];

        $builder->reorder();

        $builder
            ->orderBy($builder->getModel()->qualifyColumn($column), $direction)
            ->orderBy($builder->getModel()->qualifyColumn('id'), $direction);
    }

    private static function matchesPath(array $patterns): bool
    {
        if (! app()->bound('request')) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (request()->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function registerHeaderEnhancer(): void
    {
        Event::listen(RequestHandled::class, function (RequestHandled $event): void {
            $request = $event->request;
            $response = $event->response;

            if (! $request->isMethod('GET') || ! method_exists($response, 'getContent') || ! method_exists($response, 'setContent')) {
                return;
            }

            $contentType = (string) $response->headers->get('Content-Type', '');
            if ($contentType !== '' && ! str_contains(strtolower($contentType), 'text/html')) {
                return;
            }

            $html = $response->getContent();
            if (! is_string($html) || ! str_contains($html, '</body>') || str_contains($html, 'data-sortable-table-enhancer')) {
                return;
            }

            $response->setContent(str_replace('</body>', self::headerScript() . "\n</body>", $html));
        });
    }

    private static function headerScript(): string
    {
        return <<<'HTML'
<script data-sortable-table-enhancer>
(() => {
    const normalize = (value) => (value || '').replace(/\s+/g, ' ').trim().toLocaleLowerCase('de-DE');
    const path = window.location.pathname.toLocaleLowerCase('de-DE');

    const configurations = [
        {
            matches: ['/products', '/verkauf/produkte'],
            columns: {
                'produktbezeichnung': ['name', 'asc'],
                'produkt': ['name', 'asc'],
                'hersteller': ['manufacturer', 'asc'],
                'code-nummer': ['code', 'asc'],
                'produktcode': ['code', 'asc'],
                'lieferant': ['supplier', 'asc'],
            },
        },
        {
            matches: ['/offers', '/angebote', '/lager/angebote'],
            columns: {
                'angebot': ['number', 'desc'],
                'angebotsnummer': ['number', 'desc'],
                'nummer': ['number', 'desc'],
                'datum': ['date', 'desc'],
                'erstellt': ['date', 'desc'],
                'status': ['status', 'asc'],
                'gesamt': ['total', 'desc'],
                'gesamtbetrag': ['total', 'desc'],
            },
        },
        {
            matches: ['/branch-withdrawals', '/filialausgaenge', '/verkauf/filialausgaenge'],
            columns: {
                'filialausgang': ['number', 'desc'],
                'nummer': ['number', 'desc'],
                'datum': ['date', 'desc'],
                'filiale': ['branch', 'asc'],
                'status': ['status', 'asc'],
            },
        },
        {
            matches: ['/batches', '/chargen'],
            columns: {
                'charge': ['batch', 'asc'],
                'chargennummer': ['batch', 'asc'],
                'batchnummer': ['batch', 'asc'],
                'produkt': ['product', 'asc'],
                'wareneingang': ['received', 'asc'],
                'lieferdatum': ['received', 'asc'],
                'ablaufdatum': ['expires', 'asc'],
                'menge': ['quantity', 'asc'],
            },
        },
        {
            matches: ['/customers', '/kunden', '/crm/kunden'],
            columns: {
                'kunde': ['name', 'asc'],
                'name': ['name', 'asc'],
                'firma': ['name', 'asc'],
                'kundennummer': ['number', 'asc'],
                'gruppe': ['group', 'asc'],
                'kundengruppe': ['group', 'asc'],
                'ort': ['city', 'asc'],
                'stadt': ['city', 'asc'],
            },
        },
        {
            matches: ['/product-categories', '/kategorien'],
            columns: {
                'kategorie': ['name', 'asc'],
                'name': ['name', 'asc'],
                'priorität': ['priority', 'asc'],
            },
        },
        {
            matches: ['/suppliers', '/lieferanten'],
            columns: {
                'lieferant': ['name', 'asc'],
                'firma': ['name', 'asc'],
                'lieferantennummer': ['number', 'asc'],
                'nummer': ['number', 'asc'],
                'ansprechpartner': ['contact', 'asc'],
                'ort': ['city', 'asc'],
                'stadt': ['city', 'asc'],
            },
        },
    ];

    const config = configurations.find((entry) => entry.matches.some((match) => path === match || path.startsWith(match + '/')));
    if (!config) return;

    const currentUrl = new URL(window.location.href);
    const currentSort = currentUrl.searchParams.get('sort') || '';
    const currentDirection = currentUrl.searchParams.get('direction') || '';

    document.querySelectorAll('table thead th').forEach((th) => {
        if (th.querySelector('[data-table-sort-link]')) return;

        const label = normalize(th.textContent);
        const definition = config.columns[label];
        if (!definition) return;

        const [sortKey, defaultDirection] = definition;
        const active = currentSort === sortKey;
        const nextDirection = active
            ? (currentDirection === 'asc' ? 'desc' : 'asc')
            : defaultDirection;

        const target = new URL(window.location.href);
        target.searchParams.set('sort', sortKey);
        target.searchParams.set('direction', nextDirection);
        target.searchParams.delete('page');

        const link = document.createElement('a');
        link.href = target.toString();
        link.dataset.tableSortLink = '1';
        link.className = 'table-sort-link' + (active ? ' active' : '');
        link.setAttribute('aria-label', `${th.textContent.trim()} sortieren`);

        const text = document.createElement('span');
        text.textContent = th.textContent.trim();

        const arrow = document.createElement('span');
        arrow.className = 'table-sort-arrow';
        arrow.textContent = active ? (currentDirection === 'desc' ? '↓' : '↑') : '↕';
        arrow.setAttribute('aria-hidden', 'true');

        link.append(text, arrow);
        th.replaceChildren(link);
    });

    if (!document.getElementById('table-sort-link-styles')) {
        const style = document.createElement('style');
        style.id = 'table-sort-link-styles';
        style.textContent = `
            .table-sort-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                color: inherit !important;
                text-decoration: none !important;
                font: inherit;
                letter-spacing: inherit;
                text-transform: inherit;
                cursor: pointer;
            }
            .table-sort-link:hover,
            .table-sort-link.active {
                color: #8a6a00 !important;
            }
            .table-sort-arrow {
                font-size: 12px;
                line-height: 1;
                opacity: .65;
            }
            .table-sort-link.active .table-sort-arrow {
                opacity: 1;
                font-weight: 950;
            }
        `;
        document.head.appendChild(style);
    }
})();
</script>
HTML;
    }
}
