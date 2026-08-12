<?php

use App\Http\Controllers\BranchWithdrawalController;
use App\Http\Controllers\BranchWithdrawalPdfController;
use App\Http\Controllers\PriceTierDefinitionController;
use App\Http\Controllers\SettingsController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::put('settings/price-tiers', [PriceTierDefinitionController::class, 'updateAll'])
        ->name('price-tiers.update')
        ->middleware('role:' . User::ROLE_ADMIN);

    Route::put('settings/low-stock-warning', [SettingsController::class, 'updateLowStockWarning'])
        ->name('settings.low-stock-warning.update')
        ->middleware('role:' . User::ROLE_ADMIN);

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

    /* Persönliche Konto-/Sicherheitsseiten bleiben für jeden angemeldeten Benutzer erreichbar. */
    Route::livewire('settings/profile', 'pages::settings.profile')
        ->name('profile.edit');

    Route::livewire('settings/appearance', 'pages::settings.appearance')
        ->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->name('security.edit');
});
