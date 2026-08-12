<?php

use App\Models\ApplicationSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;

it('allows an admin to configure the low-stock warning percentage', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('settings.low-stock-warning.update'), [
            'low_stock_warning_percentage' => 100,
        ])
        ->assertRedirect(route('settings.index') . '#low-stock-warning');

    $this->assertDatabaseHas('application_settings', [
        'key' => 'low_stock_warning_percentage',
        'value' => '100',
        'type' => 'decimal',
    ]);

    expect(ApplicationSetting::lowStockWarningPercentage())->toBe(100.0);
});

it('uses the configured percentage above the minimum stock for low warnings', function () {
    ApplicationSetting::putValue('low_stock_warning_percentage', 100, 'decimal');

    $product = Product::query()->create([
        'name' => 'Warnschwellen-Testprodukt',
        'unit' => 'Stück',
        'minimum_stock' => 500,
    ]);

    $batch = ProductBatch::query()->create([
        'product_id' => $product->id,
        'quantity' => 1000,
        'received_at' => now()->toDateString(),
    ]);

    $product->refresh();

    expect($product->low_stock_warning_threshold)->toBe(1000.0)
        ->and($product->stock_status)->toBe('low');

    $batch->update(['quantity' => 1001]);
    $product->refresh();

    expect($product->stock_status)->toBe('ok');

    $batch->update(['quantity' => 500]);
    $product->refresh();

    expect($product->stock_status)->toBe('critical');
});

it('keeps the previous ten percent behavior when no custom value is stored', function () {
    expect(ApplicationSetting::lowStockWarningPercentage())->toBe(10.0);
});

it('rejects invalid percentages', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->from(route('settings.index'))
        ->put(route('settings.low-stock-warning.update'), [
            'low_stock_warning_percentage' => -1,
        ])
        ->assertRedirect(route('settings.index'))
        ->assertSessionHasErrors('low_stock_warning_percentage');
});
