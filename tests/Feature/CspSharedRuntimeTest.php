<?php

test('shared browser runtime no longer depends on inline scripts', function () {
    $branding = file_get_contents(resource_path('views/partials/browser-branding-runtime.blade.php'));
    $statuses = file_get_contents(resource_path('views/partials/unified-status-colors.blade.php'));
    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($branding)
        ->not->toContain('<script>')
        ->toContain("asset('js/csp-shared-runtime.js')")
        ->toContain('data-document-title=')
        ->toContain('data-favicon-url=');

    expect($statuses)
        ->not->toContain('<script')
        ->toContain('.status-unified-open')
        ->toContain('.status-unified-expired');

    expect($runtime)
        ->toContain('initBrowserBranding')
        ->toContain('initUnifiedStatusColors')
        ->toContain('MutationObserver')
        ->toContain('data-configurable-site-favicon');
});

test('simple browser actions no longer use inline event handler attributes', function () {
    $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
    $prices = file_get_contents(resource_path('views/pages/prices/index.blade.php'));
    $customerRevenue = file_get_contents(resource_path('views/pages/customers/_revenue.blade.php'));
    $buttonAppearance = file_get_contents(resource_path('views/pages/settings/_button-appearance.blade.php'));
    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($dashboard)
        ->not->toContain('onclick=')
        ->toContain('data-dialog-open="activityModal"')
        ->toContain('data-dialog-close="activityModal"');

    expect($prices)
        ->not->toContain('onchange=')
        ->toContain('data-auto-submit');

    expect($customerRevenue)
        ->not->toContain('onchange=')
        ->toContain('data-auto-submit');

    expect($buttonAppearance)
        ->not->toContain('onclick=')
        ->toContain('data-confirm="Standardfarben der Buttons wiederherstellen?"');

    expect($runtime)
        ->toContain('initCspEventHandlers')
        ->toContain('[data-dialog-open]')
        ->toContain('[data-dialog-close]')
        ->toContain('[data-auto-submit]')
        ->toContain('[data-confirm]');
});

test('destructive form confirmations use CSP safe data attributes', function () {
    $views = [
        resource_path('views/pages/offers/show.blade.php'),
        resource_path('views/pages/security/index.blade.php'),
        resource_path('views/pages/customers/index.blade.php'),
        resource_path('views/pages/suppliers/index.blade.php'),
        resource_path('views/pages/batches/index.blade.php'),
        resource_path('views/pages/products/index.blade.php'),
        resource_path('views/pages/product-categories/index.blade.php'),
        resource_path('views/pages/branch-withdrawals/index.blade.php'),
        resource_path('views/pages/offers/index.blade.php'),
        resource_path('views/pages/settings/_customer-groups.blade.php'),
    ];

    foreach ($views as $view) {
        $content = file_get_contents($view);

        expect($content)
            ->not->toContain('onsubmit=')
            ->toContain('data-confirm=');
    }

    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($runtime)
        ->toContain("document.addEventListener('submit'")
        ->toContain("form[data-confirm]")
        ->toContain("confirmTrigger.matches('form')");
});

test('simple product and sales branch page scripts use the shared CSP runtime', function () {
    $productCreate = file_get_contents(resource_path('views/pages/products/create.blade.php'));
    $productEdit = file_get_contents(resource_path('views/pages/products/edit.blade.php'));
    $branchCreate = file_get_contents(resource_path('views/pages/branch-withdrawals/create.blade.php'));
    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($productCreate)
        ->not->toContain('<script')
        ->toContain('data-product-editor-runtime')
        ->toContain('data-product-create-runtime');

    expect($productEdit)
        ->not->toContain('<script')
        ->toContain('data-product-editor-runtime')
        ->not->toContain('data-product-create-runtime');

    expect($branchCreate)
        ->not->toContain('<script')
        ->toContain('data-sales-branch-create')
        ->toContain('data-open-status=')
        ->toContain('data-default-index-url=')
        ->toContain('data-sales-index-url=');

    expect($runtime)
        ->toContain('initProductEditorRuntime')
        ->toContain('data-product-editor-runtime')
        ->toContain('data-product-create-runtime')
        ->toContain('initSalesBranchCreateRuntime')
        ->toContain('data-sales-branch-create');
});

test('customer and batch form scripts use the shared CSP runtime', function () {
    $customerForm = file_get_contents(resource_path('views/pages/customers/_form.blade.php'));
    $batchForm = file_get_contents(resource_path('views/pages/batches/_form.blade.php'));
    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($customerForm)
        ->not->toContain('<script')
        ->toContain('data-customer-form-runtime')
        ->toContain('delivery-address-card')
        ->toContain('customer-group-preview');

    expect($batchForm)
        ->not->toContain('<script')
        ->toContain('data-batch-expiry-runtime')
        ->toContain('data-default-months=')
        ->toContain('data-auto-expiry=');

    expect($runtime)
        ->toContain('initCustomerFormRuntime')
        ->toContain('data-customer-form-runtime')
        ->toContain('initBatchExpiryRuntime')
        ->toContain('data-batch-expiry-runtime')
        ->toContain('addMonthsNoOverflow');
});

test('category product search uses the shared CSP runtime', function () {
    $categoryShow = file_get_contents(resource_path('views/pages/product-categories/show.blade.php'));
    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($categoryShow)
        ->not->toContain('<script')
        ->toContain('data-category-product-search-runtime')
        ->toContain('data-category-product-row')
        ->toContain('category-product-search-clear')
        ->toContain('category-product-search-empty');

    expect($runtime)
        ->toContain('initCategoryProductSearchRuntime')
        ->toContain('data-category-product-search-runtime')
        ->toContain('data-category-product-row')
        ->toContain('category-product-search-empty');
});

test('button appearance preview uses the shared CSP runtime', function () {
    $buttonAppearance = file_get_contents(resource_path('views/pages/settings/_button-appearance.blade.php'));
    $runtime = file_get_contents(public_path('js/csp-shared-runtime.js'));

    expect($buttonAppearance)
        ->not->toContain('<script')
        ->toContain('data-button-appearance-runtime')
        ->toContain('primary_button_background')
        ->toContain('secondary_button_background')
        ->toContain('data-confirm="Standardfarben der Buttons wiederherstellen?"');

    expect($runtime)
        ->toContain('initButtonAppearanceRuntime')
        ->toContain('data-button-appearance-runtime')
        ->toContain('--premium-primary-button-bg')
        ->toContain('--premium-secondary-button-bg');
});

test('price tier settings load their CSP safe runtime externally', function () {
    $priceTiers = file_get_contents(resource_path('views/pages/settings/_price-tiers.blade.php'));
    $runtime = file_get_contents(public_path('js/price-tiers-runtime.js'));

    expect($priceTiers)
        ->not->toContain('<script>')
        ->toContain("asset('js/price-tiers-runtime.js')")
        ->toContain('data-price-tiers-runtime')
        ->toContain('data-next-index=')
        ->toContain('data-price-tier-row')
        ->toContain('data-remove-price-tier');

    expect($runtime)
        ->toContain('initPriceTiersRuntime')
        ->toContain('[data-price-tiers-runtime]')
        ->toContain('[data-price-tier-row]')
        ->toContain('[data-remove-price-tier]')
        ->toContain('Mindestens eine Preisstufe muss bestehen bleiben.')
        ->toContain('Preisstufe wirklich entfernen?');
});
