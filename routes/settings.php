<?php

use App\Http\Controllers\PriceTierDefinitionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::put('settings/price-tiers', [PriceTierDefinitionController::class, 'updateAll'])
        ->name('price-tiers.update')
        ->middleware('role:manager');

    Route::livewire('settings/profile', 'pages::settings.profile')
        ->name('profile.edit');

    Route::livewire('settings/appearance', 'pages::settings.appearance')
        ->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->name('security.edit');
});
