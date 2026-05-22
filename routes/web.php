<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::redirect('/home', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::resource('products', ProductController::class)
        ->except(['show'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE . ',' . User::ROLE_WAREHOUSE);

    Route::view('/batches', 'pages.batches.index')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.index');

    Route::view('/offers', 'pages.documents.index')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.index');

    Route::view('/customers', 'pages.customers.index')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('customers.index');

    Route::view('/prices', 'pages.prices.index')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('prices.index');

    Route::view('/warnings', 'pages.warnings.index')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('warnings.index');

    Route::view('/statistics', 'pages.statistics.index')
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('statistics.index');

    Route::view('/security', 'pages.security.index')
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.index');
});

if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}
