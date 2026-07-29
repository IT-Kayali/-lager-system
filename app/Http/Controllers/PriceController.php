<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'product_code', 'name']);

        $groups = CustomerGroup::query()
            ->ordered()
            ->get();

        $selectedProduct = $products->firstWhere('id', (int) $request->query('product_id')) ?? $products->first();
        $selectedGroup = $groups->firstWhere('id', (int) $request->query('customer_group_id')) ?? $groups->first();

        $tiers = collect();

        if ($selectedProduct && $selectedGroup) {
            ProductPriceTier::ensureForProduct($selectedProduct);

            $tiers = ProductPriceTier::query()
                ->where('product_id', $selectedProduct->id)
                ->where('customer_group_id', $selectedGroup->id)
                ->orderByRaw("FIELD(tier_key, '50g', '100g', '250g', '500g', '1000g')")
                ->get();
        }

        return view('pages.prices.index', [
            'products' => $products,
            'groups' => $groups,
            'selectedProduct' => $selectedProduct,
            'selectedGroup' => $selectedGroup,
            'tiers' => $tiers,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'customer_group_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'prices' => ['required', 'array'],
            'prices.*' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $group = CustomerGroup::findOrFail($data['customer_group_id']);

        ProductPriceTier::ensureForProduct($product);

        foreach ($data['prices'] as $tierId => $price) {
            $tier = ProductPriceTier::query()
                ->where('id', $tierId)
                ->where('product_id', $product->id)
                ->where('customer_group_id', $group->id)
                ->firstOrFail();

            $oldPrice = (float) $tier->price;
            $newPrice = round((float) $price, 2);

            if ($oldPrice !== $newPrice) {
                $tier->update([
                    'price' => $newPrice,
                ]);

                ActivityLog::record('price.updated', $tier, [
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'customer_group' => $group->name,
                    'tier' => $tier->tier_label,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                ]);
            }
        }

        return redirect()
            ->route('prices.index', [
                'product_id' => $product->id,
                'customer_group_id' => $group->id,
            ])
            ->with('success', 'Preise wurden erfolgreich gespeichert.');
    }
}
