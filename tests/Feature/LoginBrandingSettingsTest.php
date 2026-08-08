<?php

use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores the login texts and site name from settings', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('settings.login-appearance.update'), [
            'site_name' => 'Alowidat Lager',
            'login_eyebrow' => 'Sicher anmelden',
            'login_title' => 'Willkommen im Lager',
            'login_subtitle' => 'Alle Bestände und Bestellungen sicher im Blick.',
        ])
        ->assertRedirect(route('settings.index') . '#login-appearance');

    expect(ApplicationSetting::siteName())->toBe('Alowidat Lager')
        ->and(ApplicationSetting::loginEyebrow())->toBe('Sicher anmelden')
        ->and(ApplicationSetting::loginTitle())->toBe('Willkommen im Lager')
        ->and(ApplicationSetting::loginSubtitle())->toBe('Alle Bestände und Bestellungen sicher im Blick.');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('<title>Login · Alowidat Lager</title>', false)
        ->assertSee('Sicher anmelden')
        ->assertSee('Willkommen im Lager')
        ->assertSee('Alle Bestände und Bestellungen sicher im Blick.')
        ->assertSee('color: #ffffff !important;', false);
});

it('uploads logo background and favicon and serves the favicon publicly', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('settings.login-appearance.update'), [
            'site_name' => 'Alowidat Lager',
            'login_eyebrow' => 'Sicherer Zugriff',
            'login_title' => 'Alles im Blick',
            'login_subtitle' => 'Willkommen im Lager-System.',
            'login_background' => UploadedFile::fake()->image('background.jpg', 1600, 900),
            'login_logo' => UploadedFile::fake()->image('logo.png', 500, 180),
            'site_favicon' => UploadedFile::fake()->image('favicon.png', 128, 128),
        ])
        ->assertRedirect(route('settings.index') . '#login-appearance');

    $backgroundPath = ApplicationSetting::loginBackgroundPath();
    $logoPath = ApplicationSetting::loginLogoPath();
    $faviconPath = ApplicationSetting::siteFaviconPath();

    expect($backgroundPath)->not->toBeNull()
        ->and($logoPath)->not->toBeNull()
        ->and($faviconPath)->not->toBeNull();

    Storage::disk('public')->assertExists($backgroundPath);
    Storage::disk('public')->assertExists($logoPath);
    Storage::disk('public')->assertExists($faviconPath);

    $this->get(route('site.favicon'))
        ->assertOk();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('site.favicon', [], false), false)
        ->assertSee(route('login.logo', [], false), false)
        ->assertSee(route('login.background', [], false), false);
});

it('removes uploaded login branding files without losing the texts', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('settings.login-appearance.update'), [
            'site_name' => 'Alowidat Lager',
            'login_eyebrow' => 'Sicherer Zugriff',
            'login_title' => 'Alles im Blick',
            'login_subtitle' => 'Willkommen im Lager-System.',
            'login_background' => UploadedFile::fake()->image('background.jpg', 1600, 900),
            'login_logo' => UploadedFile::fake()->image('logo.png', 500, 180),
            'site_favicon' => UploadedFile::fake()->image('favicon.png', 128, 128),
        ]);

    $backgroundPath = ApplicationSetting::loginBackgroundPath();
    $logoPath = ApplicationSetting::loginLogoPath();
    $faviconPath = ApplicationSetting::siteFaviconPath();

    $this->actingAs($admin)
        ->put(route('settings.login-appearance.update'), [
            'site_name' => 'Alowidat Neu',
            'login_eyebrow' => 'Neue kleine Überschrift',
            'login_title' => 'Neue Hauptüberschrift',
            'login_subtitle' => 'Neuer Beschreibungstext.',
            'remove_login_background' => '1',
            'remove_login_logo' => '1',
            'remove_site_favicon' => '1',
        ])
        ->assertRedirect(route('settings.index') . '#login-appearance');

    Storage::disk('public')->assertMissing($backgroundPath);
    Storage::disk('public')->assertMissing($logoPath);
    Storage::disk('public')->assertMissing($faviconPath);

    expect(ApplicationSetting::loginBackgroundPath())->toBeNull()
        ->and(ApplicationSetting::loginLogoPath())->toBeNull()
        ->and(ApplicationSetting::siteFaviconPath())->toBeNull()
        ->and(ApplicationSetting::siteName())->toBe('Alowidat Neu')
        ->and(ApplicationSetting::loginTitle())->toBe('Neue Hauptüberschrift');
});
