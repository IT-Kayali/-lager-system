<?php

use App\Models\BranchWithdrawal;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;

function reservationProduct(string $name, float $quantity): Product
{
    $product = Product::query()->create([
        'name' => $name,
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    ProductBatch::query()->create([
        'product_id' => $product->id,
        'quantity' => $quantity,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    return $product;
}

it('reserves branch withdrawal quantities immediately when an open withdrawal is created', function () {
    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
    $product = reservationProduct('Reserviertes Filialprodukt', 10);

    $this->actingAs($manager)
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_MAIN,
            'status' => BranchWithdrawal::STATUS_OPEN,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    $product->refresh();

    expect((float) $product->total_stock)->toBe(10.0)
        ->and((float) $product->reserved_stock)->toBe(4.0)
        ->and((float) $product->available_stock)->toBe(6.0);
});

it('rejects an open branch withdrawal immediately when the requested quantity is unavailable', function () {
    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
    $product = reservationProduct('Zu wenig Filialbestand', 5);

    $response = $this->actingAs($manager)
        ->from(route('branch-withdrawals.create'))
        ->post(route('branch-withdrawals.store'), [
            'branch_name' => BranchWithdrawal::BRANCH_DEZ,
            'status' => BranchWithdrawal::STATUS_OPEN,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 6],
            ],
        ]);

    $response
        ->assertRedirect(route('branch-withdrawals.create'))
        ->assertSessionHasErrors('items');

    expect(BranchWithdrawal::query()->count())->toBe(0)
        ->and((float) $product->fresh()->available_stock)->toBe(5.0);
});

it('releases a branch reservation when a manager cancels an open withdrawal', function () {
    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
     $product = reservationProduct('Freizugebender Filialbestand', 10);

    $this->actingAs($manager)->post(route('branch-withdrawals.store'), [
        'branch_name' => BranchWithdrawal::BRANCH_MAIN,
        'status' => BranchWithdrawal::STATUS_OPEN,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 4],
        ],
    ]);

    $withdrawal = BranchWithdrawal::query()->firstOrFail();
    expect((float) $product->fresh()->available_stock)->toBe(6.0);

    $this->actingAs($manager)
        ->put(route('branch-withdrawals.update', $withdrawal), [
            'status' => BranchWithdrawal::STATUS_CANCELLED,
        ])
        ->assertRedirect(route('branch-withdrawals.index'));

    expect((float) $product->fresh()->reserved_stock)->toBe(0.0)
        ->and((float) $product->fresh()->available_stock)->toBe(10.0);
});
