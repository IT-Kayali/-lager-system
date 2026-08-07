<?php

namespace App\Services;

use App\Models\BranchWithdrawal;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerWalletTransaction;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function filters(Request $request): array
    {
        $period = (string) $request->query('period', 'year');
        if (! array_key_exists($period, $this->periods())) {
            $period = 'year';
        }

        $status = $request->query('status');
        if ($status && ! in_array($status, Offer::STATUSES, true)) {
            $status = null;
        }

        $shippingMethod = $request->query('shipping_method');
        if ($shippingMethod && ! in_array($shippingMethod, ['Lieferung', 'Abholung'], true)) {
            $shippingMethod = null;
        }

        $branch = $request->query('branch');
        if ($branch && ! array_key_exists($branch, BranchWithdrawal::branches())) {
            $branch = null;
        }

        $branchStatus = $request->query('branch_status');
        if ($branchStatus && ! array_key_exists($branchStatus, BranchWithdrawal::statusLabels())) {
            $branchStatus = null;
        }

        $movementType = $request->query('movement_type');
        if ($movementType && ! array_key_exists($movementType, $this->movementTypes())) {
            $movementType = null;
        }

        return [
            'period' => $period,
            'date_from' => $this->dateValue($request->query('date_from')),
            'date_to' => $this->dateValue($request->query('date_to')),
            'status' => $status,
            'customer_group_id' => $this->integerValue($request->query('customer_group_id')),
            'customer_id' => $this->integerValue($request->query('customer_id')),
            'product_id' => $this->integerValue($request->query('product_id')),
            'category_id' => $this->integerValue($request->query('category_id')),
            'supplier_id' => $this->integerValue($request->query('supplier_id')),
            'user_id' => $this->integerValue($request->query('user_id')),
            'shipping_method' => $shippingMethod,
            'branch' => $branch,
            'branch_status' => $branchStatus,
            'movement_type' => $movementType,
        ];
    }

    public function periods(): array
    {
        return [
            'today' => 'Heute',
            '7_days' => 'Letzte 7 Tage',
            '30_days' => 'Letzte 30 Tage',
            'month' => 'Dieser Monat',
            'quarter' => 'Dieses Quartal',
            'year' => 'Dieses Jahr',
            'all' => 'Gesamtzeitraum',
            'custom' => 'Benutzerdefiniert',
        ];
    }

    public function movementTypes(): array
    {
        return [
            'in' => 'Wareneingang',
            'out' => 'Warenausgang',
            'adjustment' => 'Bestandskorrektur',
        ];
    }

    public function build(array $filters): array
    {
        $sections = $this->sections($filters);
        $context = $this->context($filters, $sections);

        $summary = $this->summary($filters);
        $sales = $sections['sales'] ? $this->sales($filters, $summary) : null;
        $products = $sections['products'] ? $this->products($filters) : null;
        $customers = $sections['customers'] ? $this->customers($filters) : null;
        $stock = $sections['stock'] ? $this->stock($filters) : null;
        $branches = $sections['branches'] ? $this->branches($filters) : null;
        $wallet = $sections['wallet'] ? $this->wallet($filters) : null;
        $operations = $sections['operations'] ? $this->operations($filters) : null;

        return [
            'context' => $context,
            'summary' => $summary,
            'sales' => $sales,
            'products' => $products,
            'customers' => $customers,
            'stock' => $stock,
            'branches' => $branches,
            'wallet' => $wallet,
            'operations' => $operations,
            'charts' => [
                'offerStatus' => $summary['status_chart'],
                'salesTrend' => $sales['trend'] ?? ['labels' => [], 'data' => [], 'orders' => []],
                'shipping' => $sales['shipping_chart'] ?? ['labels' => [], 'data' => []],
                'topProducts' => $products['chart'] ?? ['labels' => [], 'data' => []],
                'topCustomers' => $customers['chart'] ?? ['labels' => [], 'data' => []],
                'stockStatus' => $stock['status_chart'] ?? ['labels' => [], 'data' => []],
                'stockMovements' => $stock['movement_chart'] ?? ['labels' => [], 'in' => [], 'out' => [], 'adjustment' => []],
                'branches' => $branches['chart'] ?? ['labels' => [], 'data' => []],
                'wallet' => $wallet['chart'] ?? ['labels' => [], 'data' => []],
                'employees' => $operations['employee_chart'] ?? ['labels' => [], 'data' => []],
            ],
        ];
    }

    public function sections(array $filters): array
    {
        $contextKeys = [
            'status',
            'customer_group_id',
            'customer_id',
            'product_id',
            'category_id',
            'supplier_id',
            'user_id',
            'shipping_method',
            'branch',
            'branch_status',
            'movement_type',
        ];

        $hasContext = collect($contextKeys)->contains(fn (string $key) => filled($filters[$key] ?? null));

        if (! $hasContext) {
            return [
                'summary' => true,
                'sales' => true,
                'products' => true,
                'customers' => true,
                'stock' => true,
                'branches' => true,
                'wallet' => true,
                'operations' => true,
            ];
        }

        $active = fn (array $keys): bool => collect($keys)
            ->contains(fn (string $key) => filled($filters[$key] ?? null));

        return [
            'summary' => true,
            'sales' => $active(['status', 'customer_group_id', 'customer_id', 'product_id', 'category_id', 'supplier_id', 'user_id', 'shipping_method']),
            'products' => $active(['customer_group_id', 'customer_id', 'product_id', 'category_id', 'supplier_id']),
            'customers' => $active(['customer_group_id', 'customer_id', 'product_id', 'category_id', 'supplier_id']),
            'stock' => $active(['product_id', 'category_id', 'supplier_id', 'movement_type']),
            'branches' => $active(['branch', 'branch_status', 'product_id', 'category_id', 'supplier_id', 'user_id']),
            'wallet' => $active(['customer_group_id', 'customer_id', 'user_id']),
            'operations' => $active(['status', 'user_id']),
        ];
    }

    private function context(array $filters, array $sections): array
    {
        $chips = $this->filterChips($filters);
        $objectChips = collect($chips)->reject(fn (array $chip) => $chip['key'] === 'period')->values();

        if ($objectChips->isEmpty()) {
            $title = 'ERP Gesamtübersicht';
            $subtitle = 'Alle wichtigen Kennzahlen für Verkauf, Kunden, Produkte, Lager und Filialen.';
        } elseif ($objectChips->count() === 1) {
            $chip = $objectChips->first();
            $title = $chip['label'];
            $subtitle = 'Die Seite zeigt nur Auswertungen, die zu diesem Filter fachlich passen.';
        } else {
            $title = 'Gefilterte ERP-Auswertung';
            $subtitle = 'Die angezeigten Bereiche berücksichtigen die gesetzten Filter soweit sie zum jeweiligen Datenbereich gehören.';
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'period_label' => $this->periodLabel($filters),
            'chips' => $chips,
            'sections' => $sections,
            'has_context_filters' => $objectChips->isNotEmpty(),
        ];
    }

    private function filterChips(array $filters): array
    {
        $chips = [];

        if (($filters['period'] ?? 'year') !== 'year') {
            $chips[] = ['key' => 'period', 'label' => 'Zeitraum: ' . $this->periodLabel($filters)];
        }

        if ($filters['status']) {
            $chips[] = ['key' => 'status', 'label' => 'Status: ' . (Offer::STATUS_LABELS[$filters['status']] ?? $filters['status'])];
        }

        if ($filters['customer_group_id'] && ($group = CustomerGroup::find($filters['customer_group_id']))) {
            $chips[] = ['key' => 'customer_group_id', 'label' => 'Kundengruppe: ' . $group->name];
        }

        if ($filters['customer_id'] && ($customer = Customer::find($filters['customer_id']))) {
            $chips[] = ['key' => 'customer_id', 'label' => 'Kunde: ' . $customer->company_name];
        }

        if ($filters['product_id'] && ($product = Product::find($filters['product_id']))) {
            $chips[] = ['key' => 'product_id', 'label' => 'Produkt: ' . $product->name];
        }

        if ($filters['category_id'] && ($category = ProductCategory::find($filters['category_id']))) {
            $chips[] = ['key' => 'category_id', 'label' => 'Kategorie: ' . $category->name];
        }

        if ($filters['supplier_id'] && ($supplier = Supplier::find($filters['supplier_id']))) {
            $chips[] = ['key' => 'supplier_id', 'label' => 'Lieferant: ' . $supplier->company_name];
        }

        if ($filters['user_id'] && ($user = User::find($filters['user_id']))) {
            $chips[] = ['key' => 'user_id', 'label' => 'Mitarbeiter: ' . $user->name];
        }

        if ($filters['shipping_method']) {
            $chips[] = ['key' => 'shipping_method', 'label' => 'Versand: ' . $filters['shipping_method']];
        }

        if ($filters['branch']) {
            $chips[] = ['key' => 'branch', 'label' => 'Filiale: ' . $filters['branch']];
        }

        if ($filters['branch_status']) {
            $chips[] = ['key' => 'branch_status', 'label' => 'Filialstatus: ' . (BranchWithdrawal::statusLabels()[$filters['branch_status']] ?? $filters['branch_status'])];
        }

        if ($filters['movement_type']) {
            $chips[] = ['key' => 'movement_type', 'label' => 'Lagerbewegung: ' . ($this->movementTypes()[$filters['movement_type']] ?? $filters['movement_type'])];
        }

        return $chips;
    }

    private function summary(array $filters): array
    {
        $offerQuery = $this->offerQuery($filters);
        $offersCount = (clone $offerQuery)->count();
        $offersValue = (float) (clone $offerQuery)->sum('total');

        $statusCounts = (clone $offerQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $completedOrders = $this->completedOrderCount($filters);
        $completedRevenue = $this->completedRevenue($filters);
        $averageOrderValue = $completedOrders > 0 ? $completedRevenue / $completedOrders : 0.0;

        $reservedValue = (float) (clone $offerQuery)
            ->whereIn('status', Offer::RESERVING_STATUSES)
            ->sum('total');

        $cancelled = (int) ($statusCounts[Offer::STATUS_CANCELLED] ?? 0);
        $expired = (int) ($statusCounts[Offer::STATUS_RESERVATION_EXPIRED] ?? 0);
        $completedInPipeline = (int) ($statusCounts[Offer::STATUS_COMPLETED] ?? 0);
        $finalDecisions = $completedInPipeline + $cancelled + $expired;
        $conversionRate = $finalDecisions > 0 ? ($completedInPipeline / $finalDecisions) * 100 : 0.0;
        $cancelRate = $offersCount > 0 ? ($cancelled / $offersCount) * 100 : 0.0;

        [$previousRevenue, $changePercent] = $this->revenueComparison($filters, $completedRevenue);

        $statusLabels = Offer::STATUS_LABELS;

        return [
            'completed_revenue' => $completedRevenue,
            'completed_orders' => $completedOrders,
            'average_order_value' => $averageOrderValue,
            'offers_count' => $offersCount,
            'offers_value' => $offersValue,
            'reserved_value' => $reservedValue,
            'conversion_rate' => $conversionRate,
            'cancel_rate' => $cancelRate,
            'previous_revenue' => $previousRevenue,
            'revenue_change_percent' => $changePercent,
            'status_counts' => collect($statusLabels)->mapWithKeys(fn (string $label, string $status) => [$status => (int) ($statusCounts[$status] ?? 0)]),
            'status_chart' => [
                'labels' => array_values($statusLabels),
                'data' => collect(array_keys($statusLabels))->map(fn (string $status) => (int) ($statusCounts[$status] ?? 0))->values(),
            ],
        ];
    }

    private function sales(array $filters, array $summary): array
    {
        $trend = $this->salesTrend($filters);

        $shippingCounts = $this->completedOfferQuery($filters)
            ->select('shipping_method', DB::raw('COUNT(*) as total'))
            ->groupBy('shipping_method')
            ->pluck('total', 'shipping_method');

        $deliveryCount = (int) ($shippingCounts['Lieferung'] ?? 0);
        $pickupCount = (int) ($shippingCounts['Abholung'] ?? 0);
        $cartons = (int) $this->completedOfferQuery($filters)
            ->where('shipping_method', 'Lieferung')
            ->sum('carton_count');

        $processingTimes = $this->completedOfferQuery($filters)
            ->whereNotNull('completed_at')
            ->get(['created_at', 'completed_at'])
            ->map(fn (Offer $offer) => $offer->created_at->diffInMinutes($offer->completed_at) / 60);

        $recent = $this->completedOfferQuery($filters)
            ->with(['customer:id,company_name,customer_number', 'user:id,name'])
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return [
            'trend' => $trend,
            'delivery_count' => $deliveryCount,
            'pickup_count' => $pickupCount,
            'cartons' => $cartons,
            'average_processing_hours' => $processingTimes->isNotEmpty() ? (float) $processingTimes->avg() : 0.0,
            'shipping_chart' => [
                'labels' => ['Lieferung', 'Abholung'],
                'data' => [$deliveryCount, $pickupCount],
            ],
            'recent' => $recent,
            'completed_revenue' => $summary['completed_revenue'],
            'completed_orders' => $summary['completed_orders'],
        ];
    }

    private function products(array $filters): array
    {
        $items = $this->completedItemQuery($filters)
            ->select([
                'product_id',
                'product_code',
                'product_name',
                'unit',
                DB::raw('SUM(quantity) as sold_quantity'),
                DB::raw('SUM(line_total) as revenue'),
                DB::raw('COUNT(DISTINCT offer_id) as orders_count'),
            ])
            ->groupBy('product_id', 'product_code', 'product_name', 'unit')
            ->orderByDesc('revenue')
            ->get();

        $top = $items->take(10)->values();
        $totalRevenue = (float) $items->sum(fn ($row) => (float) $row->revenue);
        $totalQuantity = (float) $items->sum(fn ($row) => (float) $row->sold_quantity);

        $soldProductIds = $items->pluck('product_id')->filter()->unique()->values();

        $withoutSales = $this->productScopeQuery($filters)
            ->when($soldProductIds->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $soldProductIds))
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'product_code', 'name', 'unit', 'minimum_stock']);

        return [
            'top' => $top,
            'total_revenue' => $totalRevenue,
            'total_quantity' => $totalQuantity,
            'sold_products_count' => $soldProductIds->count(),
            'products_without_sales' => $withoutSales,
            'chart' => [
                'labels' => $top->pluck('product_name')->values(),
                'data' => $top->pluck('revenue')->map(fn ($value) => (float) $value)->values(),
            ],
        ];
    }

    private function customers(array $filters): array
    {
        $activeCustomerIds = $this->completedOfferQuery($filters)
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id')
            ->values();

        $customerOrderCounts = $this->completedOfferQuery($filters)
            ->whereNotNull('customer_id')
            ->get(['customer_id'])
            ->countBy('customer_id');

        $repeatCustomers = $customerOrderCounts->filter(fn (int $count) => $count >= 2)->count();

        [$from, $to] = $this->dateRange($filters);
        $newCustomerQuery = Customer::query();
        $this->applyCustomerFilters($newCustomerQuery, $filters);
        if ($activeCustomerIds->isNotEmpty()) {
            $newCustomerQuery->whereIn('id', $activeCustomerIds);
        }
        if ($from && $to) {
            $newCustomerQuery->whereBetween('created_at', [$from, $to]);
        }
        $newCustomers = $newCustomerQuery->count();

        $top = $this->topCustomers($filters);

        return [
            'active_customers' => $activeCustomerIds->count(),
            'new_customers' => $newCustomers,
            'repeat_customers' => $repeatCustomers,
            'top' => $top,
            'chart' => [
                'labels' => $top->pluck('company_name')->values(),
                'data' => $top->pluck('revenue')->map(fn ($value) => (float) $value)->values(),
            ],
        ];
    }

    private function stock(array $filters): array
    {
        $products = $this->productScopeQuery($filters)
            ->orderBy('name')
            ->get(['id', 'product_code', 'name', 'unit', 'minimum_stock', 'supplier_id']);

        $productIds = $products->pluck('id');

        $batchTotals = $productIds->isEmpty()
            ? collect()
            : ProductBatch::query()
                ->whereIn('product_id', $productIds)
                ->select('product_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('product_id')
                ->pluck('total', 'product_id');

        $reservationTotals = $productIds->isEmpty()
            ? collect()
            : OfferItem::query()
                ->join('offers', 'offers.id', '=', 'offer_items.offer_id')
                ->whereIn('offer_items.product_id', $productIds)
                ->whereIn('offers.status', Offer::RESERVING_STATUSES)
                ->select('offer_items.product_id', DB::raw('SUM(offer_items.quantity) as total'))
                ->groupBy('offer_items.product_id')
                ->pluck('total', 'product_id');

        $rows = $products->map(function (Product $product) use ($batchTotals, $reservationTotals) {
            $total = (float) ($batchTotals[$product->id] ?? 0);
            $reserved = (float) ($reservationTotals[$product->id] ?? 0);
            $available = max(0, $total - $reserved);
            $minimum = (float) $product->minimum_stock;

            $status = 'ok';
            if ($minimum > 0 && $available <= $minimum) {
                $status = 'critical';
            } elseif ($minimum > 0 && $available <= ($minimum * 1.10)) {
                $status = 'low';
            }

            return [
                'id' => $product->id,
                'product_code' => $product->product_code,
                'name' => $product->name,
                'unit' => $product->unit,
                'minimum' => $minimum,
                'total' => $total,
                'reserved' => $reserved,
                'available' => $available,
                'status' => $status,
            ];
        });

        $today = now()->startOfDay();
        $expiringQuery = ProductBatch::query()
            ->with('product:id,name,product_code,unit')
            ->whereIn('product_id', $productIds)
            ->where('quantity', '>', 0)
            ->whereNotNull('expires_at');

        $expiredBatches = (clone $expiringQuery)->whereDate('expires_at', '<', $today)->get();
        $expiringBatches = (clone $expiringQuery)
            ->whereDate('expires_at', '>=', $today)
            ->whereDate('expires_at', '<=', $today->copy()->addDays(30))
            ->orderBy('expires_at')
            ->get();

        $movementQuery = $this->movementQuery($filters, $productIds);
        $movementRows = (clone $movementQuery)->get(['type', 'quantity', 'created_at']);

        $movementIn = (float) $movementRows->where('type', 'in')->sum(fn (StockMovement $movement) => max(0, (float) $movement->quantity));
        $movementOut = abs((float) $movementRows->where('type', 'out')->sum(fn (StockMovement $movement) => min(0, (float) $movement->quantity)));
        $adjustment = (float) $movementRows->where('type', 'adjustment')->sum(fn (StockMovement $movement) => (float) $movement->quantity);

        $recentMovements = (clone $movementQuery)
            ->with(['product:id,name,product_code,unit', 'user:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return [
            'product_count' => $rows->count(),
            'total_stock' => (float) $rows->sum('total'),
            'available_stock' => (float) $rows->sum('available'),
            'reserved_stock' => (float) $rows->sum('reserved'),
            'ok_count' => $rows->where('status', 'ok')->count(),
            'low_count' => $rows->where('status', 'low')->count(),
            'critical_count' => $rows->where('status', 'critical')->count(),
            'critical_products' => $rows->whereIn('status', ['critical', 'low'])->sortBy('available')->take(10)->values(),
            'expired_batches' => $expiredBatches,
            'expiring_batches' => $expiringBatches,
            'expired_quantity' => (float) $expiredBatches->sum(fn (ProductBatch $batch) => (float) $batch->quantity),
            'expiring_quantity' => (float) $expiringBatches->sum(fn (ProductBatch $batch) => (float) $batch->quantity),
            'movement_in' => $movementIn,
            'movement_out' => $movementOut,
            'movement_adjustment' => $adjustment,
            'recent_movements' => $recentMovements,
            'status_chart' => [
                'labels' => ['OK', 'Niedrig', 'Kritisch'],
                'data' => [$rows->where('status', 'ok')->count(), $rows->where('status', 'low')->count(), $rows->where('status', 'critical')->count()],
            ],
            'movement_chart' => $this->movementTrend($movementRows, $filters),
        ];
    }

    private function branches(array $filters): array
    {
        $base = $this->branchQuery($filters, false);
        $statusCounts = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $issued = $this->branchQuery($filters, true)
            ->with(['items.product.categories', 'user:id,name', 'processor:id,name'])
            ->get();

        $itemRows = $issued->flatMap(function (BranchWithdrawal $withdrawal) use ($filters) {
            return $withdrawal->items
                ->filter(fn ($item) => $this->productMatchesFilters($item->product, $filters))
                ->map(fn ($item) => [
                    'branch' => $withdrawal->branch_name,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Unbekannt',
                    'unit' => $item->product?->unit ?? '',
                    'quantity' => (float) $item->quantity,
                ]);
        });

        $topProducts = $itemRows
            ->groupBy('product_id')
            ->map(function (Collection $items) {
                $first = $items->first();
                return [
                    'product_name' => $first['product_name'],
                    'unit' => $first['unit'],
                    'quantity' => (float) $items->sum('quantity'),
                ];
            })
            ->sortByDesc('quantity')
            ->take(10)
            ->values();

        $byBranch = $itemRows
            ->groupBy('branch')
            ->map(fn (Collection $items, string $branch) => [
                'branch' => $branch,
                'quantity' => (float) $items->sum('quantity'),
            ])
            ->sortByDesc('quantity')
            ->values();

        $recent = (clone $base)
            ->with(['items.product:id,name,unit', 'user:id,name', 'processor:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return [
            'total_count' => (clone $base)->count(),
            'issued_count' => (int) ($statusCounts[BranchWithdrawal::STATUS_ISSUED] ?? 0),
            'open_count' => (int) ($statusCounts[BranchWithdrawal::STATUS_OPEN] ?? 0),
            'in_progress_count' => (int) ($statusCounts[BranchWithdrawal::STATUS_IN_PROGRESS] ?? 0),
            'cancelled_count' => (int) ($statusCounts[BranchWithdrawal::STATUS_CANCELLED] ?? 0),
            'issued_quantity' => (float) $itemRows->sum('quantity'),
            'top_products' => $topProducts,
            'by_branch' => $byBranch,
            'recent' => $recent,
            'chart' => [
                'labels' => $byBranch->pluck('branch')->values(),
                'data' => $byBranch->pluck('quantity')->values(),
            ],
        ];
    }

    private function wallet(array $filters): array
    {
        $query = CustomerWalletTransaction::query();
        [$from, $to] = $this->dateRange($filters);
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if ($filters['customer_group_id']) {
            $query->whereHas('customer', fn (Builder $customer) => $customer->where('customer_group_id', $filters['customer_group_id']));
        }
        if ($filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        $transactions = (clone $query)
            ->with(['customer:id,company_name,customer_number', 'user:id,name'])
            ->get();

        $credits = (float) $transactions->filter(fn (CustomerWalletTransaction $row) => (float) $row->amount > 0)->sum(fn ($row) => (float) $row->amount);
        $debits = abs((float) $transactions->filter(fn (CustomerWalletTransaction $row) => (float) $row->amount < 0)->sum(fn ($row) => (float) $row->amount));

        $byCustomer = $transactions
            ->groupBy('customer_id')
            ->map(function (Collection $rows) {
                $first = $rows->first();
                return [
                    'company_name' => $first->customer?->company_name ?? 'Unbekannt',
                    'customer_number' => $first->customer?->customer_number ?? '—',
                    'net' => (float) $rows->sum(fn ($row) => (float) $row->amount),
                    'transactions' => $rows->count(),
                ];
            })
            ->sortByDesc(fn (array $row) => abs($row['net']))
            ->take(10)
            ->values();

        $selectedBalance = null;
        if ($filters['customer_id'] && ($customer = Customer::find($filters['customer_id']))) {
            $selectedBalance = (float) $customer->wallet_balance;
        }

        return [
            'transactions_count' => $transactions->count(),
            'credits' => $credits,
            'debits' => $debits,
            'net' => $credits - $debits,
            'selected_customer_balance' => $selectedBalance,
            'top_customers' => $byCustomer,
            'recent' => $transactions->sortByDesc('created_at')->take(8)->values(),
            'chart' => [
                'labels' => ['Gutschriften', 'Belastungen'],
                'data' => [$credits, $debits],
            ],
        ];
    }

    private function operations(array $filters): array
    {
        $offerQuery = $this->offerQuery($filters);
        $expiringSoon = (clone $offerQuery)
            ->whereIn('status', Offer::RESERVING_STATUSES)
            ->whereNotNull('reserved_until')
            ->whereBetween('reserved_until', [now(), now()->addDay()])
            ->count();

        $completed = $this->completedOfferQuery($filters)
            ->whereNotNull('completed_at')
            ->get(['user_id', 'total', 'created_at', 'completed_at']);

        $averageHours = $completed->isNotEmpty()
            ? (float) $completed->avg(fn (Offer $offer) => $offer->created_at->diffInMinutes($offer->completed_at) / 60)
            : 0.0;

        $employeeRows = $completed
            ->groupBy('user_id')
            ->map(function (Collection $offers, $userId) {
                $user = $userId ? User::find($userId) : null;
                return [
                    'name' => $user?->name ?? 'Unbekannt',
                    'orders' => $offers->count(),
                    'revenue' => (float) $offers->sum(fn (Offer $offer) => (float) $offer->total),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        return [
            'expiring_soon' => $expiringSoon,
            'average_processing_hours' => $averageHours,
            'employee_rows' => $employeeRows,
            'employee_chart' => [
                'labels' => $employeeRows->pluck('name')->values(),
                'data' => $employeeRows->pluck('revenue')->values(),
            ],
        ];
    }

    private function offerQuery(array $filters, ?array $rangeOverride = null): Builder
    {
        $query = Offer::query();
        $this->applyOfferFilters($query, $filters, true, true);

        [$from, $to] = $rangeOverride ?? $this->dateRange($filters);
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query;
    }

    private function completedOfferQuery(array $filters, ?array $rangeOverride = null): Builder
    {
        $query = Offer::query();
        $this->applyOfferFilters($query, $filters, false, true);
        $query->where('status', Offer::STATUS_COMPLETED);

        if ($filters['status'] && $filters['status'] !== Offer::STATUS_COMPLETED) {
            $query->whereRaw('1 = 0');
        }

        [$from, $to] = $rangeOverride ?? $this->dateRange($filters);
        $this->applyCompletedDateRange($query, $from, $to);

        return $query;
    }

    private function completedItemQuery(array $filters, ?array $rangeOverride = null): Builder
    {
        $query = OfferItem::query();

        $query->whereHas('offer', function (Builder $offer) use ($filters, $rangeOverride) {
            $this->applyOfferFilters($offer, $filters, false, false);
            $offer->where('status', Offer::STATUS_COMPLETED);

            if ($filters['status'] && $filters['status'] !== Offer::STATUS_COMPLETED) {
                $offer->whereRaw('1 = 0');
            }

            [$from, $to] = $rangeOverride ?? $this->dateRange($filters);
            $this->applyCompletedDateRange($offer, $from, $to);
        });

        $this->applyItemProductFilters($query, $filters);

        return $query;
    }

    private function applyOfferFilters(Builder $query, array $filters, bool $includeStatus, bool $includeProductFilters): void
    {
        if ($includeStatus && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if ($filters['customer_group_id']) {
            $query->whereHas('customer', fn (Builder $customer) => $customer->where('customer_group_id', $filters['customer_group_id']));
        }
        if ($filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }
        if ($filters['shipping_method']) {
            $query->where('shipping_method', $filters['shipping_method']);
        }
        if ($includeProductFilters && $this->hasProductScope($filters)) {
            $query->whereHas('items', function (Builder $item) use ($filters) {
                $this->applyItemProductFilters($item, $filters);
            });
        }
    }

    private function applyItemProductFilters(Builder $query, array $filters): void
    {
        if ($filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }
        if ($filters['category_id']) {
            $query->whereHas('product.categories', fn (Builder $category) => $category->whereKey($filters['category_id']));
        }
        if ($filters['supplier_id']) {
            $query->whereHas('product', fn (Builder $product) => $product->where('supplier_id', $filters['supplier_id']));
        }
    }

    private function productScopeQuery(array $filters): Builder
    {
        $query = Product::query();
        if ($filters['product_id']) {
            $query->whereKey($filters['product_id']);
        }
        if ($filters['category_id']) {
            $query->whereHas('categories', fn (Builder $category) => $category->whereKey($filters['category_id']));
        }
        if ($filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query;
    }

    private function applyCustomerFilters(Builder $query, array $filters): void
    {
        if ($filters['customer_id']) {
            $query->whereKey($filters['customer_id']);
        }
        if ($filters['customer_group_id']) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }
    }

    private function movementQuery(array $filters, Collection $productIds): Builder
    {
        $query = StockMovement::query();

        if ($productIds->isNotEmpty()) {
            $query->whereIn('product_id', $productIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        [$from, $to] = $this->dateRange($filters);
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }
        if ($filters['movement_type']) {
            $query->where('type', $filters['movement_type']);
        }

        return $query;
    }

    private function branchQuery(array $filters, bool $issuedOnly): Builder
    {
        $query = BranchWithdrawal::query();

        if ($filters['branch']) {
            $query->where('branch_name', $filters['branch']);
        }
        if ($filters['branch_status']) {
            $query->where('status', $filters['branch_status']);
        }
        if ($issuedOnly) {
            $query->where('status', BranchWithdrawal::STATUS_ISSUED);
        }
        if ($filters['user_id']) {
            $query->where(function (Builder $user) use ($filters) {
                $user->where('user_id', $filters['user_id'])
                    ->orWhere('processed_by', $filters['user_id']);
            });
        }
        if ($this->hasProductScope($filters)) {
            $query->whereHas('items', function (Builder $item) use ($filters) {
                $this->applyItemProductFilters($item, $filters);
            });
        }

        [$from, $to] = $this->dateRange($filters);
        if ($from && $to) {
            if ($issuedOnly) {
                $query->where(function (Builder $date) use ($from, $to) {
                    $date->where(function (Builder $processed) use ($from, $to) {
                        $processed->whereNotNull('processed_at')->whereBetween('processed_at', [$from, $to]);
                    })->orWhere(function (Builder $legacy) use ($from, $to) {
                        $legacy->whereNull('processed_at')->whereBetween('created_at', [$from, $to]);
                    });
                });
            } else {
                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        return $query;
    }

    private function productMatchesFilters(?Product $product, array $filters): bool
    {
        if (! $product) {
            return false;
        }
        if ($filters['product_id'] && $product->id !== $filters['product_id']) {
            return false;
        }
        if ($filters['supplier_id'] && (int) $product->supplier_id !== (int) $filters['supplier_id']) {
            return false;
        }
        if ($filters['category_id'] && ! $product->categories->contains('id', $filters['category_id'])) {
            return false;
        }

        return true;
    }

    private function topCustomers(array $filters): Collection
    {
        if ($this->hasProductScope($filters)) {
            $items = $this->completedItemQuery($filters)
                ->with(['offer.customer:id,company_name,customer_number'])
                ->get(['offer_id', 'line_total']);

            return $items
                ->filter(fn (OfferItem $item) => $item->offer?->customer)
                ->groupBy(fn (OfferItem $item) => $item->offer->customer_id)
                ->map(function (Collection $rows) {
                    $first = $rows->first();
                    $customer = $first->offer->customer;
                    return [
                        'customer_number' => $customer->customer_number,
                        'company_name' => $customer->company_name,
                        'orders_count' => $rows->pluck('offer_id')->unique()->count(),
                        'revenue' => (float) $rows->sum(fn (OfferItem $row) => (float) $row->line_total),
                    ];
                })
                ->sortByDesc('revenue')
                ->take(10)
                ->values();
        }

        $rows = $this->completedOfferQuery($filters)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $customers = Customer::query()
            ->whereIn('id', $rows->pluck('customer_id'))
            ->get(['id', 'customer_number', 'company_name'])
            ->keyBy('id');

        return $rows->map(function ($row) use ($customers) {
            $customer = $customers[$row->customer_id] ?? null;
            return [
                'customer_number' => $customer?->customer_number ?? '—',
                'company_name' => $customer?->company_name ?? 'Unbekannt',
                'orders_count' => (int) $row->orders_count,
                'revenue' => (float) $row->revenue,
            ];
        })->values();
    }

    private function completedRevenue(array $filters, ?array $rangeOverride = null): float
    {
        if ($this->hasProductScope($filters)) {
            return (float) $this->completedItemQuery($filters, $rangeOverride)->sum('line_total');
        }

        return (float) $this->completedOfferQuery($filters, $rangeOverride)->sum('total');
    }

    private function completedOrderCount(array $filters, ?array $rangeOverride = null): int
    {
        if ($this->hasProductScope($filters)) {
            return $this->completedItemQuery($filters, $rangeOverride)->distinct()->count('offer_id');
        }

        return $this->completedOfferQuery($filters, $rangeOverride)->count();
    }

    private function revenueComparison(array $filters, float $currentRevenue): array
    {
        [$from, $to] = $this->dateRange($filters);
        if (! $from || ! $to) {
            return [null, null];
        }

        $days = max(1, $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);
        $previousTo = $from->copy()->subSecond();
        $previousFrom = $from->copy()->subDays($days);
        $previousRevenue = $this->completedRevenue($filters, [$previousFrom, $previousTo]);

        if ($previousRevenue == 0.0) {
            return [$previousRevenue, $currentRevenue == 0.0 ? 0.0 : null];
        }

        return [$previousRevenue, (($currentRevenue - $previousRevenue) / $previousRevenue) * 100];
    }

    private function salesTrend(array $filters): array
    {
        $granularity = $this->trendGranularity($filters);
        $buckets = [];

        if ($this->hasProductScope($filters)) {
            $rows = $this->completedItemQuery($filters)
                ->with('offer:id,completed_at,created_at')
                ->get(['offer_id', 'line_total']);

            foreach ($rows as $row) {
                $date = $row->offer?->completed_at ?: $row->offer?->created_at;
                if (! $date) {
                    continue;
                }
                $key = $date->format($granularity === 'day' ? 'Y-m-d' : 'Y-m');
                $buckets[$key]['revenue'] = ($buckets[$key]['revenue'] ?? 0) + (float) $row->line_total;
                $buckets[$key]['orders'][$row->offer_id] = true;
            }
        } else {
            $rows = $this->completedOfferQuery($filters)->get(['id', 'total', 'completed_at', 'created_at']);
            foreach ($rows as $row) {
                $date = $row->completed_at ?: $row->created_at;
                $key = $date->format($granularity === 'day' ? 'Y-m-d' : 'Y-m');
                $buckets[$key]['revenue'] = ($buckets[$key]['revenue'] ?? 0) + (float) $row->total;
                $buckets[$key]['orders'][$row->id] = true;
            }
        }

        ksort($buckets);
        $labels = [];
        $revenue = [];
        $orders = [];
        foreach ($buckets as $key => $bucket) {
            $labels[] = Carbon::parse($granularity === 'day' ? $key : $key . '-01')->format($granularity === 'day' ? 'd.m.' : 'm/Y');
            $revenue[] = round((float) ($bucket['revenue'] ?? 0), 2);
            $orders[] = count($bucket['orders'] ?? []);
        }

        return ['labels' => $labels, 'data' => $revenue, 'orders' => $orders];
    }

    private function movementTrend(Collection $rows, array $filters): array
    {
        $granularity = $this->trendGranularity($filters);
        $buckets = [];

        foreach ($rows as $row) {
            $key = $row->created_at->format($granularity === 'day' ? 'Y-m-d' : 'Y-m');
            $buckets[$key] ??= ['in' => 0.0, 'out' => 0.0, 'adjustment' => 0.0];
            $quantity = (float) $row->quantity;
            if ($row->type === 'in') {
                $buckets[$key]['in'] += max(0, $quantity);
            } elseif ($row->type === 'out') {
                $buckets[$key]['out'] += abs(min(0, $quantity));
            } elseif ($row->type === 'adjustment') {
                $buckets[$key]['adjustment'] += $quantity;
            }
        }

        ksort($buckets);
        $labels = [];
        $in = [];
        $out = [];
        $adjustment = [];
        foreach ($buckets as $key => $bucket) {
            $labels[] = Carbon::parse($granularity === 'day' ? $key : $key . '-01')->format($granularity === 'day' ? 'd.m.' : 'm/Y');
            $in[] = round($bucket['in'], 3);
            $out[] = round($bucket['out'], 3);
            $adjustment[] = round($bucket['adjustment'], 3);
        }

        return compact('labels', 'in', 'out', 'adjustment');
    }

    private function trendGranularity(array $filters): string
    {
        [$from, $to] = $this->dateRange($filters);
        if ($from && $to && $from->diffInDays($to) <= 45) {
            return 'day';
        }

        return 'month';
    }

    private function hasProductScope(array $filters): bool
    {
        return filled($filters['product_id']) || filled($filters['category_id']) || filled($filters['supplier_id']);
    }

    private function applyCompletedDateRange(Builder $query, ?Carbon $from, ?Carbon $to): void
    {
        if (! $from || ! $to) {
            return;
        }

        $query->where(function (Builder $date) use ($from, $to) {
            $date->where(function (Builder $completed) use ($from, $to) {
                $completed->whereNotNull('completed_at')->whereBetween('completed_at', [$from, $to]);
            })->orWhere(function (Builder $legacy) use ($from, $to) {
                $legacy->whereNull('completed_at')->whereBetween('created_at', [$from, $to]);
            });
        });
    }

    private function dateRange(array $filters): array
    {
        $now = now();

        return match ($filters['period']) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => $this->customDateRange($filters),
            default => [null, null],
        };
    }

    private function customDateRange(array $filters): array
    {
        if (! $filters['date_from'] || ! $filters['date_to']) {
            return [null, null];
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $filters['date_from'])->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $filters['date_to'])->endOfDay();
        } catch (\Throwable) {
            return [null, null];
        }

        if ($from->gt($to)) {
            return [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function periodLabel(array $filters): string
    {
        if ($filters['period'] === 'custom' && $filters['date_from'] && $filters['date_to']) {
            return Carbon::parse($filters['date_from'])->format('d.m.Y') . ' – ' . Carbon::parse($filters['date_to'])->format('d.m.Y');
        }

        return $this->periods()[$filters['period']] ?? 'Dieses Jahr';
    }

    private function integerValue(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $integer = (int) $value;
        return $integer > 0 ? $integer : null;
    }

    private function dateValue(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }
}
