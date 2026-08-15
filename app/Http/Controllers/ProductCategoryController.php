<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Services\CategoryPriorityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly CategoryPriorityService $priorityService)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $categories = ProductCategory::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('LOWER(name) ASC')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.product-categories.index', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('pages.product-categories.create', [
            'category' => new ProductCategory([
                'priority' => $this->priorityService->nextPriority(),
                'is_active' => true,
                'color' => '#d4af37',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->priorityService->create($this->validatedData($request));

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Kategorie wurde erfolgreich angelegt.');
    }

    public function show(ProductCategory $productCategory): View
    {
        $productCategory->load([
            'products' => fn ($query) => $query
                ->with('supplierRecord')
                ->naturalNameOrder(),
        ]);

        return view('pages.product-categories.show', [
            'category' => $productCategory,
        ]);
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('pages.product-categories.edit', [
            'category' => $productCategory,
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->priorityService->update($productCategory, $this->validatedData($request));

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Kategorie wurde erfolgreich aktualisiert.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        if ($productCategory->products()->exists()) {
            return redirect()
                ->route('product-categories.index')
                ->with('error', 'Kategorie kann nicht gelöscht werden, weil noch Produkte zugeordnet sind.');
        }

        $this->priorityService->delete($productCategory);

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Kategorie wurde gelöscht.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'integer', 'min:1', 'max:999999'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['priority'] = (int) $data['priority'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
