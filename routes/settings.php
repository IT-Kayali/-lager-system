<?php

use App\Http\Controllers\BranchWithdrawalController;
use App\Http\Controllers\BranchWithdrawalPdfController;
use App\Http\Controllers\OfferInternalNoteController;
use App\Http\Controllers\PriceTierDefinitionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::put('settings/price-tiers', [PriceTierDefinitionController::class, 'updateAll'])
        ->name('price-tiers.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/offer-numbering', [SettingsController::class, 'updateOfferNumber'])
        ->name('settings.offer-numbering.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/low-stock-warning', [SettingsController::class, 'updateLowStockWarning'])
        ->name('settings.low-stock-warning.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/batch-expiry', [SettingsController::class, 'updateBatchExpiry'])
        ->name('settings.batch-expiry.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::post('offer-notes/{offer}', [OfferInternalNoteController::class, 'store'])
        ->name('offer-notes.store');

    Route::get('branch-withdrawals/{branchWithdrawal}/delivery-note', [BranchWithdrawalPdfController::class, 'stream'])
        ->name('branch-withdrawals.delivery-note')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::get('branch-withdrawals/{branchWithdrawal}/edit', [BranchWithdrawalController::class, 'edit'])
        ->name('branch-withdrawals.edit')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::put('branch-withdrawals/{branchWithdrawal}', [BranchWithdrawalController::class, 'update'])
        ->name('branch-withdrawals.update')
        ->middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_WAREHOUSE);

    Route::delete('branch-withdrawals/{branchWithdrawal}', [BranchWithdrawalController::class, 'destroy'])
        ->name('branch-withdrawals.destroy')
        ->middleware('role:' . User::ROLE_MANAGER);

    Route::get('verkauf/produkte', [ProductController::class, 'index'])
        ->name('sales.products.index')
        ->middleware('role:' . User::ROLE_SALES);

    Route::get('verkauf/produkte/{product}', [ProductController::class, 'show'])
        ->name('sales.products.show')
        ->middleware('role:' . User::ROLE_SALES);

    Route::get('verkauf/filialausgaenge', [BranchWithdrawalController::class, 'index'])
        ->name('sales.branch-withdrawals.index')
        ->middleware('role:' . User::ROLE_SALES);

    Route::get('verkauf/filialausgaenge/erstellen', [BranchWithdrawalController::class, 'create'])
        ->name('sales.branch-withdrawals.create')
        ->middleware('role:' . User::ROLE_SALES);

    Route::post('verkauf/filialausgaenge', [BranchWithdrawalController::class, 'store'])
        ->name('sales.branch-withdrawals.store')
        ->middleware('role:' . User::ROLE_SALES);

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
    Route::livewire('settings/security', 'pages::settings.security')->name('security.edit');
});
