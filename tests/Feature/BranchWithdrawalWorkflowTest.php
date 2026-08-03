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

it('deducts all products by fifo when the status is issued', function () {
    [$product, $batch] = branchProduct('FIFO Filialprodukt', 10);

    $this->actingAs($this->warehouse)
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_DEZ,
            'status' => BranchWithdrawal::STATUS_ISSUED,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    $withdrawal = BranchWithdrawal::query()->firstOrFail();

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
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_CANCELLED,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    expect((float) $batch->fresh()->quantity)->toBe(10.0)
        ->and($withdrawal->fresh()->status)->toBe(BranchWithdrawal::STATUS_CANCELLED)
        ->and($withdrawal->fresh()->processed_by)->toBeNull();
});

it('returns issued stock before a manager deletes the withdrawal', function () {
    [$product, $batch] = branchProduct('Löschbares Filialprodukt', 10);

    $this->actingAs($this->manager)->post(route('branch-withdrawals.store'), [
        'branch_name' => BranchWithdrawal::BRANCH_DEZ,
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

    $this->assertDatabaseHas('branch_withdrawals', ['id' => $withdrawal->id]);
});

it('keeps every product unchanged when one issued position has insufficient stock', function () {
    [$firstProduct, $firstBatch] = branchProduct('Ausreichendes Produkt', 10);
    [$secondProduct, $secondBatch] = branchProduct('Knappes Produkt', 1);

    $this->actingAs($this->manager)
        ->from(route('branch-withdrawals.create'))
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_ISSUED,
            'items' => [
                ['product_id' => $firstProduct->id, 'quantity' => 2],
                ['product_id' => $secondProduct->id, 'quantity' => 2],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.create'))
        ->assertSessionHasErrors('items');

    expect((float) $firstBatch->fresh()->quantity)->toBe(10.0)
        ->and((float) $secondBatch->fresh()->quantity)->toBe(1.0)
        ->and(BranchWithdrawal::query()->count())->toBe(0);
});
