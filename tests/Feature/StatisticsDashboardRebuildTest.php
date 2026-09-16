<?php

test('statistics dashboard uses dedicated CSP safe assets', function () {
    $view = file_get_contents(
        resource_path('views/pages/statistics/index.blade.php')
    );

    $runtime = file_get_contents(
        public_path('js/statistics-runtime.js')
    );

    $styles = file_get_contents(
        public_path('css/statistics-dashboard.css')
    );

    expect($view)
        ->not->toContain('<style>')
        ->not->toContain('<script>')
        ->not->toContain('@style(')
        ->not->toContain(':hide-header="true"')
        ->not->toContain('statistics-data-warning')
        ->not->toContain(
            'Geldwerte sind für einen Teil der Verkäufe nicht gespeichert'
        )
        ->toContain('title="Statistik"')
        ->toContain(
            'Die wichtigsten Verkaufs-, Kunden-, Produkt- und Lagerkennzahlen auf einen Blick.'
        )
        ->toContain("asset('css/statistics-dashboard.css')")
        ->toContain("asset('js/statistics-runtime.js')")
        ->toContain('data-statistics-runtime')
        ->toContain('data-statistics-period')
        ->toContain('data-custom-dates')
        ->toContain('data-sales-trend-chart')
        ->toContain('data-chart-mode')
        ->toContain('Weitere Filter');

    expect($runtime)
        ->toContain('initStatisticsRuntime')
        ->toContain('syncCustomDates')
        ->toContain('customDates.hidden')
        ->toContain('parseTrend')
        ->toContain('trend.orders')
        ->toContain('trend.revenue')
        ->toContain('new ChartCtor');

    expect($styles)
        ->toContain('.statistics-dashboard')
        ->toContain('.statistics-kpi-grid')
        ->toContain('.statistics-chart')
        ->not->toContain('.statistics-data-warning');
});

test('statistics service reports missing revenue coverage', function () {
    $service = file_get_contents(
        app_path('Services/StatisticsService.php')
    );

    expect($service)
        ->toContain('revenueCoverage')
        ->toContain("'orders_with_amount'")
        ->toContain("'orders_without_amount'")
        ->toContain("'has_missing_amounts'")
        ->toContain("'all_amounts_missing'")
        ->toContain("'revenue_coverage' => \$revenueCoverage")
        ->toContain("->orderByDesc('sold_quantity')")
        ->toContain("->orderByDesc('orders_count')");
});
