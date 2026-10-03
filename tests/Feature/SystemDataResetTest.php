<?php

use App\Models\User;
use App\Services\SystemDataResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows only admins to reset system business data', function () {
    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($manager)
        ->post(route('settings.system-data-reset'), [
            'current_password' => 'password',
            'confirmation' => 'SYSTEM RESETTEN',
            'acknowledge' => '1',
        ]);

    $response->assertForbidden();
});

it('does not reset data without the exact confirmation phrase', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    DB::table('suppliers')->insert([
        'supplier_number' => 'LFR-TEST',
        'company_name' => 'Testlieferant',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('settings.system-data-reset'), [
            'current_password' => 'password',
            'confirmation' => 'RESET',
            'acknowledge' => '1',
        ]);

    $response->assertSessionHasErrors('confirmation');

    $this->assertDatabaseHas('suppliers', [
        'supplier_number' => 'LFR-TEST',
    ]);
});

it('resets business data but preserves system configuration and users', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
    ]);

    DB::table('suppliers')->insert([
        'supplier_number' => 'LFR-TEST',
        'company_name' => 'Testlieferant',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(SystemDataResetService::RESET_TABLES)
        ->toContain(
            'offers',
            'customers',
            'products',
            'product_batches',
            'stock_movements',
            'suppliers',
            'activity_logs',
        );

    expect(SystemDataResetService::PRESERVED_TABLES)
        ->toContain(
            'users',
            'application_settings',
            'customer_groups',
            'price_tier_definitions',
            'document_templates',
        );

    $response = $this
        ->actingAs($admin)
        ->post(route('settings.system-data-reset'), [
            'current_password' => 'password',
            'confirmation' => 'SYSTEM RESETTEN',
            'acknowledge' => '1',
        ]);

    $response
        ->assertRedirect(
            route('settings.index') . '#system-data-reset'
        )
        ->assertSessionHas('success');

    $this->assertDatabaseCount('suppliers', 0);

    $this->assertDatabaseCount('users', 2);

    $this->assertDatabaseHas('application_settings', [
        'key' => 'reservation_hours',
    ]);

    $this->assertDatabaseCount('activity_logs', 1);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $admin->id,
        'action' => 'system.data_reset',
    ]);
});
