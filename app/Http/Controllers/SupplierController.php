<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $suppliers = Supplier::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('supplier_number', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('pages.suppliers.create', [
            'supplier' => new Supplier([
                'country' => 'Deutschland',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $supplier = Supplier::create($this->validatedData($request));

        ActivityLog::record('supplier.created', $supplier, [
            'supplier_number' => $supplier->supplier_number,
            'company_name' => $supplier->company_name,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Lieferant wurde erstellt.');
    }

    public function show(Supplier $supplier): View
    {
        $products = Product::query()
            ->with(['categories', 'batches', 'supplierRecord'])
            ->where(function ($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id)
                    ->orWhere('supplier', $supplier->company_name)
                    ->orWhere('supplier', $supplier->supplier_number);
            })
            ->orderBy('name')
            ->get();

        return view('pages.suppliers.show', [
            'supplier' => $supplier,
            'products' => $products,
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('pages.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validatedData($request));

        ActivityLog::record('supplier.updated', $supplier, [
            'supplier_number' => $supplier->supplier_number,
            'company_name' => $supplier->company_name,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Lieferant wurde aktualisiert.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->products()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Lieferant kann nicht gelöscht werden, weil Produkte zugeordnet sind.');
        }

        ActivityLog::record('supplier.deleted', $supplier, [
            'supplier_number' => $supplier->supplier_number,
            'company_name' => $supplier->company_name,
        ]);

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Lieferant wurde gelöscht.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:255'],
            'whatsapp_country_code' => ['nullable', 'string', 'max:10'],
            'whatsapp' => ['nullable', 'string', 'max:255'],

            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],

            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
    }
}
