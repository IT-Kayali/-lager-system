<?php

use Illuminate\Support\Facades\File;

test('productive browser views contain no inline style blocks', function () {
    $views = collect(File::allFiles(resource_path('views')))
        ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
        ->reject(function ($file) {
            $path = str_replace('\\', '/', $file->getPathname());

            return str_contains($path, '/resources/views/pdf/')
                || str_ends_with($path, '/resources/views/welcome.blade.php')
                || str_ends_with($path, '/resources/views/styles/login.blade.php');
        });

    $violations = [];

    foreach ($views as $view) {
        $content = File::get($view->getPathname());

        if (preg_match('/<style\\b/i', $content) === 1) {
            $violations[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $view->getPathname());
        }
    }

    expect($violations)->toBe([]);
});

test('premium layout loads CSP safe external style bundles from the head', function () {
    $layout = File::get(resource_path('views/components/layouts/premium.blade.php'));

    expect($layout)
        ->not->toContain('<style')
        ->toContain("asset('css/csp/premium-layout.css')")
        ->toContain("route('application.theme.css')")
        ->toContain("asset('css/csp/application-theme.css')")
        ->toContain("asset('css/csp/unified-app-chrome.css')")
        ->toContain("asset('css/csp/app-sidebar.css')")
        ->toContain("asset('css/csp/offers.css')")
        ->toContain("asset('css/csp/products.css')")
        ->toContain("asset('css/csp/suppliers.css')")
        ->toContain("asset('css/csp/batches.css')")
        ->toContain("asset('css/csp/branch-withdrawals.css')")
        ->toContain("asset('css/csp/warehouse-offers.css')")
        ->toContain("asset('css/csp/product-categories.css')")
        ->toContain("asset('css/csp/settings-admin.css')")
        ->toContain("asset('css/csp/security-users.css')")
        ->toContain("asset('css/csp/account-security.css')")
        ->toContain("asset('css/csp/document-templates.css')")
        ->toContain("asset('css/csp/customers.css')")
        ->toContain("asset('css/csp/warnings.css')")
        ->toContain("asset('css/csp/prices.css')");
});

test('dynamic theme styles are delivered by same-origin stylesheet endpoints', function () {
    $this->get(route('application.theme.css'))
        ->assertOk()
        ->assertHeader('content-type', 'text/css; charset=UTF-8')
        ->assertSee('--premium-primary-button-bg', false)
        ->assertSee('--premium-secondary-button-text', false);

    $this->get(route('login.styles'))
        ->assertOk()
        ->assertHeader('content-type', 'text/css; charset=UTF-8')
        ->assertSee('.login-page', false)
        ->assertSee('.login-card', false);
});

test('login page loads its styles externally', function () {
    $view = File::get(resource_path('views/pages/auth/login.blade.php'));

    expect($view)
        ->not->toContain('<style')
        ->toContain("route('login.styles')");
});
