<?php

test('statistics page loads its runtime externally', function () {
    $view = file_get_contents(
        resource_path('views/pages/statistics/index.blade.php')
    );

    $runtime = file_get_contents(
        public_path('js/statistics-runtime.js')
    );

    expect($view)
        ->not->toContain('<script>')
        ->not->toContain('const chartData=@json')
        ->toContain("asset('js/statistics-runtime.js')")
        ->toContain('data-statistics-runtime')
        ->toContain('data-sales-trend-chart')
        ->toContain('data-sales-trend=');

    expect($runtime)
        ->toContain('initStatisticsRuntime')
        ->toContain('parseSalesTrend')
        ->toContain('[data-statistics-runtime]')
        ->toContain('[data-sales-trend-chart]')
        ->toContain('JSON.parse')
        ->toContain('new window.Chart');
});
