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

            $response->setContent(str_replace('</body>', self::headerAssets() . "\n</body>", $html));
        });
    }

    private static function headerAssets(): string
    {
        $styleUrl = e(asset('css/sortable-tables.css'));
        $scriptUrl = e(asset('js/sortable-tables.js'));

        return <<<HTML
<link rel="stylesheet" href="{$styleUrl}" data-sortable-table-styles>
<script src="{$scriptUrl}" data-sortable-table-enhancer defer></script>
HTML;
    }
}
