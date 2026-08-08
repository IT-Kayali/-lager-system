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
