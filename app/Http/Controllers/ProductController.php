<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\ReservationReleaseService;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        app(\App\Services\ReservationReleaseService::class)->releaseExpired();

        $search = trim((string) $request->query('search'));

        $products = Product::query()
            ->with(['batches' => fn ($query) => $query->orderBy('received_at')->orderBy('id')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('product_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%")
                        ->orWhere('storage_location', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('pages.products.create', [
            'product' => new Product(),
            'units' => $this->units(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produkt wurde erfolgreich angelegt.');
    }

    public function edit(Product $product): View
    {
        return view('pages.products.edit', [
            'product' => $product,
            'units' => $this->units(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produkt wurde erfolgreich aktualisiert.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produkt wurde gelöscht.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', Rule::in(array_keys($this->units()))],
            'supplier' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function units(): array
    {
        return [
            'gram' => 'Gramm',
            'liter' => 'Liter',
            'piece' => 'Stück',
        ];
    }
}
