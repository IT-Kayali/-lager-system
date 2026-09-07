<?php

test('system user management uses the central password policy', function () {
    $source = file_get_contents(app_path('Http/Controllers/SystemUserController.php'));

    expect($source)
        ->toContain('Password::default()')
        ->not->toContain("'min:8'");
});

test('sensitive controllers do not expose raw exception messages', function () {
    $controllers = [
        app_path('Http/Controllers/OfferStatusController.php'),
        app_path('Http/Controllers/WarehouseOfferController.php'),
        app_path('Http/Controllers/ProductExcelController.php'),
    ];

    foreach ($controllers as $controller) {
        expect(file_get_contents($controller))
            ->not->toContain('$exception->getMessage()');
    }
});

test('profile does not render the self deletion component', function () {
    $profile = file_get_contents(
        resource_path('views/pages/settings/⚡profile.blade.php')
    );

    expect($profile)
        ->not->toContain('delete-user-form');
});
