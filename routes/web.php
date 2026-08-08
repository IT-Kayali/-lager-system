<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerWalletTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferPdfController;
use App\Http\Controllers\OfferStatusController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductExcelController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SystemUserController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\WarehouseOfferController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::redirect('/home', '/dashboard')->name('home');
Route::get('/login-background', [SettingsController::class, 'loginBackground'])
    ->name('login.background');
Route::get('/login-logo', [SettingsController::class, 'loginLogo'])
    ->name('login.logo');
Route::get('/site-favicon', [SettingsController::class, 'siteFavicon'])
    ->name('site.favicon');

Route::middleware(['auth'])->group(function () {
    /*
     * Globale Systemeinstellungen: ausschließlich Admin.
     */
    Route::get('settings', [SettingsController::class, 'index'])
        ->name('settings.index')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/reservation', [SettingsController::class, 'updateReservation'])
        ->name('settings.reservation.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/button-appearance', [SettingsController::class, 'updateButtonAppearance'])
        ->name('settings.button-appearance.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/login-appearance', [SettingsController::class, 'updateLoginAppearance'])
        ->name('settings.login-appearance.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::post('settings/customer-groups', [CustomerGroupController::class, 'store'])
        ->name('customer-groups.store')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/customer-groups/{customerGroup}', [CustomerGroupController::class, 'update'])
        ->name('customer-groups.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::delete('settings/customer-groups/{customerGroup}', [CustomerGroupController::class, 'destroy'])
        ->name('customer-groups.destroy')
        ->middleware('role:' . User::ROLE_ADMIN);

    /*
     * Dashboard entscheidet selbst über das rollenabhängige Login-Ziel:
     * Admin/Manager -> Dashboard, Lager -> Produkte, Verkauf -> Angebote.
     */
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    /* Lager / Produktverwaltung */
    Route::get('/products/excel/export', [ProductExcelController::class, 'export'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('products.excel.export');

    Route::get('/products/excel/import', [ProductExcelController::class, 'importForm'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('products.excel.import.form');

    Route::post('/products/excel/import', [ProductExcelController::class, 'import'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('products.excel.import');

    Route::resource('products', ProductController::class)
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::resource('product-categories', ProductCategoryController::class)
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::get('/kategorien', function () {
        return redirect()->route('product-categories.index');
    })
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('product-categories.redirect');

    Route::get('/batches/fifo-out', [BatchController::class, 'fifoOutForm'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.fifo-out.form');

    Route::post('/batches/fifo-out', [BatchController::class, 'fifoOut'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('batches.fifo-out.store');

    Route::resource('batches', BatchController::class)
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::resource('branch-withdrawals', \App\Http\Controllers\BranchWithdrawalController::class)
        ->only(['index', 'create', 'store'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    /*
     * Normale Verkaufsansicht: Manager und Verkauf.
     */
    Route::get('/offers', [OfferController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.index');

    Route::get('/offers/create', [OfferController::class, 'create'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.create');

    Route::post('/offers', [OfferController::class, 'store'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.store');

    Route::get('/offers/{offer}', [OfferController::class, 'show'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.show');

    Route::get('/offers/{offer}/edit', [OfferController::class, 'edit'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.edit');

    Route::put('/offers/{offer}', [OfferController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.update');

    Route::put('/offers/{offer}/status', [OfferStatusController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.status');

    Route::get('/offers/{offer}/pdf/{type}', [OfferPdfController::class, 'stream'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.pdf');

    Route::post('/offers/{offer}/cancel', [OfferController::class, 'cancel'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('offers.cancel');

    Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('offers.destroy');

    /*
     * Lager-Arbeitsansicht für Bestellungen ab "In Bearbeitung".
     * Keine Preise, Rechnungen, Angebots-PDFs oder Angebotsbearbeitung.
     */
    Route::prefix('lager/angebote')
        ->name('warehouse.offers.')
        ->middleware('role:' . User::ROLE_WAREHOUSE)
        ->group(function () {
            Route::get('/', [WarehouseOfferController::class, 'index'])->name('index');
            Route::get('/{offer}', [WarehouseOfferController::class, 'show'])->name('show');
            Route::put('/{offer}/status', [WarehouseOfferController::class, 'updateStatus'])->name('status');
            Route::get('/{offer}/lieferschein', [WarehouseOfferController::class, 'deliveryNote'])->name('delivery-note');
        });

    /* Verkauf / Kunden */
    Route::resource('suppliers', SupplierController::class)
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES);

    Route::post('customers/{customer}/wallet-transactions', [CustomerWalletTransactionController::class, 'store'])
        ->name('customers.wallet-transactions.store')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES);

    Route::resource('customers', CustomerController::class)
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES);

    Route::get('/prices', [PriceController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('prices.index');

    Route::put('/prices', [PriceController::class, 'update'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_SALES)
        ->name('prices.update');

    /* Manager-Bereiche */
    Route::get('/warnings', [WarningController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE)
        ->name('warnings.index');

    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->middleware('role:' . User::ROLE_MANAGER)
        ->name('statistics.index');

    /* Rechte & Sicherheit: ausschließlich Admin. */
    Route::get('/security', [SecurityController::class, 'index'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('security.index');

    Route::get('/security/users/create', [SystemUserController::class, 'create'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('security.users.create');

    Route::post('/security/users', [SystemUserController::class, 'store'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('security.users.store');

    Route::get('/security/users/{user}/edit', [SystemUserController::class, 'edit'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('security.users.edit');

    Route::put('/security/users/{user}', [SystemUserController::class, 'update'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('security.users.update');

    Route::delete('/security/users/{user}', [SystemUserController::class, 'destroy'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('security.users.destroy');

    /* PDF-Vorlagen bleiben Manager/Admin-Verantwortung. */
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

if (file_exists(__DIR__ . '/settings.php')) {
    require __DIR__ . '/settings.php';
}
