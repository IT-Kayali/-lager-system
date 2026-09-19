<?php

test('productive browser views contain no inline style blocks after bulk CSP extraction', function () {
    $root = resource_path('views');
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    $violations = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $path = str_replace('\\', '/', $file->getPathname());
        $relative = str_replace(str_replace('\\', '/', resource_path()) . '/', '', $path);

        if (str_contains($relative, 'views/pdf/') || $relative === 'views/welcome.blade.php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if (preg_match('/<style\\b/i', $content) === 1) {
            $violations[] = $relative;
        }
    }

    expect($violations)->toBe([]);
});

test('bulk and dynamic stylesheets are loaded from external same origin resources', function () {
    $premium = file_get_contents(resource_path('views/components/layouts/premium.blade.php'));
    $head = file_get_contents(resource_path('views/partials/head.blade.php'));
    $login = file_get_contents(resource_path('views/pages/auth/login.blade.php'));
    $applicationTheme = file_get_contents(resource_path('views/partials/application-theme.blade.php'));
    $chrome = file_get_contents(resource_path('views/partials/unified-app-chrome.blade.php'));
    $bulk = file_get_contents(public_path('css/csp-static-bulk.css'));
    $loginStyles = file_get_contents(public_path('css/login-page.css'));

    foreach ([$premium, $head] as $layout) {
        expect($layout)
            ->toContain("asset('css/csp-static-bulk.css')")
            ->toContain("route('application.theme.css')");
    }

    expect($login)
        ->not->toContain('<style')
        ->toContain("asset('css/login-page.css')")
        ->toContain("route('login.theme.css')");

    expect($applicationTheme)->not->toContain('<style');
    expect($chrome)->not->toContain('<style');

    expect($bulk)
        ->toContain('Source: resources/views/components/layouts/premium.blade.php')
        ->toContain('Source: resources/views/partials/unified-app-chrome.blade.php')
        ->toContain('.premium-btn')
        ->toContain('.offer-status-pill')
        ->toContain('.product-editor-card');

    expect($loginStyles)
        ->toContain('.login-page')
        ->toContain('.login-card')
        ->not->toContain('@if')
        ->not->toContain('{{');
});
