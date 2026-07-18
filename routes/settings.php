<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings/profile', '/settings')
        ->name('profile.edit');

    Route::redirect('settings/appearance', '/settings')
        ->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
    ->name('security.edit');
});
