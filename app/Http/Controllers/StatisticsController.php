<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Services\ReservationReleaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(ReservationReleaseService $reservationReleaseService): View
    {
        $reservationReleaseService->releaseExpired();

        $products = Product::query()->get();

        $stockSummary = [
            'ok' => $products->filter(fn (Product $product) => $product->stock_status === 'ok')->count(),
            'low' => $products->filter(fn (Product $product) => $product->stock_status === 'low')->count(),
            'critical' => $products->filter(fn (Product $product) => $product->stock_status === 'critical')->count(),
            'total_stock' => $products->sum(fn (Product $product) => $product->total_stock),
            'available_stock' => $products->sum(fn (Product $product) => $product->available_stock),
            'reserved_stock' => $products->sum(fn (Product $product) => $product->reserved_stock),
        ];

        $offerStatusCounts = Offer::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $financialStats = [
            'completed_revenue' => (float) Offer::query()
                ->where('status', Offer::STATUS_COMPLETED)
                ->sum('total'),

            'active_reserved_value' => (float) Offer::query()
                ->whereIn('status', Offer::RESERVING_STATUSES)
                ->sum('total'),

            'all_offer_value' => (float) Offer::query()
                ->sum('total'),

            'completed_count' => Offer::query()
                ->where('status', Offer::STATUS_COMPLETED)
                ->count(),

            'active_reservations' => Offer::query()
                ->whereIn('status', Offer::RESERVING_STATUSES)
                ->count(),
        ];

        $topProducts = DB::table('offer_items')
            ->join('offers', 'offers.id', '=', 'offer_items.offer_id')
            ->where('offers.status', Offer::STATUS_COMPLETED)
            ->select(
                'offer_items.product_code',
                'offer_items.product_name',
                DB::raw('SUM(offer_items.quantity) as sold_quantity'),
                DB::raw('SUM(offer_items.line_total) as revenue')
            )
            ->groupBy('offer_items.product_code', 'offer_items.product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $topCustomers = DB::table('offers')
            ->join('customers', 'customers.id', '=', 'offers.customer_id')
            ->where('offers.status', Offer::STATUS_COMPLETED)
            ->select(
                'customers.customer_number',
                'customers.company_name',
                DB::raw('COUNT(offers.id) as offers_count'),
                DB::raw('SUM(offers.total) as revenue')
            )
            ->groupBy('customers.customer_number', 'customers.company_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $monthlyRevenue = Offer::query()
            ->where('status', Offer::STATUS_COMPLETED)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as offers_count')
            )
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $recentCompletedOffers = Offer::query()
            ->with('customer')
            ->where('status', Offer::STATUS_COMPLETED)
            ->latest('completed_at')
            ->limit(8)
            ->get();

        return view('pages.statistics.index', [
            'stockSummary' => $stockSummary,
            'offerStatusCounts' => $offerStatusCounts,
            'financialStats' => $financialStats,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'monthlyRevenue' => $monthlyRevenue,
            'recentCompletedOffers' => $recentCompletedOffers,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    private function statusLabels(): array
    {
        return [
            Offer::STATUS_OFFER => 'Angebot',
            Offer::STATUS_IN_PROGRESS => 'In Bearbeitung',
            Offer::STATUS_RESERVED => 'Reserviert',
            Offer::STATUS_READY => 'Abholbereit',
            Offer::STATUS_COMPLETED => 'Erledigt',
            Offer::STATUS_CANCELLED => 'Storniert',
            Offer::STATUS_RESERVATION_EXPIRED => 'Reservierung abgelaufen',
        ];
    }
}
