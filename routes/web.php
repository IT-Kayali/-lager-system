<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BatchController;
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

    Route::get('/batches/fifo-out', [BatchController::class, 'fifoOutForm'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.fifo-out.form');

    Route::post('/batches/fifo-out', [BatchController::class, 'fifoOut'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.fifo-out.store');

    Route::resource('batches', BatchController::class)
        ->except(['show'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::view('/offers', 'pages.documents.index')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.index');

    Route::resource('customers', CustomerController::class)
        ->except(['show'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE);

    Route::get('/prices', [PriceController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('prices.index');

    Route::put('/prices', [PriceController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('prices.update');

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
