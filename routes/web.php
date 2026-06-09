<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SystemUserController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\OfferPdfController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferStatusController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerWalletTransactionController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::redirect('/home', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])
        ->name('settings.index')
        ->middleware('role:manager');

    Route::put('settings/reservation', [SettingsController::class, 'updateReservation'])
        ->name('settings.reservation.update')
        ->middleware('role:manager');

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::resource('products', ProductController::class)
        
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE . ',' . User::ROLE_WAREHOUSE);

    Route::resource('product-categories', ProductCategoryController::class)
        ->except(['show'])
        ->middleware('role:' . User::ROLE_MANAGER);

    Route::get('/kategorien', function () {
        return redirect()->route('product-categories.index');
    })
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('product-categories.redirect');


    Route::get('/batches/fifo-out', [BatchController::class, 'fifoOutForm'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.fifo-out.form');

    Route::post('/batches/fifo-out', [BatchController::class, 'fifoOut'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.fifo-out.store');

    Route::resource('batches', BatchController::class)
        
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

    Route::resource('suppliers', SupplierController::class)
        
        ->middleware('role:' . User::ROLE_MANAGER);

    Route::post('customers/{customer}/wallet-transactions', [CustomerWalletTransactionController::class, 'store'])
        ->name('customers.wallet-transactions.store')
        ->middleware('role:manager,wholesale');

    Route::resource('customers', CustomerController::class)
        
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE);

    Route::get('/prices', [PriceController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WHOLESALE)
        ->name('prices.index');

    Route::put('/prices', [PriceController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('prices.update');

    Route::get('/warnings', [WarningController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('warnings.index');

    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('statistics.index');

    Route::get('/security', [SecurityController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.index');

    Route::get('/security/users/create', [SystemUserController::class, 'create'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.users.create');

    Route::post('/security/users', [SystemUserController::class, 'store'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.users.store');

    Route::get('/security/users/{user}/edit', [SystemUserController::class, 'edit'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.users.edit');

    Route::put('/security/users/{user}', [SystemUserController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.users.update');

    Route::delete('/security/users/{user}', [SystemUserController::class, 'destroy'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('security.users.destroy');
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


Route::middleware(['auth'])->group(function () {
    Route::resource('branch-withdrawals', \App\Http\Controllers\BranchWithdrawalController::class)
        ->only(['index', 'create', 'store']);
});

