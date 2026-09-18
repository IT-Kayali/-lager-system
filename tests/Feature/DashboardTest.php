<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('managers can visit the dashboard', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard stylesheet is rendered in the document head', function () {
    $view = file_get_contents(resource_path('views/dashboard.blade.php'));
    $layout = file_get_contents(resource_path('views/components/layouts/premium.blade.php'));
    $styles = file_get_contents(public_path('css/dashboard.css'));

    expect($view)
        ->not->toContain('<style>')
        ->not->toContain('style=')
        ->toContain('<x-slot name="head">')
        ->toContain("asset('css/dashboard.css')");

    expect($layout)
        ->toContain("{{ \$head ?? '' }}");

    expect($styles)
        ->toContain('.dashboard-kpi-grid')
        ->toContain('.dashboard-modal')
        ->toContain('@media(max-width:760px)');

    $user = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();

    $html = $response->getContent();
    $stylesheetPosition = strpos($html, asset('css/dashboard.css'));
    $headEndPosition = strpos($html, '</head>');

    expect($stylesheetPosition !== false)->toBeTrue();
    expect($headEndPosition !== false)->toBeTrue();
    expect($stylesheetPosition)->toBeLessThan($headEndPosition);
});
