<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApplicationSetting;
use App\Models\Customer;
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

        $offers = Offer::query()
            ->with(['customer.group', 'items'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('offer_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery
                                ->where('customer_number', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.offers.index', [
            'offers' => $offers,
            'search' => $search,
            'selectedStatus' => $status,
            'statuses' => $this->statuses(),
        ]);
    }

    public function show(Offer $offer): View
    {
        $offer->load(['customer.group', 'items.product']);

        return view('pages.offers.show', compact('offer'));
    }

    public function create(): View
    {
        return view('pages.offers.create', [
            'offer' => new Offer(),
            'customers' => $this->customers(),
            'products' => $this->products(),
            'templates' => $this->templates(),
            'formItems' => collect([['product_id' => '', 'quantity' => '']]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->normalizeShippingData($this->validatedData($request));
        $customer = Customer::with('group')->findOrFail($data['customer_id']);
        $cleanItems = $this->cleanItems($data['items']);

        if ($cleanItems->isEmpty()) {
            return back()->withInput()->with('error', 'Bitte mindestens eine Produktposition mit Menge hinzufügen.');
        }

        $prepared = $this->prepareItems($customer, $cleanItems);
        $total = collect($prepared)->sum('line_total');

        $offer = DB::transaction(function () use ($customer, $data, $prepared, $total) {
            $offer = Offer::create([
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'status' => Offer::STATUS_OFFER,
                'template_type' => $data['template_type'],
                'shipping_method' => $data['shipping_method'] ?? null,
                'shipping_price_gross' => $data['shipping_price_gross'] ?? null,
                'document_type' => 'offer',
                'subtotal' => $total,
                'total' => $total,
                'reserved_until' => now()->addHours(ApplicationSetting::reservationHours()),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncOfferItems($offer, $prepared);

            ActivityLog::record('offer.created', $offer, [
                'offer_number' => $offer->offer_number,
                'customer' => $customer->company_name,
                'total' => $total,
            ]);

            return $offer;
        });

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', 'Angebot ' . $offer->offer_number . ' wurde erstellt und Ware wurde reserviert.');
    }

    public function edit(Offer $offer): View
    {
        if (! $this->canEdit($offer)) {
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', 'Erledigte, stornierte oder abgelaufene Angebote können nicht bearbeitet werden.');
        }

        $offer->load('items');

        return view('pages.offers.edit', [
            'offer' => $offer,
            'customers' => $this->customers(),
            'products' => $this->products(),
            'templates' => $this->templates(),
            'formItems' => $offer->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])->values(),
        ]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        if (! $this->canEdit($offer)) {
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', 'Dieses Angebot kann nicht mehr bearbeitet werden.');
        }

        $data = $this->normalizeShippingData($this->validatedData($request));
        $customer = Customer::with('group')->findOrFail($data['customer_id']);
        $cleanItems = $this->cleanItems($data['items']);

        if ($cleanItems->isEmpty()) {
            return back()->withInput()->with('error', 'Bitte mindestens eine Produktposition mit Menge hinzufügen.');
        }

        $prepared = $this->prepareItems($customer, $cleanItems, $offer);
        $total = collect($prepared)->sum('line_total');

        DB::transaction(function () use ($offer, $customer, $data, $prepared, $total) {
            $offer->update([
                'customer_id' => $customer->id,
                'template_type' => $data['template_type'],
                'shipping_method' => $data['shipping_method'] ?? null,
                'shipping_price_gross' => $data['shipping_price_gross'] ?? null,
                'subtotal' => $total,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
            ]);

            $offer->items()->delete();
            $this->syncOfferItems($offer, $prepared);

            ActivityLog::record('offer.updated', $offer, [
                'offer_number' => $offer->offer_number,
                'total' => $total,
            ]);
        });

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', 'Angebot wurde aktualisiert.');
    }

    public function updateStatus(Request $request, Offer $offer): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys($this->statuses()))],
        ]);
        $oldStatus = $offer->status;

        $offer->update([
            'status' => $data['status'],
        ]);

        ActivityLog::record('offer.status.updated', $offer, [
            'offer_number' => $offer->offer_number,
            'old_status' => $oldStatus,
            'new_status' => $offer->status,
        ]);

        return redirect()
            ->route('offers.show', $offer)
            ->with('success', 'Status wurde geändert.');
    }

    public function cancel(Offer $offer): RedirectResponse
    {
        if (! $offer->isReservationActive()) {
            return redirect()
                ->route('offers.index')
                ->with('error', 'Dieses Angebot kann nicht storniert werden.');
        }

        $offer->update([
            'status' => Offer::STATUS_CANCELLED,
        ]);

        ActivityLog::record('offer.cancelled', $offer, [
            'offer_number' => $offer->offer_number,
        ]);

        return redirect()
            ->route('offers.index')
            ->with('success', 'Angebot wurde storniert. Die Reservierung wurde freigegeben.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        if ($offer->status === Offer::STATUS_COMPLETED) {
            $hasFifoMovements = \App\Models\StockMovement::query()
                ->where('reference_type', Offer::class)
                ->where('reference_id', $offer->id)
                ->exists();

            if ($hasFifoMovements) {
                return redirect()
                    ->route('offers.index')
                    ->with('error', 'Erledigte Angebote mit FIFO-Abbuchung können nicht gelöscht werden. Bitte als Nachweis behalten.');
            }
        }

        $offerNumber = $offer->offer_number;

        DB::transaction(function () use ($offer, $offerNumber) {
            ActivityLog::record('offer.deleted', $offer, [
                'offer_number' => $offerNumber,
                'status' => $offer->status,
                'total' => (float) $offer->total,
            ]);

            $offer->delete();
        });

        return redirect()
            ->route('offers.index')
            ->with('success', 'Angebot ' . $offerNumber . ' wurde gelöscht.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'template_type' => ['required', 'string', 'in:with_company,without_company'],
            'shipping_method' => ['required', 'string', 'in:Lieferung,Abholung'],
            'shipping_price_gross' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.001', 'max:5000'],
        ]);
    }

    private function normalizeShippingData(array $data): array
    {
        if (($data['shipping_method'] ?? null) === 'Abholung') {
            $data['shipping_price_gross'] = null;
        }

        return $data;
    }

    private function cleanItems(array $items): Collection
    {
        return collect($items)
            ->filter(fn ($item) => ! empty($item['product_id']) && ! empty($item['quantity']))
            ->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (float) $item['quantity'],
            ])
            ->values();
    }

    private function prepareItems(Customer $customer, Collection $cleanItems, ?Offer $ignoreOffer = null): array
    {
        $productIds = $cleanItems->pluck('product_id')->unique()->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $requestedByProduct = $cleanItems
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        foreach ($requestedByProduct as $productId => $requestedQuantity) {
            $product = $products->get((int) $productId);

            if (! $product) {
                abort(422, 'Ein ausgewähltes Produkt wurde nicht gefunden.');
            }

            $maxReservable = $this->maxReservableForProduct($product, $ignoreOffer);

            if ($requestedQuantity > $maxReservable) {
                back()->withInput()->with('error', 'Nicht genug frei reservierbarer Bestand für ' . $product->product_code . ' — ' . $product->name . '. Maximal reservierbar: ' . number_format($maxReservable, 3, ',', '.'))->throwResponse();
            }
        }

        $prepared = [];

        foreach ($cleanItems as $item) {
            $product = $products->get($item['product_id']);

            ProductPriceTier::ensureForProduct($product);

            $tier = ProductPriceTier::query()
                ->where('product_id', $product->id)
                ->where('customer_group_id', $customer->customer_group_id)
                ->where('min_grams', '<=', $item['quantity'])
                ->where('max_grams', '>=', $item['quantity'])
                ->first();

            if (! $tier) {
                back()->withInput()->with('error', 'Keine passende Preisstaffel für ' . $product->product_code . ' bei ' . number_format($item['quantity'], 3, ',', '.') . ' Gramm.')->throwResponse();
            }

            $unitPrice = (float) $tier->price;
            $quantity = (float) $item['quantity'];
            $lineTotal = round($quantity * $unitPrice, 2);

            $prepared[] = [
                'product' => $product,
                'tier' => $tier,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        return $prepared;
    }

    private function syncOfferItems(Offer $offer, array $preparedItems): void
    {
        foreach ($preparedItems as $preparedItem) {
            $product = $preparedItem['product'];
            $tier = $preparedItem['tier'];

            $offer->items()->create([
                'product_id' => $product->id,
                'product_price_tier_id' => $tier->id,
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'quantity' => $preparedItem['quantity'],
                'unit' => $product->unit,
                'tier_key' => $tier->tier_key,
                'tier_label' => $tier->tier_label,
                'unit_price' => $preparedItem['unit_price'],
                'line_total' => $preparedItem['line_total'],
            ]);
        }
    }

    private function maxReservableForProduct(Product $product, ?Offer $ignoreOffer = null): float
    {
        $reserved = DB::table('offer_items')
            ->join('offers', 'offers.id', '=', 'offer_items.offer_id')
            ->where('offer_items.product_id', $product->id)
            ->whereIn('offers.status', Offer::RESERVING_STATUSES)
            ->when($ignoreOffer, fn ($query) => $query->where('offers.id', '!=', $ignoreOffer->id))
            ->sum('offer_items.quantity');

        return max(0, $product->total_stock - (float) $product->minimum_stock - (float) $reserved);
    }

    private function canEdit(Offer $offer): bool
    {
        return ! in_array($offer->status, [
            Offer::STATUS_COMPLETED,
            Offer::STATUS_CANCELLED,
            Offer::STATUS_RESERVATION_EXPIRED,
        ], true);
    }

    private function customers()
    {
        return Customer::query()->with('group')->orderBy('company_name')->get();
    }

    private function products()
    {
        return Product::query()->orderBy('name')->get();
    }

    private function templates(): array
    {
        return [
            'with_company' => 'Mit Firmendaten & Logo',
            'without_company' => 'Ohne Firmendaten & Logo',
        ];
    }

    private function statuses(): array
    {
        return [
            Offer::STATUS_OFFER => 'Angebot',
            Offer::STATUS_IN_PROGRESS => 'In Bearbeitung',
Offer::STATUS_READY => 'Abholbereit',
            Offer::STATUS_COMPLETED => 'Erledigt',
            Offer::STATUS_CANCELLED => 'Storniert',
            Offer::STATUS_RESERVATION_EXPIRED => 'Reservierung abgelaufen',
        ];
    }
}
