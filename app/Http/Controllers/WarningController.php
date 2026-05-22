<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarningController extends Controller
{
    public function index(Request $request): View
    {
        app(\App\Services\ReservationReleaseService::class)->releaseExpired();

        $filter = $request->query('filter', 'warning');

        $allProducts = Product::query()
            ->with(['batches' => fn ($query) => $query->orderBy('received_at')->orderBy('id')])
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) {
                return [
                    'product' => $product,
                    'status' => $product->stock_status,
                    'total_stock' => $product->total_stock,
                    'reserved_stock' => $product->reserved_stock,
                    'available_stock' => $product->available_stock,
                    'minimum_stock' => (float) $product->minimum_stock,
                    'max_reservable' => $product->max_reservable,
                ];
            });

        $summary = [
            'all' => $allProducts->count(),
            'low' => $allProducts->where('status', 'low')->count(),
            'critical' => $allProducts->where('status', 'critical')->count(),
            'warning' => $allProducts->whereIn('status', ['low', 'critical'])->count(),
        ];

        $products = $allProducts
            ->filter(function (array $row) use ($filter) {
                if ($filter === 'critical') {
                    return $row['status'] === 'critical';
                }

                if ($filter === 'low') {
                    return $row['status'] === 'low';
                }

                if ($filter === 'all') {
                    return true;
                }

                return in_array($row['status'], ['low', 'critical'], true);
            })
            ->values();

        return view('pages.warnings.index', [
            'products' => $products,
            'filter' => $filter,
            'summary' => $summary,
        ]);
    }
}
