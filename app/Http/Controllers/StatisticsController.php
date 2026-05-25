<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Offer;
use App\Models\Product;
use App\Services\ReservationReleaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(Request $request, ReservationReleaseService $reservationReleaseService): View
    {
        $reservationReleaseService->releaseExpired();

        $filters = $this->filters($request);

        $offerQuery = Offer::query()
            ->with(['customer.group', 'items'])
            ->latest();

        $this->applyOfferFilters($offerQuery, $filters);

        $offers = $offerQuery->get();

        $productsForStock = Product::query()
            ->when($filters['product_id'], fn ($query) => $query->where('id', $filters['product_id']))
            ->get();

        $completedOffers = $offers->where('status', Offer::STATUS_COMPLETED);
        $activeOffers = $offers->whereIn('status', Offer::RESERVING_STATUSES);

        $stockSummary = [
            'ok' => $productsForStock->filter(fn (Product $product) => $product->stock_status === 'ok')->count(),
            'low' => $productsForStock->filter(fn (Product $product) => $product->stock_status === 'low')->count(),
            'critical' => $productsForStock->filter(fn (Product $product) => $product->stock_status === 'critical')->count(),
            'total_stock' => $productsForStock->sum(fn (Product $product) => $product->total_stock),
            'available_stock' => $productsForStock->sum(fn (Product $product) => $product->available_stock),
            'reserved_stock' => $productsForStock->sum(fn (Product $product) => $product->reserved_stock),
        ];

        $statusLabels = $this->statusLabels();

        $offerStatusCounts = collect($statusLabels)
            ->map(fn ($label, $status) => $offers->where('status', $status)->count());

        $financialStats = [
            'completed_revenue' => (float) $completedOffers->sum('total'),
            'active_reserved_value' => (float) $activeOffers->sum('total'),
            'all_offer_value' => (float) $offers->sum('total'),
            'completed_count' => $completedOffers->count(),
            'active_reservations' => $activeOffers->count(),
            'offers_count' => $offers->count(),
        ];

        $topProducts = $this->topProducts($completedOffers);
        $topCustomers = $this->topCustomers($completedOffers);
        $monthlyRevenue = $this->monthlyRevenue($completedOffers);
        $customerGroupRevenue = $this->customerGroupRevenue($completedOffers);

        $charts = [
            'offerStatus' => [
                'labels' => collect($statusLabels)->values(),
                'data' => $offerStatusCounts->values(),
            ],
            'stockStatus' => [
                'labels' => ['OK', 'Niedrig', 'Kritisch'],
                'data' => [$stockSummary['ok'], $stockSummary['low'], $stockSummary['critical']],
            ],
            'topProductsRevenue' => [
                'labels' => $topProducts->pluck('product_name'),
                'data' => $topProducts->pluck('revenue'),
            ],
            'topProductsQuantity' => [
                'labels' => $topProducts->pluck('product_name'),
                'data' => $topProducts->pluck('sold_quantity'),
            ],
            'topCustomersRevenue' => [
                'labels' => $topCustomers->pluck('company_name'),
                'data' => $topCustomers->pluck('revenue'),
            ],
            'monthlyRevenue' => [
                'labels' => $monthlyRevenue->pluck('month'),
                'data' => $monthlyRevenue->pluck('revenue'),
                'count' => $monthlyRevenue->pluck('offers_count'),
            ],
            'customerGroupRevenue' => [
                'labels' => $customerGroupRevenue->pluck('group_name'),
                'data' => $customerGroupRevenue->pluck('revenue'),
            ],
        ];

        $recentCompletedOffers = $completedOffers
            ->sortByDesc(fn (Offer $offer) => $offer->completed_at ?: $offer->created_at)
            ->take(8)
            ->values();

        return view('pages.statistics.index', [
            'filters' => $filters,
            'periods' => $this->periods(),
            'customers' => Customer::query()->orderBy('company_name')->get(),
            'customerGroups' => CustomerGroup::query()
                ->orderByRaw("FIELD(slug, 'gold', 'silver', 'diamond')")
                ->orderBy('name')
                ->get(),
            'products' => Product::query()->orderBy('name')->get(),
            'stockSummary' => $stockSummary,
            'offerStatusCounts' => $offerStatusCounts,
            'financialStats' => $financialStats,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'monthlyRevenue' => $monthlyRevenue,
            'customerGroupRevenue' => $customerGroupRevenue,
            'recentCompletedOffers' => $recentCompletedOffers,
            'statusLabels' => $statusLabels,
            'charts' => $charts,
        ]);
    }

    private function filters(Request $request): array
    {
        $statusLabels = array_keys($this->statusLabels());

        $period = $request->query('period', 'all');
        if (! array_key_exists($period, $this->periods())) {
            $period = 'all';
        }

        $status = $request->query('status');
        if ($status && ! in_array($status, $statusLabels, true)) {
            $status = null;
        }

        return [
            'period' => $period,
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'status' => $status,
            'customer_group_id' => $request->query('customer_group_id'),
            'customer_id' => $request->query('customer_id'),
            'product_id' => $request->query('product_id'),
        ];
    }

    private function applyOfferFilters($query, array $filters): void
    {
        [$from, $to] = $this->dateRange($filters);

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if ($filters['customer_group_id']) {
            $query->whereHas('customer', function ($customerQuery) use ($filters) {
                $customerQuery->where('customer_group_id', $filters['customer_group_id']);
            });
        }

        if ($filters['product_id']) {
            $query->whereHas('items', function ($itemQuery) use ($filters) {
                $itemQuery->where('product_id', $filters['product_id']);
            });
        }
    }

    private function dateRange(array $filters): array
    {
        $now = now();

        return match ($filters['period']) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
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

        return [
            Carbon::parse($filters['date_from'])->startOfDay(),
            Carbon::parse($filters['date_to'])->endOfDay(),
        ];
    }

    private function topProducts(Collection $completedOffers): Collection
    {
        return $completedOffers
            ->flatMap(fn (Offer $offer) => $offer->items)
            ->groupBy('product_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'product_code' => $first->product_code,
                    'product_name' => $first->product_name,
                    'sold_quantity' => (float) $items->sum('quantity'),
                    'revenue' => (float) $items->sum('line_total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();
    }

    private function topCustomers(Collection $completedOffers): Collection
    {
        return $completedOffers
            ->groupBy('customer_id')
            ->map(function (Collection $offers) {
                $first = $offers->first();

                return [
                    'customer_number' => $first->customer?->customer_number ?? '—',
                    'company_name' => $first->customer?->company_name ?? 'Unbekannt',
                    'offers_count' => $offers->count(),
                    'revenue' => (float) $offers->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();
    }

    private function monthlyRevenue(Collection $completedOffers): Collection
    {
        return $completedOffers
            ->groupBy(function (Offer $offer) {
                return ($offer->completed_at ?: $offer->created_at)->format('Y-m');
            })
            ->map(function (Collection $offers, string $month) {
                return [
                    'month' => $month,
                    'revenue' => (float) $offers->sum('total'),
                    'offers_count' => $offers->count(),
                ];
            })
            ->sortBy('month')
            ->values();
    }

    private function customerGroupRevenue(Collection $completedOffers): Collection
    {
        return $completedOffers
            ->groupBy(fn (Offer $offer) => $offer->customer?->group?->name ?? 'Ohne Gruppe')
            ->map(fn (Collection $offers, string $groupName) => [
                'group_name' => $groupName,
                'revenue' => (float) $offers->sum('total'),
            ])
            ->sortByDesc('revenue')
            ->values();
    }

    private function periods(): array
    {
        return [
            'all' => 'Alle Zeiträume',
            'today' => 'Heute',
            '7_days' => 'Letzte 7 Tage',
            '30_days' => 'Letzte 30 Tage',
            'month' => 'Dieser Monat',
            'year' => 'Dieses Jahr',
            'custom' => 'Benutzerdefiniert',
        ];
    }

    private function statusLabels(): array
    {
        return [
            Offer::STATUS_OFFER => 'Angebot',
            Offer::STATUS_IN_PROGRESS => 'In Bearbeitung',
Offer::STATUS_READY => 'Abholbereit',
            Offer::STATUS_COMPLETED => 'Erledigt',
            Offer::STATUS_CANCELLED => 'Storniert',
            Offer::STATUS_RESERVATION_EXPIRED => 'Reservierung abgelaufen',
        ];
    }
}
