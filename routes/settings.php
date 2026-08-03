<?php

use App\Http\Controllers\BranchWithdrawalController;
use App\Http\Controllers\BranchWithdrawalPdfController;
use App\Http\Controllers\PriceTierDefinitionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::put('settings/price-tiers', [PriceTierDefinitionController::class, 'updateAll'])
        ->name('price-tiers.update')
        ->middleware('role:manager');

    Route::get('branch-withdrawals/{branchWithdrawal}/delivery-note', [BranchWithdrawalPdfController::class, 'stream'])
        ->name('branch-withdrawals.delivery-note');

    Route::get('branch-withdrawals/{branchWithdrawal}/edit', [BranchWithdrawalController::class, 'edit'])
        ->name('branch-withdrawals.edit');

    Route::put('branch-withdrawals/{branchWithdrawal}', [BranchWithdrawalController::class, 'update'])
        ->name('branch-withdrawals.update');

    Route::delete('branch-withdrawals/{branchWithdrawal}', [BranchWithdrawalController::class, 'destroy'])
        ->name('branch-withdrawals.destroy');

    Route::livewire('settings/profile', 'pages::settings.profile')
        ->name('profile.edit');

    Route::livewire('settings/appearance', 'pages::settings.appearance')
        ->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->name('security.edit');
});
