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

it('allows a manager to create a customer group with manual colors', function () {
    $this->actingAs(managerUser())
        ->post(route('customer-groups.store'), [
            'name' => 'Platin',
            'description' => 'Platin-Kunden',
            'color' => '#7C3AED',
            'text_color_auto' => '0',
            'text_color' => '#F8FAFC',
        ])
        ->assertRedirect(route('settings.index') . '#customer-groups');

    $this->assertDatabaseHas('customer_groups', [
        'name' => 'Platin',
        'slug' => 'platin',
        'description' => 'Platin-Kunden',
        'color' => '#7C3AED',
        'text_color' => '#F8FAFC',
    ]);
});

it('stores no text color when automatic contrast is enabled', function () {
    $this->actingAs(managerUser())
        ->post(route('customer-groups.store'), [
            'name' => 'Bronze',
            'color' => '#92400E',
            'text_color_auto' => '1',
            'text_color' => '#000000',
        ])
        ->assertRedirect(route('settings.index') . '#customer-groups');

    $this->assertDatabaseHas('customer_groups', [
        'name' => 'Bronze',
        'color' => '#92400E',
        'text_color' => null,
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
            'text_color_auto' => '0',
            'text_color' => '#111827',
        ])
        ->assertRedirect(route('settings.index') . '#customer-groups');

    $this->assertDatabaseHas('customer_groups', [
        'id' => $group->id,
        'name' => 'Premium Gold',
        'slug' => 'gold',
        'color' => '#F59E0B',
        'text_color' => '#111827',
    ]);
});

it('clears a manual text color when automatic contrast is enabled', function () {
    $group = CustomerGroup::create([
        'name' => 'Diamond',
        'slug' => 'diamond',
        'color' => '#2563EB',
        'text_color' => '#FFFF00',
    ]);

    $this->actingAs(managerUser())
        ->put(route('customer-groups.update', $group), [
            'name' => 'Diamond',
            'color' => '#2563EB',
            'text_color_auto' => '1',
            'text_color' => '#FFFF00',
        ])
        ->assertRedirect(route('settings.index') . '#customer-groups');

    expect($group->fresh()->text_color)->toBeNull();
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
