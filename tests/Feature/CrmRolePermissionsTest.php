<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;

beforeEach(function () {
    $this->crm = User::factory()->create([
        'role' => User::ROLE_CRM,
        'is_active' => true,
    ]);

    $group = CustomerGroup::query()->create([
        'name' => 'CRM Testgruppe',
        'slug' => 'crm-testgruppe',
        'color' => '#D4AD16',
    ]);

    $this->customer = Customer::query()->create([
        'customer_group_id' => $group->id,
        'company_name' => 'CRM Testkunde',
        'delivery_city' => 'Rheine',
    ]);
});

it('redirects CRM users directly to customer maintenance', function () {
    expect($this->crm->isCrm())->toBeTrue()
        ->and($this->crm->roleLabel())->toBe('CRM / Kundenpflege');

    $this->actingAs($this->crm)
        ->get(route('dashboard'))
        ->assertRedirect(route('customers.index'));
});

it('allows CRM to list create and edit customers', function () {
    $this->actingAs($this->crm)
        ->get(route('customers.index'))
        ->assertOk()
        ->assertSee('CRM Testkunde')
        ->assertSee('Kunde hinzufügen')
        ->assertDontSee('Kundenprofil anzeigen')
        ->assertDontSee('Kunde wirklich löschen?');

    $this->actingAs($this->crm)
        ->get(route('customers.create'))
        ->assertOk();

    $this->actingAs($this->crm)
        ->get(route('customers.edit', $this->customer))
        ->assertOk();
});

it('blocks CRM from customer preview deletion wallet and every other work area', function () {
    $this->actingAs($this->crm)
        ->get(route('customers.show', $this->customer))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->delete(route('customers.destroy', $this->customer))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->post(route('customers.wallet-transactions.store', $this->customer), [
            'type' => 'credit',
            'amount' => 10,
        ])
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('products.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('offers.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('suppliers.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('prices.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('warnings.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('statistics.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('settings.index'))
        ->assertForbidden();

    $this->actingAs($this->crm)
        ->get(route('security.index'))
        ->assertForbidden();
});
