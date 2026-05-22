<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'products' => Schema::hasTable('products') ? DB::table('products')->count() : 0,
            'reservations' => Schema::hasTable('offers') ? DB::table('offers')->where('status', 'reserved')->count() : 0,
            'offers' => Schema::hasTable('offers') ? DB::table('offers')->count() : 0,
            'critical' => 0,
            'users' => User::count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
