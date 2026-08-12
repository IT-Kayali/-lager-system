<?php

use App\Models\ApplicationSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->warehouse = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
    ]);

    $this->product = Product::query()->create([
        'name' => 'Ablaufdatum Testprodukt',
        'unit' => 'piece',
        'minimum_stock' => 0,
    ]);
});

it('uses 24 months as the default batch expiry setting', function () {
    expect(ApplicationSetting::defaultBatchExpiryMonths())->toBe(24);
});

it('lets admin change the default batch expiry months', function () {
    $this->actingAs($this->admin)
        ->put(route('settings.batch-expiry.update'), [
            'default_batch_expiry_months' => 36,
        ])
        ->assertRedirect(route('settings.index') . '#batch-expiry');

    expect(ApplicationSetting::defaultBatchExpiryMonths())->toBe(36);
});

it('automatically stores the configured expiry date when a new batch has no manual date', function () {
    ApplicationSetting::putValue('default_batch_expiry_months', 24, 'integer');

    $this->actingAs($this->warehouse)
        ->post(route('batches.store'), [
            'product_id' => $this->product->id,
            'batch_number' => 'AUTO-EXP-001',
            'quantity' => 100,
            'received_at' => '2026-08-12',
            'expires_at' => '',
        ])
        ->assertRedirect(route('batches.index'));

    $batch = ProductBatch::query()->where('batch_number', 'AUTO-EXP-001')->firstOrFail();

    expect($batch->expires_at->format('Y-m-d'))->toBe('2028-08-12');
});

it('keeps a manually entered expiry date unchanged', function () {
    ApplicationSetting::putValue('default_batch_expiry_months', 24, 'integer');

    $this->actingAs($this->warehouse)
        ->post(route('batches.store'), [
            'product_id' => $this->product->id,
            'batch_number' => 'MANUAL-EXP-001',
            'quantity' => 100,
            'received_at' => '2026-08-12',
            'expires_at' => '2027-02-01',
        ])
        ->assertRedirect(route('batches.index'));

    $batch = ProductBatch::query()->where('batch_number', 'MANUAL-EXP-001')->firstOrFail();

    expect($batch->expires_at->format('Y-m-d'))->toBe('2027-02-01');
});

it('allows fifo stock withdrawal from an expired batch', function () {
    $batch = ProductBatch::query()->create([
        'product_id' => $this->product->id,
        'batch_number' => 'EXPIRED-BUT-USABLE',
        'quantity' => 10,
        'received_at' => '2024-01-01',
        'expires_at' => '2025-01-01',
    ]);

    $this->actingAs($this->warehouse)
        ->post(route('batches.fifo-out.store'), [
            'product_id' => $this->product->id,
            'quantity' => 4,
            'note' => 'Abgelaufene Charge bleibt verkaufbar',
        ])
        ->assertRedirect(route('batches.index'));

    expect((float) $batch->fresh()->quantity)->toBe(6.0);
});
