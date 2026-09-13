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
