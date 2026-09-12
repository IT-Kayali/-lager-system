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
