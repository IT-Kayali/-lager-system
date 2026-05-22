<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\User;
use App\Services\ReservationReleaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReservationReleaseService $reservationReleaseService): View
    {
        $reservationReleaseService->releaseExpired();

        $stats = [
            'products' => Schema::hasTable('products') ? DB::table('products')->count() : 0,
            'reservations' => Schema::hasTable('offers')
                ? DB::table('offers')->whereIn('status', Offer::RESERVING_STATUSES)->count()
                : 0,
            'offers' => Schema::hasTable('offers') ? DB::table('offers')->count() : 0,
            'critical' => 0,
            'users' => User::count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
