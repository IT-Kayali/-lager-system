<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Offer;
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

    public function show(Customer $customer): View
    {
        $customer->load('group');

        $offers = Offer::query()
            ->withCount('items')
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        $walletTransactions = $customer->walletTransactions()
            ->with(['user', 'offer'])
            ->latest()
            ->paginate(15, ['*'], 'wallet_page');

        return view('pages.customers.show', [
            'customer' => $customer,
            'offers' => $offers,
            'walletTransactions' => $walletTransactions,
            'walletBalance' => $customer->wallet_balance,
            'statusLabels' => Offer::STATUS_LABELS,
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
        $data = $request->validate([
            'customer_group_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],

            'billing_street' => ['nullable', 'string', 'max:255'],
            'billing_house_number' => ['nullable', 'string', 'max:50'],
            'billing_postal_code' => ['nullable', 'string', 'max:50'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_country' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:3000'],

            'delivery_address_different' => ['nullable', 'boolean'],
            'delivery_street' => ['nullable', 'string', 'max:255'],
            'delivery_house_number' => ['nullable', 'string', 'max:50'],
            'delivery_postal_code' => ['nullable', 'string', 'max:50'],
            'delivery_city' => ['nullable', 'string', 'max:255'],
            'delivery_country' => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:3000'],

            'vat_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! $request->boolean('delivery_address_different')) {
            $data['delivery_street'] = $data['billing_street'] ?? null;
            $data['delivery_house_number'] = $data['billing_house_number'] ?? null;
            $data['delivery_postal_code'] = $data['billing_postal_code'] ?? null;
            $data['delivery_city'] = $data['billing_city'] ?? null;
            $data['delivery_country'] = $data['billing_country'] ?? null;
            $data['delivery_address'] = $data['billing_address'] ?? null;
        }

        unset($data['delivery_address_different']);

        return $data;
    }

    private function groups()
    {
        return CustomerGroup::query()
            ->ordered()
            ->get();
    }
}
