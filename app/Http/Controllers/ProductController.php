<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\ReservationReleaseService;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        app(\App\Services\ReservationReleaseService::class)->releaseExpired();

        $search = trim((string) $request->query('search'));

        $products = Product::query()->with('categories')
            ->with(['batches' => fn ($query) => $query->orderBy('received_at')->orderBy('id')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('product_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->naturalNameOrder()
            ->paginate(15)
            ->withQueryString();

        return view('pages.products.index', compact('products', 'search'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'supplierRecord',
            'categories' => fn ($query) => $query->orderByRaw('LOWER(name) ASC')->orderBy('id'),
            'batches' => fn ($query) => $query
                ->orderBy('received_at')
                ->orderBy('id'),
        ]);

        return view('pages.products.show', [
            'product' => $product,
        ]);
    }

    public function create(): View
    {
        $product = new Product([
            'unit' => 'gram',
            'minimum_stock' => 0,
        ]);

        $suppliers = Supplier::query()
            ->orderByRaw('LOWER(company_name) ASC')
            ->orderBy('id')
            ->get();

        return view('pages.products.create', compact('product', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $initialBatchData = $this->validatedInitialBatchData($request);

        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        DB::transaction(function () use ($data, $categoryIds, $initialBatchData): void {
            $product = Product::create($data);
            $product->categories()->sync($categoryIds);

            $initialBatches = collect($initialBatchData['initial_batches'] ?? [])
                ->filter(fn ($row) => (float) ($row['quantity'] ?? 0) > 0)
                ->values();

            foreach ($initialBatches as $row) {
                $receivedAt = Carbon::parse(($row['received_at'] ?? null) ?: now()->toDateString())->startOfDay();
                $expiresAt = ($row['expires_at'] ?? null) ?: $receivedAt
                    ->copy()
                    ->addMonthsNoOverflow(ApplicationSetting::defaultBatchExpiryMonths())
                    ->toDateString();

                $batch = ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => ($row['batch_number'] ?? null) ?: null,
                    'quantity' => (float) ($row['quantity'] ?? 0),
                    'received_at' => $receivedAt->toDateString(),
                    'expires_at' => $expiresAt,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $batch->quantity,
                    'note' => 'Wareneingang / Charge beim Produkt angelegt',
                ]);
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Produkt wurde erfolgreich angelegt.');
    }

    public function edit(Product $product): View
    {
        $suppliers = Supplier::query()
            ->orderByRaw('LOWER(company_name) ASC')
            ->orderBy('id')
            ->get();

        return view('pages.products.edit', compact('product', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request);

        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $product->update($data);
        $product->categories()->sync($categoryIds);

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

    private function validatedInitialBatchData(Request $request): array
    {
        return $request->validate([
            'initial_batches' => ['nullable', 'array'],
            'initial_batches.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'initial_batches.*.batch_number' => [
                'nullable',
                'string',
                'max:255',
                'distinct',
                Rule::unique('product_batches', 'batch_number'),
            ],
            'initial_batches.*.received_at' => ['nullable', 'date'],
            'initial_batches.*.expires_at' => ['nullable', 'date'],
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manufacturer_designation' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', Rule::in(array_keys($this->units()))],
            'supplier' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:product_categories,id'],
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
