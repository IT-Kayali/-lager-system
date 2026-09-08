<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createStoredSessionFor(User $user, string $id): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Security test',
        'payload' => 'test',
        'last_activity' => now()->timestamp,
    ]);
}

beforeEach(function () {
    config([
        'session.driver' => 'database',
        'session.table' => 'sessions',
    ]);
});

test('changing a user role revokes existing sessions', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
        'remember_token' => 'old-token',
    ]);

    createStoredSessionFor($user, 'role-session');

    $response = $this->actingAs($admin)
        ->put(route('security.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => User::ROLE_MANAGER,
            'is_active' => true,
            'password' => '',
            'password_confirmation' => '',
        ]);

    $response->assertRedirect(route('security.index'));

    $this->assertDatabaseMissing('sessions', [
        'id' => 'role-session',
    ]);

    expect($user->refresh()->remember_token)
        ->not->toBe('old-token');
});

test('deactivating a user revokes existing sessions', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
        'remember_token' => 'old-token',
    ]);

    createStoredSessionFor($user, 'inactive-session');

    $response = $this->actingAs($admin)
        ->put(route('security.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => false,
            'password' => '',
            'password_confirmation' => '',
        ]);

    $response->assertRedirect(route('security.index'));

    expect($user->refresh()->is_active)->toBeFalse();

    $this->assertDatabaseMissing('sessions', [
        'id' => 'inactive-session',
    ]);

    expect($user->remember_token)
        ->not->toBe('old-token');
});

test('changing a user password revokes existing sessions', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
        'remember_token' => 'old-token',
    ]);

    createStoredSessionFor($user, 'password-session');

    $response = $this->actingAs($admin)
        ->put(route('security.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => true,
            'password' => 'Secure-Test!2026-Password',
            'password_confirmation' => 'Secure-Test!2026-Password',
        ]);

    $response->assertRedirect(route('security.index'));

    $this->assertDatabaseMissing('sessions', [
        'id' => 'password-session',
    ]);

    expect(
        Hash::check(
            'Secure-Test!2026-Password',
            $user->refresh()->password
        )
    )->toBeTrue();

    expect($user->remember_token)
        ->not->toBe('old-token');
});

test('user edit form exposes explicit account status and password reset controls', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('security.users.edit', $user));

    $response
        ->assertOk()
        ->assertSee('Benutzerkonto')
        ->assertSee('Kontostatus')
        ->assertSee('Aktiv')
        ->assertSee('Deaktiviert')
        ->assertSee('Neues Passwort vergeben')
        ->assertSee('Neues Passwort bestätigen')
        ->assertSee('Sitzungsschutz aktiv');
});

test('administrator cannot deactivate own account from the edit form', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('security.users.edit', $admin));

    $response
        ->assertOk()
        ->assertSee('Der eigene Benutzer kann nicht deaktiviert werden.');
});


test('personal account security link is visible in premium sidebar', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('security.index'));

    $response
        ->assertOk()
        ->assertSee('Mein Konto & Sicherheit')
        ->assertSee(route('security.edit'), false);
});
