<?php

it('loads the unified app chrome globally through the sidebar', function () {
    $sidebar = file_get_contents(resource_path('views/partials/app-sidebar.blade.php'));

    expect($sidebar)
        ->toContain("@include('partials.application-theme')")
        ->toContain("@include('partials.unified-app-chrome')");
});

it('removes the global header quick actions visually on every premium page', function () {
    $chrome = file_get_contents(public_path('css/csp-static-bulk.css'));

    expect($chrome)
        ->toContain('.premium-topbar > div:last-child:not(:first-child)')
        ->toContain('display: none !important;');
});

it('keeps the page header inset with additional top and right spacing', function () {
    $chrome = file_get_contents(public_path('css/csp-static-bulk.css'));

    expect($chrome)
        ->toContain('padding: 34px 54px 52px !important;')
        ->toContain('margin: 0 0 30px !important;')
        ->toContain('border-radius: 20px !important;');
});

it('covers the main search and filter toolbars with one shared design', function () {
    $chrome = file_get_contents(public_path('css/csp-static-bulk.css'));

    foreach ([
        '.products-page-actions',
        '.category-page-actions',
        '.batches-page-actions',
        '.offers-page-actions',
        '.suppliers-toolbar-card',
        '.customers-search-inline',
        'section.premium-card > form.premium-toolbar',
        'section.premium-card > form.premium-search',
        '.stats-filter-card',
    ] as $selector) {
        expect($chrome)->toContain($selector);
    }

    expect($chrome)
        ->toContain('min-height: 48px !important;')
        ->toContain('grid-template-columns: minmax(300px, 1.35fr) minmax(240px, .9fr) auto !important;');
});

it('keeps page specific action buttons available outside the removed global header actions', function () {
    $productPage = file_get_contents(resource_path('views/pages/products/index.blade.php'));
    $offerPage = file_get_contents(resource_path('views/pages/offers/index.blade.php'));
    $categoryPage = file_get_contents(resource_path('views/pages/product-categories/index.blade.php'));

    expect($productPage)
        ->toContain('Produkt hinzufügen')
        ->toContain('Excel exportieren')
        ->toContain('Excel importieren');

    expect($offerPage)
        ->toContain('Neues Angebot')
        ->not->toContain('PDF-Vorlagen');

    expect($categoryPage)->toContain('Neue Kategorie');
});

it('contains responsive toolbar rules for smaller screens', function () {
    $chrome = file_get_contents(public_path('css/csp-static-bulk.css'));

    expect($chrome)
        ->toContain('@media (max-width: 1240px)')
        ->toContain('@media (max-width: 820px)')
        ->toContain('@media (max-width: 560px)');
});


it('keeps all ERP list filters in one desktop row and prices use the shared toolbar', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/premium.blade.php'));
    $chrome = file_get_contents(public_path('css/csp-static-bulk.css'));
    $prices = file_get_contents(resource_path('views/pages/prices/index.blade.php'));

    expect($layout)
        ->toContain("asset('css/csp-static-bulk.css')")
        ->not->toContain('<style');

    expect($chrome)
        ->toContain('flex-wrap:nowrap !important;')
        ->toContain('flex:0 1 170px !important;')
        ->toContain('.erp-list-filter-form,')
        ->toContain('section.premium-card > form.premium-toolbar.erp-list-filter-form')
        ->toContain('flex-wrap: nowrap !important;')
        ->toContain('@media (max-width: 820px)');

    expect($prices)
        ->toContain('class="premium-toolbar erp-list-filter-form"')
        ->not->toContain('<div class="premium-search">');
});