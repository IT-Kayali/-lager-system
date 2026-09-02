<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApplicationSetting;
use App\Models\BranchWithdrawal;
use App\Models\Customer;
use App\Models\ManualPriceRule;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(Request $request): View
    {
        app(\App\Services\ReservationReleaseService::class)->releaseExpired();
        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $offers = Offer::query()->with(['customer.group', 'items'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('offer_number', 'like', "%{$search}%")->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('customer_number', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%");
                    });
                });
            })->when($status !== '', fn ($query) => $query->where('status', $status))->latest()->paginate(15)->withQueryString();
        return view('pages.offers.index', ['offers' => $offers, 'search' => $search, 'selectedStatus' => $status, 'statuses' => $this->statuses()]);
    }

    public function show(Offer $offer): View
    {
        $offer->load(['customer.group', 'items.product', 'internalNotes.user']);
        return view('pages.offers.show', compact('offer'));
    }

    public function create(): View
    {
        return view('pages.offers.create', ['offer' => new Offer(), 'customers' => $this->customers(), 'products' => $this->products(), 'templates' => $this->templates(), 'formItems' => collect([['product_id' => '', 'quantity' => '']])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->normalizeShippingData($this->validatedData($request));
        $customer = Customer::with('group')->findOrFail($data['customer_id']);
        $cleanItems = $this->cleanItems($data['items']);
        if ($cleanItems->isEmpty()) return back()->withInput()->with('error', 'Bitte mindestens eine Produktposition mit Menge hinzufügen.');
        $prepared = $this->prepareItems($customer, $cleanItems);
        $total = collect($prepared)->sum('line_total');
        $offer = DB::transaction(function () use ($customer, $data, $prepared, $total) {
            $offer = Offer::create(['customer_id' => $customer->id, 'user_id' => auth()->id(), 'status' => Offer::STATUS_OFFER, 'template_type' => $data['template_type'], 'shipping_method' => $data['shipping_method'] ?? null, 'shipping_price_gross' => $data['shipping_price_gross'] ?? null, 'carton_count' => $data['carton_count'] ?? null, 'document_type' => 'offer', 'subtotal' => $total, 'total' => $total, 'reserved_until' => now()->addHours(ApplicationSetting::reservationHours()), 'notes' => $data['notes'] ?? null]);
            $this->syncOfferItems($offer, $prepared);
            ActivityLog::record('offer.created', $offer, ['offer_number' => $offer->offer_number, 'customer' => $customer->company_name, 'total' => $total]);
            return $offer;
        });
        return redirect()->route('offers.show', $offer)->with('success', 'Angebot ' . $offer->offer_number . ' wurde erstellt und Ware wurde reserviert.');
    }

    public function edit(Offer $offer): View
    {
        if (! $this->canEdit($offer)) return redirect()->route('offers.show', $offer)->with('error', 'Erledigte, stornierte oder abgelaufene Angebote können nicht bearbeitet werden.');
        $offer->load('items');
        return view('pages.offers.edit', ['offer' => $offer, 'customers' => $this->customers(), 'products' => $this->products(), 'templates' => $this->templates(), 'formItems' => $offer->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity])->values()]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        if (! $this->canEdit($offer)) return redirect()->route('offers.show', $offer)->with('error', 'Dieses Angebot kann nicht mehr bearbeitet werden.');
        $data = $this->normalizeShippingData($this->validatedData($request));
        $customer = Customer::with('group')->findOrFail($data['customer_id']);
        $cleanItems = $this->cleanItems($data['items']);
        if ($cleanItems->isEmpty()) return back()->withInput()->with('error', 'Bitte mindestens eine Produktposition mit Menge hinzufügen.');
        $prepared = $this->prepareItems($customer, $cleanItems, $offer);
        $total = collect($prepared)->sum('line_total');
        DB::transaction(function () use ($offer, $customer, $data, $prepared, $total) {
            $offer->update(['customer_id' => $customer->id, 'template_type' => $data['template_type'], 'shipping_method' => $data['shipping_method'] ?? null, 'shipping_price_gross' => $data['shipping_price_gross'] ?? null, 'carton_count' => $data['carton_count'] ?? null, 'subtotal' => $total, 'total' => $total, 'notes' => $data['notes'] ?? null]);
            $offer->items()->delete(); $this->syncOfferItems($offer, $prepared);
            ActivityLog::record('offer.updated', $offer, ['offer_number' => $offer->offer_number, 'total' => $total]);
        });
        return redirect()->route('offers.show', $offer)->with('success', 'Angebot wurde aktualisiert.');
    }

    public function updateStatus(Request $request, Offer $offer): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'string', 'in:' . implode(',', array_keys($this->statuses()))]]); $oldStatus = $offer->status;
        $offer->update(['status' => $data['status']]);
        ActivityLog::record('offer.status.updated', $offer, ['offer_number' => $offer->offer_number, 'old_status' => $oldStatus, 'new_status' => $offer->status]);
        return redirect()->route('offers.show', $offer)->with('success', 'Status wurde geändert.');
    }

    public function cancel(Offer $offer): RedirectResponse
    {
        if (! $offer->isReservationActive()) return redirect()->route('offers.index')->with('error', 'Dieses Angebot kann nicht storniert werden.');
        $offer->update(['status' => Offer::STATUS_CANCELLED]);
        ActivityLog::record('offer.cancelled', $offer, ['offer_number' => $offer->offer_number]);
        return redirect()->route('offers.index')->with('success', 'Angebot wurde storniert. Die Reservierung wurde freigegeben.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        if ($offer->status === Offer::STATUS_COMPLETED) {
            $hasFifoMovements = \App\Models\StockMovement::query()->where('reference_type', Offer::class)->where('reference_id', $offer->id)->exists();
            if ($hasFifoMovements) return redirect()->route('offers.index')->with('error', 'Erledigte Angebote mit FIFO-Abbuchung können nicht gelöscht werden. Bitte als Nachweis behalten.');
        }
        $number = $offer->offer_number; $offer->items()->delete(); $offer->delete();
        ActivityLog::record('offer.deleted', null, ['offer_number' => $number]);
        return redirect()->route('offers.index')->with('success', 'Angebot wurde gelöscht.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate(['customer_id' => ['required', 'exists:customers,id'], 'template_type' => ['required', 'in:with_company,without_company'], 'shipping_method' => ['nullable', 'string', 'max:255'], 'shipping_price_gross' => ['nullable', 'numeric', 'min:0'], 'carton_count' => ['nullable', 'integer', 'min:0'], 'notes' => ['nullable', 'string'], 'items' => ['required', 'array'], 'items.*.product_id' => ['nullable', 'exists:products,id'], 'items.*.quantity' => ['nullable', 'numeric', 'min:0.001']]);
    }

    private function cleanItems(array $items): Collection { return collect($items)->filter(fn ($item) => ! empty($item['product_id']) && (float) ($item['quantity'] ?? 0) > 0)->values(); }

    private function prepareItems(Customer $customer, Collection $items, ?Offer $editingOffer = null): array
    {
        $products = Product::query()->with(['categories', 'priceTiers', 'manualPriceRules'])->whereIn('id', $items->pluck('product_id'))->get()->keyBy('id'); $prepared = [];
        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']); $quantity = (float) $item['quantity']; if (! $product) continue;
            $reservedElsewhere = $this->reservedQuantityForProduct($product->id, $editingOffer?->id); $available = max(0, (float) $product->total_stock - $reservedElsewhere);
            if ($quantity > $available) abort(422, "Für {$product->name} sind nur {$available} {$product->unitLabel('de')} verfügbar.");
            $pricing = $this->resolvePrice($product, $customer, $quantity);
            $prepared[] = ['product_id' => $product->id, 'product_name' => $product->name, 'product_code' => $product->product_code, 'quantity' => $quantity, 'unit' => $product->unit, 'tier_key' => $pricing['key'], 'tier_label' => $pricing['label'], 'unit_price' => $pricing['price'], 'line_total' => round($quantity * $pricing['price'], 2)];
        } return $prepared;
    }

    private function resolvePrice(Product $product, Customer $customer, float $quantity): array
    {
        $groupId = $customer->customer_group_id; if (! $groupId) return ['key' => 'none', 'label' => 'Keine Kundengruppe', 'price' => 0.0];
        if (! $product->usesPriceTiers()) {
            $rule = ManualPriceRule::query()->where('product_id', $product->id)->where('customer_group_id', $groupId)->where('min_quantity', '<=', $quantity)->where(fn ($q) => $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity))->orderByDesc('min_quantity')->first();
            return $rule ? ['key' => 'manual:' . $rule->id, 'label' => $rule->rangeLabel($product->unitLabel('de')), 'price' => (float) $rule->price] : ['key' => 'manual:none', 'label' => 'Keine Preisregel', 'price' => 0.0];
        }
        $tier = ProductPriceTier::query()->where('product_id', $product->id)->where('customer_group_id', $groupId)->where('min_grams', '<=', $quantity)->where('max_grams', '>=', $quantity)->orderByDesc('min_grams')->first();
        return $tier ? ['key' => $tier->tier_key, 'label' => $tier->tier_label, 'price' => (float) $tier->price] : ['key' => 'none', 'label' => 'Keine Preisstufe', 'price' => 0.0];
    }

    private function syncOfferItems(Offer $offer, array $prepared): void { foreach ($prepared as $item) $offer->items()->create($item); }
    private function reservedQuantityForProduct(int $productId, ?int $excludeOfferId = null): float
    {
        $offerReserved = (float) \App\Models\OfferItem::query()->where('product_id', $productId)->whereHas('offer', fn ($q) => $q->whereIn('status', Offer::RESERVING_STATUSES)->when($excludeOfferId, fn ($q2) => $q2->where('id', '!=', $excludeOfferId)))->sum('quantity');
        $branchReserved = (float) \App\Models\BranchWithdrawalItem::query()->where('product_id', $productId)->whereHas('withdrawal', fn ($q) => $q->whereIn('status', BranchWithdrawal::RESERVING_STATUSES))->sum('quantity');
        return $offerReserved + $branchReserved;
    }
    private function canEdit(Offer $offer): bool { return ! $offer->isFinal(); }
    private function customers(): Collection { return Customer::query()->with('group')->orderByRaw('LOWER(company_name) ASC')->get(); }
    private function products(): Collection { return Product::query()->with(['categories', 'priceTiers', 'manualPriceRules'])->naturalNameOrder()->get(); }
    private function templates(): array { return ['with_company' => 'Mit Firmendaten & Logo', 'without_company' => 'Ohne Firmendaten & Logo']; }
    private function statuses(): array { return Offer::STATUS_LABELS; }
    private function normalizeShippingData(array $data): array { $data['shipping_price_gross'] = isset($data['shipping_price_gross']) && $data['shipping_price_gross'] !== '' ? round((float) $data['shipping_price_gross'], 2) : null; return $data; }
}
