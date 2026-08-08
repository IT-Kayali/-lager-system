<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Services\ReservationReleaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReservationReleaseService $reservationReleaseService): View|RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user?->is_active, 403, 'Dieser Benutzer ist deaktiviert.');

        if ($user->isWarehouse()) {
            return redirect()->route('products.index');
        }

        if ($user->isSales()) {
            return redirect()->route('offers.index');
        }

        abort_unless($user->hasRole(User::ROLE_MANAGER), 403, 'Keine Berechtigung für das Dashboard.');

        $reservationReleaseService->releaseExpired();

        $products = Schema::hasTable('products')
            ? Product::query()->get()
            : collect();

        $criticalProducts = $products->filter(fn (Product $product) => $product->stock_status === 'critical')->count();
        $lowProducts = $products->filter(fn (Product $product) => $product->stock_status === 'low')->count();

        $stats = [
            'products' => $products->count(),
            'reservations' => Schema::hasTable('offers')
                ? DB::table('offers')->whereIn('status', Offer::RESERVING_STATUSES)->count()
                : 0,
            'offers' => Schema::hasTable('offers') ? DB::table('offers')->count() : 0,
            'critical' => $criticalProducts,
            'low' => $lowProducts,
            'users' => User::count(),
        ];

        $latestActivities = Schema::hasTable('activity_logs')
            ? ActivityLog::query()->with('user')->latest()->limit(8)->get()
            : collect();

        return view('dashboard', compact('stats', 'latestActivities'));
    }
}
