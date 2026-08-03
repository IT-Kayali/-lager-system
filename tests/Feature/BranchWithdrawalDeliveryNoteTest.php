<?php

use App\Models\BranchWithdrawal;
use App\Models\DocumentTemplate;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
});

function deliveryNoteWithdrawal(): BranchWithdrawal
{
    $product = Product::query()->create([
        'name' => 'Filial-Lieferschein Testprodukt',
        'unit' => 'gram',
        'minimum_stock' => 0,
    ]);

    $withdrawal = BranchWithdrawal::query()->create([
        'product_id' => $product->id,
        'user_id' => auth()->id(),
        'withdrawal_number' => 'FIL-PDF-TEST',
        'branch_name' => BranchWithdrawal::BRANCH_DEZ,
        'status' => BranchWithdrawal::STATUS_OPEN,
        'quantity' => 12.5,
        'stock_before' => 0,
        'stock_after' => 0,
        'batch_allocations' => [],
        'note' => 'Interner Testhinweis',
    ]);

    $withdrawal->items()->create([
        'product_id' => $product->id,
        'quantity' => 12.5,
        'stock_before' => 0,
        'stock_after' => 0,
        'batch_allocations' => [],
    ]);

    return $withdrawal;
}

it('shows a simplified branch withdrawal table with the creator and delivery note action', function () {
    $withdrawal = deliveryNoteWithdrawal();

    $this->actingAs($this->manager)
        ->get(route('branch-withdrawals.index'))
        ->assertOk()
        ->assertSee('class="premium-table branch-table"', false)
        ->assertSee(route('branch-withdrawals.delivery-note', $withdrawal), false)
        ->assertSee($this->manager->name)
        ->assertDontSee('Produkte und Mengen')
        ->assertDontSee('Filial-Lieferschein Testprodukt')
        ->assertDontSee('Ausgegeben:');
});

it('streams an internal branch delivery note as pdf', function () {
    $withdrawal = deliveryNoteWithdrawal();

    DocumentTemplate::query()->updateOrCreate(
        ['key' => DocumentTemplate::WITH_COMPANY],
        [
            'name' => 'Mit Firmendaten & Logo',
            'company_name' => 'Alowidat Test',
            'show_company_details' => true,
            'show_logo' => false,
        ]
    );

    $this->actingAs($this->manager)
        ->get(route('branch-withdrawals.delivery-note', $withdrawal))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
