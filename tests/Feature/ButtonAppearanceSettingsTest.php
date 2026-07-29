<?php

use App\Models\ApplicationSetting;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
});

it('allows a manager to update the global button colors', function () {
    $this->actingAs($this->manager)
        ->put(route('settings.button-appearance.update'), [
            'primary_button_background' => '#123456',
            'primary_button_text' => '#FEDCBA',
            'secondary_button_background' => '#334455',
            'secondary_button_text' => '#FFFFFF',
        ])
        ->assertRedirect(route('settings.index') . '#button-appearance');

    $this->assertDatabaseHas('application_settings', [
        'key' => 'primary_button_background',
        'value' => '#123456',
        'type' => 'color',
    ]);

    $this->assertDatabaseHas('application_settings', [
        'key' => 'primary_button_text',
        'value' => '#FEDCBA',
        'type' => 'color',
    ]);

    $this->assertDatabaseHas('application_settings', [
        'key' => 'secondary_button_background',
        'value' => '#334455',
        'type' => 'color',
    ]);

    $this->assertDatabaseHas('application_settings', [
        'key' => 'secondary_button_text',
        'value' => '#FFFFFF',
        'type' => 'color',
    ]);
});

it('rejects invalid button colors', function () {
    $this->actingAs($this->manager)
        ->from(route('settings.index'))
        ->put(route('settings.button-appearance.update'), [
            'primary_button_background' => 'gold',
            'primary_button_text' => '#171716',
            'secondary_button_background' => '#111111',
            'secondary_button_text' => '#FFFFFF',
        ])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHasErrors('primary_button_background');

    $this->assertDatabaseMissing('application_settings', [
        'key' => 'primary_button_background',
        'value' => 'gold',
    ]);
});

it('restores the default button colors', function () {
    foreach ([
        'primary_button_background' => '#000001',
        'primary_button_text' => '#000002',
        'secondary_button_background' => '#000003',
        'secondary_button_text' => '#000004',
    ] as $key => $value) {
        ApplicationSetting::putValue($key, $value, 'color');
    }

    $this->actingAs($this->manager)
        ->put(route('settings.button-appearance.update'), [
            'primary_button_background' => '#000001',
            'primary_button_text' => '#000002',
            'secondary_button_background' => '#000003',
            'secondary_button_text' => '#000004',
            'reset_button_appearance' => '1',
        ])
        ->assertRedirect(route('settings.index') . '#button-appearance');

    foreach (ApplicationSetting::buttonThemeDefaults() as $key => $value) {
        $this->assertDatabaseHas('application_settings', [
            'key' => $key,
            'value' => $value,
        ]);
    }
});

it('uses safe defaults when stored button colors are invalid', function () {
    ApplicationSetting::putValue('primary_button_background', 'invalid', 'color');

    expect(ApplicationSetting::buttonTheme())
        ->toMatchArray(ApplicationSetting::buttonThemeDefaults());
});

it('blocks non managers from changing button colors', function () {
    $wholesale = User::factory()->create([
        'role' => User::ROLE_WHOLESALE,
        'is_active' => true,
    ]);

    $this->actingAs($wholesale)
        ->put(route('settings.button-appearance.update'), [
            'primary_button_background' => '#123456',
            'primary_button_text' => '#FFFFFF',
            'secondary_button_background' => '#111111',
            'secondary_button_text' => '#FFFFFF',
        ])
        ->assertForbidden();
});
