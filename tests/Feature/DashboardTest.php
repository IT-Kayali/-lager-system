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

test('dashboard styles are loaded from an external CSP safe stylesheet', function () {
    $view = file_get_contents(resource_path('views/dashboard.blade.php'));
    $styles = file_get_contents(public_path('css/dashboard.css'));

    expect($view)
        ->not->toContain('<style>')
        ->not->toContain('style=')
        ->toContain("asset('css/dashboard.css')");

    expect($styles)
        ->toContain('.dashboard-kpi-grid')
        ->toContain('.dashboard-modal')
        ->toContain('@media(max-width:760px)');
});
