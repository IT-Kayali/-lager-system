<?php

use App\Models\BranchWithdrawal;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $this->warehouse = User::factory()->create([
        'role' => User::ROLE_WAREHOUSE,
        'is_active' => true,
    ]);

    $this->sales = User::factory()->create([
        'role' => User::ROLE_SALES,
        'is_active' => true,
    ]);
});

function branchProduct(string $name, float $quantity): array
{
    $product = Product::query()->create([
        'name' => $name,
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    $batch = ProductBatch::query()->create([
        'product_id' => $product->id,
        'quantity' => $quantity,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    return [$product, $batch];
}

it('creates an open branch withdrawal with multiple products and an optional note', function () {
    [$firstProduct, $firstBatch] = branchProduct('Filialprodukt A', 10);
    [$secondProduct, $secondBatch] = branchProduct('Filialprodukt B', 20);

    $this->actingAs($this->manager)
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_OPEN,
            'note' => '',
            'items' => [
                ['product_id' => $firstProduct->id, 'quantity' => 3],
                ['product_id' => $secondProduct->id, 'quantity' => 5],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    $withdrawal = BranchWithdrawal::query()->with('items')->firstOrFail();

    expect($withdrawal->status)->toBe(BranchWithdrawal::STATUS_OPEN)
        ->and($withdrawal->note)->toBe('')
        ->and($withdrawal->items)->toHaveCount(2)
        ->and((float) $firstBatch->fresh()->quantity)->toBe(10.0)
        ->and((float) $secondBatch->fresh()->quantity)->toBe(20.0);
});

it('allows warehouse to create a branch withdrawal in progress', function () {
    [$product, $batch] = branchProduct('Lager Filialprodukt', 12);

    $this->actingAs($this->warehouse)
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_IN_PROGRESS,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    expect($withdrawal->status)->toBe(BranchWithdrawal::STATUS_IN_PROGRESS)
        ->and($withdrawal->user_id)->toBe($this->warehouse->id)
        ->and((float) $batch->fresh()->quantity)->toBe(12.0);

    $this->actingAs($this->warehouse)
        ->get(route('branch-withdrawals.index'))
        ->assertOk()
        ->assertSee($withdrawal->withdrawal_number);
});

it('prevents warehouse from creating an open branch withdrawal', function () {
    [$product] = branchProduct('Lager Statusschutz Produkt', 12);

    $this->actingAs($this->warehouse)
        ->from(route('branch-withdrawals.create'))
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_OPEN,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.create'))
        ->assertSessionHas('error');

    expect(BranchWithdrawal::query()->count())->toBe(0);
});

it('deducts all products by fifo when warehouse changes the status to issued', function () {
    [$product, $batch] = branchProduct('FIFO Filialprodukt', 10);

    $this->actingAs($this->manager)
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_DEZ,
            'status' => BranchWithdrawal::STATUS_IN_PROGRESS,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->warehouse)
        ->put(route('branch-withdrawals.update', $withdrawal), [
            'status' => BranchWithdrawal::STATUS_ISSUED,
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    $withdrawal->refresh();

    expect((float) $batch->fresh()->quantity)->toBe(6.0)
        ->and($withdrawal->processed_by)->toBe($this->warehouse->id)
        ->and($withdrawal->processed_at)->not->toBeNull();

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'reference_type' => BranchWithdrawal::class,
        'reference_id' => $withdrawal->id,
        'type' => 'out',
    ]);
});

it('automatically adjusts stock when an issued quantity is edited', function () {
    [$product, $batch] = branchProduct('Bearbeitbares Filialprodukt', 10);

    $this->actingAs($this->manager)->post(route('branch-withdrawals.store'), [
        'branch_name' => BranchWithdrawal::BRANCH_MAIN,
        'status' => BranchWithdrawal::STATUS_ISSUED,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 4],
        ],
    ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->manager)
        ->put(route('branch-withdrawals.update', $withdrawal), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_ISSUED,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 7],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    expect((float) $batch->fresh()->quantity)->toBe(3.0)
        ->and((float) $withdrawal->fresh()->items()->first()->quantity)->toBe(7.0);
});

it('returns issued stock when the status is cancelled', function () {
    [$product, $batch] = branchProduct('Stornierbares Filialprodukt', 10);

    $this->actingAs($this->manager)->post(route('branch-withdrawals.store'), [
        'branch_name' => BranchWithdrawal::BRANCH_MAIN,
        'status' => BranchWithdrawal::STATUS_ISSUED,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 4],
        ],
    ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->warehouse)
        ->put(route('branch-withdrawals.update', $withdrawal), [
            'status' => BranchWithdrawal::STATUS_CANCELLED,
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    expect((float) $batch->fresh()->quantity)->toBe(10.0)
        ->and($withdrawal->fresh()->status)->toBe(BranchWithdrawal::STATUS_CANCELLED);
});

it('returns issued stock before a manager deletes the withdrawal', function () {
    [$product, $batch] = branchProduct('Löschbares Filialprodukt', 10);

    $this->actingAs($this->manager)->post(route('branch-withdrawals.store'), [
        'branch_name' => BranchWithdrawal::BRANCH_MAIN,
        'status' => BranchWithdrawal::STATUS_ISSUED,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 4],
        ],
    ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->manager)
        ->delete(route('branch-withdrawals.destroy', $withdrawal))
        ->assertRedirect(route('branch-withdrawals.index'));

    expect((float) $batch->fresh()->quantity)->toBe(10.0);
    $this->assertDatabaseMissing('branch_withdrawals', ['id' => $withdrawal->id]);
});

it('does not allow a warehouse employee to delete a branch withdrawal', function () {
    [$product] = branchProduct('Geschütztes Filialprodukt', 10);

    $this->actingAs($this->manager)->post(route('branch-withdrawals.store'), [
        'branch_name' => BranchWithdrawal::BRANCH_MAIN,
        'status' => BranchWithdrawal::STATUS_OPEN,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->warehouse)
        ->delete(route('branch-withdrawals.destroy', $withdrawal))
        ->assertForbidden();
});

it('keeps every product unchanged when one issued position has insufficient stock', function () {
    [$firstProduct, $firstBatch] = branchProduct('Rollback Produkt A', 10);
    [$secondProduct, $secondBatch] = branchProduct('Rollback Produkt B', 2);

    $response = $this->actingAs($this->manager)
        ->from(route('branch-withdrawals.create'))
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_ISSUED,
            'items' => [
                ['product_id' => $firstProduct->id, 'quantity' => 4],
                ['product_id' => $secondProduct->id, 'quantity' => 3],
            ],
        ]);

    $response->assertRedirect(route('branch-withdrawals.create'));

    expect((float) $firstBatch->fresh()->quantity)->toBe(10.0)
        ->and((float) $secondBatch->fresh()->quantity)->toBe(2.0)
        ->and(BranchWithdrawal::query()->count())->toBe(0);
});


it('hides open branch withdrawals from warehouse until sales hands them off', function () {
    [$product] = branchProduct('Übergabe Filialprodukt', 20);

    $this->actingAs($this->sales)
        ->post(route('sales.branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_OPEN,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ])
        ->assertRedirect(route('sales.branch-withdrawals.index'));

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->warehouse)
        ->get(route('branch-withdrawals.index'))
        ->assertOk()
        ->assertDontSee($withdrawal->withdrawal_number);

    $this->actingAs($this->sales)
        ->put(route('sales.branch-withdrawals.update', $withdrawal), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_IN_PROGRESS,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ])
        ->assertRedirect(route('sales.branch-withdrawals.index'));

    expect($withdrawal->fresh()->status)->toBe(BranchWithdrawal::STATUS_IN_PROGRESS);

    $this->actingAs($this->warehouse)
        ->get(route('branch-withdrawals.index'))
        ->assertOk()
        ->assertSee($withdrawal->withdrawal_number);
});

it('prevents sales from editing a branch withdrawal after handoff to warehouse', function () {
    [$product] = branchProduct('Gesperrtes Übergabeprodukt', 20);

    $this->actingAs($this->sales)
        ->post(route('sales.branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_DEZ,
            'status' => BranchWithdrawal::STATUS_IN_PROGRESS,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->sales)
        ->get(route('sales.branch-withdrawals.edit', $withdrawal))
        ->assertForbidden();
});

it('allows warehouse to return a branch withdrawal to sales', function () {
    [$product] = branchProduct('Rückgabe Filialprodukt', 20);

    $this->actingAs($this->manager)
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_IN_PROGRESS,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

    $this->actingAs($this->warehouse)
        ->put(route('branch-withdrawals.update', $withdrawal), [
            'status' => BranchWithdrawal::STATUS_OPEN,
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    expect($withdrawal->fresh()->status)->toBe(BranchWithdrawal::STATUS_OPEN);

    $this->actingAs($this->sales)
        ->get(route('sales.branch-withdrawals.edit', $withdrawal))
        ->assertOk();
});
