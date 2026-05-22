<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OfferPdfController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferStatusController;
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

    Route::get('/offers', [OfferController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.index');

    Route::get('/offers/create', [OfferController::class, 'create'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.create');

    Route::post('/offers', [OfferController::class, 'store'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.store');

    Route::get('/offers/{offer}', [OfferController::class, 'show'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.show');

    Route::get('/offers/{offer}/edit', [OfferController::class, 'edit'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.edit');

    Route::put('/offers/{offer}', [OfferController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.update');

    Route::put('/offers/{offer}/status', [OfferStatusController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.status');

    Route::get('/offers/{offer}/pdf/{type}', [OfferPdfController::class, 'stream'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.pdf');

    Route::post('/offers/{offer}/cancel', [OfferController::class, 'cancel'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('offers.cancel');

    Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('offers.destroy');

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
    Route::get('/document-templates', [DocumentTemplateController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('document-templates.index');

    Route::put('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('document-templates.update');

});

if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}
