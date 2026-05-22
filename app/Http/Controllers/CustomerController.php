<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $group = trim((string) $request->query('group'));

        $customers = Customer::query()
            ->with('group')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('customer_number', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%");
                });
            })
            ->when($group !== '', function ($query) use ($group) {
                $query->whereHas('group', fn ($groupQuery) => $groupQuery->where('slug', $group));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.customers.index', [
            'customers' => $customers,
            'groups' => $this->groups(),
            'search' => $search,
            'selectedGroup' => $group,
        ]);
    }

    public function create(): View
    {
        return view('pages.customers.create', [
            'customer' => new Customer(),
            'groups' => $this->groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validatedData($request));

        return redirect()
            ->route('customers.index')
            ->with('success', 'Kunde wurde erfolgreich angelegt.');
    }

    public function edit(Customer $customer): View
    {
        return view('pages.customers.edit', [
            'customer' => $customer,
            'groups' => $this->groups(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validatedData($request));

        return redirect()
            ->route('customers.index')
            ->with('success', 'Kunde wurde erfolgreich aktualisiert.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if (Schema::hasTable('offers')) {
            $usedInOffers = \DB::table('offers')
                ->where('customer_id', $customer->id)
                ->exists();

            if ($usedInOffers) {
                return redirect()
                    ->route('customers.index')
                    ->with('error', 'Kunde kann nicht gelöscht werden, weil er bereits in Angeboten verwendet wird.');
            }
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Kunde wurde gelöscht.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'customer_group_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:3000'],
            'billing_address' => ['nullable', 'string', 'max:3000'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function groups()
    {
        return CustomerGroup::query()
            ->orderByRaw("FIELD(slug, 'gold', 'silver', 'diamond')")
            ->orderBy('name')
            ->get();
    }
}
