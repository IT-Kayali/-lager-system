<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;

function managerUser(): User
{
    return User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);
}

it('allows a manager to create a customer group with a color', function () {
    $this->actingAs(managerUser())
        ->post(route('customer-groups.store'), [
            'name' => 'Platin',
            'description' => 'Platin-Kunden',
            'color' => '#7C3AED',
        ])
        ->assertRedirect(route('settings.index') . '#customer-groups');

    $this->assertDatabaseHas('customer_groups', [
        'name' => 'Platin',
        'slug' => 'platin',
        'description' => 'Platin-Kunden',
        'color' => '#7C3AED',
    ]);
});

it('updates a group without changing its technical slug', function () {
    $group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $this->actingAs(managerUser())
        ->put(route('customer-groups.update', $group), [
            'name' => 'Premium Gold',
            'description' => 'Bevorzugte Kunden',
            'color' => '#F59E0B',
        ])
        ->assertRedirect(route('settings.index') . '#customer-groups');

    $this->assertDatabaseHas('customer_groups', [
        'id' => $group->id,
        'name' => 'Premium Gold',
        'slug' => 'gold',
        'color' => '#F59E0B',
    ]);
});

it('does not delete a group that still has customers', function () {
    $group = CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    CustomerGroup::create([
        'name' => 'Silber',
        'slug' => 'silver',
        'color' => '#9CA3AF',
    ]);

    Customer::create([
        'customer_group_id' => $group->id,
        'company_name' => 'Testkunde',
    ]);

    $this->actingAs(managerUser())
        ->delete(route('customer-groups.destroy', $group))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('customer_groups', ['id' => $group->id]);
});

it('does not delete the last remaining customer group', function () {
    $group = CustomerGroup::create([
        'name' => 'Einzige Gruppe',
        'slug' => 'einzige-gruppe',
        'color' => '#475569',
    ]);

    $this->actingAs(managerUser())
        ->delete(route('customer-groups.destroy', $group))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('customer_groups', ['id' => $group->id]);
});

it('deletes an unused group when another group remains', function () {
    CustomerGroup::create([
        'name' => 'Gold',
        'slug' => 'gold',
        'color' => '#D4AD16',
    ]);

    $unused = CustomerGroup::create([
        'name' => 'Temporär',
        'slug' => 'temporaer',
        'color' => '#64748B',
    ]);

    $this->actingAs(managerUser())
        ->delete(route('customer-groups.destroy', $unused))
        ->assertRedirect(route('settings.index') . '#customer-groups');

    $this->assertDatabaseMissing('customer_groups', ['id' => $unused->id]);
});
