<?php

test('premium layout and warehouse sidebar use external runtimes', function () {
    $premium = file_get_contents(
        resource_path('views/components/layouts/premium.blade.php')
    );

    $sidebar = file_get_contents(
        resource_path('views/partials/app-sidebar.blade.php')
    );

    $premiumRuntime = file_get_contents(
        public_path('js/premium-layout-runtime.js')
    );

    $warehouseRuntime = file_get_contents(
        public_path('js/warehouse-notifications-runtime.js')
    );

    foreach ([
        'premium' => $premium,
        'sidebar' => $sidebar,
    ] as $name => $view) {
        preg_match_all(
            '/<script\b(?![^>]*\bsrc\s*=)[^>]*>/i',
            $view,
            $matches
        );

        expect(
            $matches[0],
            "{$name} still contains inline scripts"
        )->toBe([]);
    }

    expect($premium)
        ->toContain(
            "asset('js/premium-layout-runtime.js')"
        )
        ->toContain(
            'id="premium-layout-runtime-config"'
        )
        ->toContain(
            'data-server-toasts='
        )
        ->not->toContain(
            '@json($premiumToastMessages'
        );

    expect($sidebar)
        ->toContain(
            "asset('js/warehouse-notifications-runtime.js')"
        )
        ->toContain(
            'data-notifications-endpoint='
        )
        ->not->toContain(
            "@json(route('warehouse.notifications.index'))"
        );

    expect($premiumRuntime)
        ->not->toContain('@json(')
        ->not->toContain('{{')
        ->toContain('parsePremiumServerToasts')
        ->toContain('premiumToast')
        ->toContain('showInvalidToast');

    expect($warehouseRuntime)
        ->not->toContain('@json(')
        ->not->toContain('{{')
        ->toContain('notificationsEndpoint')
        ->toContain('refreshNotifications')
        ->toContain('renderNotifications');
});
