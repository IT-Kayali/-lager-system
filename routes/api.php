<?php

use App\Http\Controllers\CspReportController;
use Illuminate\Support\Facades\Route;

Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:60,1')
    ->name('csp.report');
