<?php

use App\Models\Customer;
use App\Models\Offer;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => User::ROLE_MANAGER, 'is_active' => true]);
    $this->warehouse = User::factory()->create(['role' => User::ROLE_WAREHOUSE, 'is_active' => true]);
    $this->sales = User::factory()->create(['role' => User::ROLE_SALES, 'is_active' => true]);
    $this->crm = User::factory()->create(['role' => User::ROLE_CRM, 'is_active' => true]);

    $this->customer = Customer::query()->create([
        'company_name' => 'Rechte Testkunde',
    ]);

    $this->offer = Offer::query()->create([
        'customer_id' => $this->customer->id,
        'user_id' => $this->sales->id,
        'status' => Offer::STATUS_IN_PROGRESS,
        'template_type' => 'with_company',
        'document_type' => 'offer',
        'subtotal' => 0,
        'total' => 0,
        'reserved_until' => now()->addHours(72),
    ]);
});

it('denies sales access to supplier and batch administration', function () {
    $this->actingAs($this->sales)
        ->get(route('suppliers.index'))
        ->assertForbidden();

    $this->actingAs($this->sales)
        ->get(route('batches.index'))
        ->assertForbidden();
});

it('denies warehouse access to customer and price administration', function () {
    $this->actingAs($this->warehouse)
        ->get(route('customers.index'))
        ->assertForbidden();

    $this->actingAs($this->warehouse)
        ->get(route('prices.index'))
        ->assertForbidden();
});

it('limits crm to customer care without customer detail or delete access', function () {
    $this->actingAs($this->crm)
        ->get(route('customers.index'))
        ->assertOk();

    $this->actingAs($this->crm)
        ->get(route('customers.edit', $this->customer))
        ->assertOk();

    $this->actingAs($this->crm)
        ->get(route('customers.show', $this->customer))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->delete(route('customers.destroy', $this->customer))
        ->assertForbidden();
});

it('does not allow warehouse to open the branch withdrawal create route', function () {
    $this->actingAs($this->warehouse)
        ->get(route('branch-withdrawals.create'))
        ->assertForbidden();
});

it('restricts internal offer notes to manager sales and warehouse', function () {
    $this->actingAs($this->crm)
        ->post(route('offer-notes.store', $this->offer), ['note' => 'Nicht erlaubt'])
        ->assertForbidden();

    $this->actingAs($this->warehouse)
        ->post(route('offer-notes.store', $this->offer), ['note' => 'Lagerhinweis'])
        ->assertRedirect();

    $this->assertDatabaseHas('offer_internal_notes', [
        'offer_id' => $this->offer->id,
        'user_id' => $this->warehouse->id,
        'note' => 'Lagerhinweis',
    ]);
});

it('does not expose sales or warehouse top actions to crm', function () {
    $this->actingAs($this->crm)
        ->get(route('customers.index'))
        ->assertOk()
        ->assertDontSee('Neues Angebot')
        ->assertDontSee('Lager prüfen');
});
