<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerGroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('customerGroupCreate', [
            'name' => ['required', 'string', 'max:100', 'unique:customer_groups,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $group = CustomerGroup::create([
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'color' => strtoupper($data['color']),
        ]);

        Product::query()->select('id')->cursor()->each(
            fn (Product $product) => ProductPriceTier::ensureForProduct($product)
        );

        return $this->redirectToGroups()
            ->with('success', "Kundengruppe „{$group->name}“ wurde angelegt.");
    }

    public function update(Request $request, CustomerGroup $customerGroup): RedirectResponse
    {
        $data = $request->validateWithBag('customerGroupUpdate' . $customerGroup->id, [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customer_groups', 'name')->ignore($customerGroup->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $customerGroup->update([
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'color' => strtoupper($data['color']),
        ]);

        return $this->redirectToGroups()
            ->with('success', "Kundengruppe „{$customerGroup->name}“ wurde aktualisiert.");
    }

    public function destroy(CustomerGroup $customerGroup): RedirectResponse
    {
        if (CustomerGroup::query()->count() <= 1) {
            return $this->redirectToGroups()
                ->with('error', 'Die letzte Kundengruppe kann nicht gelöscht werden.');
        }

        $customerCount = $customerGroup->customers()->count();

        if ($customerCount > 0) {
            return $this->redirectToGroups()
                ->with('error', "Die Kundengruppe kann nicht gelöscht werden, weil ihr noch {$customerCount} Kunde(n) zugeordnet sind.");
        }

        $name = $customerGroup->name;
        $customerGroup->delete();

        return $this->redirectToGroups()
            ->with('success', "Kundengruppe „{$name}“ wurde gelöscht.");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kundengruppe';
        $slug = $base;
        $suffix = 2;

        while (CustomerGroup::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function redirectToGroups(): RedirectResponse
    {
        return redirect()->to(route('settings.index') . '#customer-groups');
    }
}
