<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(Request $request): View
    {
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

    public function create(): View
    {
        return view('pages.offers.create', [
            'customers' => $this->customers(),
            'products' => $this->products(),
            'templates' => $this->templates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'template_type' => ['required', 'string', 'in:with_company,without_company'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.001', 'max:5000'],
        ]);

        $customer = Customer::query()
            ->with('group')
            ->findOrFail($data['customer_id']);

        $cleanItems = collect($data['items'])
            ->filter(fn ($item) => ! empty($item['product_id']) && ! empty($item['quantity']))
            ->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (float) $item['quantity'],
            ])
            ->values();

        if ($cleanItems->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Bitte mindestens eine Produktposition mit Menge hinzufügen.');
        }

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
                return back()->withInput()->with('error', 'Ein ausgewähltes Produkt wurde nicht gefunden.');
            }

            if ($requestedQuantity > $product->max_reservable) {
                return back()
                    ->withInput()
                    ->with('error', 'Nicht genug frei reservierbarer Bestand für ' . $product->product_code . ' — ' . $product->name . '. Maximal reservierbar: ' . number_format($product->max_reservable, 3, ',', '.'));
            }
        }

        $preparedItems = [];
        $total = 0.0;

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
                return back()
                    ->withInput()
                    ->with('error', 'Für ' . $product->product_code . ' — ' . $product->name . ' gibt es keine passende Preisstaffel für ' . number_format($item['quantity'], 3, ',', '.') . ' Gramm.');
            }

            $lineTotal = (float) $tier->price;
            $total += $lineTotal;

            $preparedItems[] = [
                'product' => $product,
                'tier' => $tier,
                'quantity' => $item['quantity'],
                'unit_price' => $lineTotal,
                'line_total' => $lineTotal,
            ];
        }

        $offer = DB::transaction(function () use ($customer, $data, $preparedItems, $total) {
            $offer = Offer::create([
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'status' => Offer::STATUS_OFFER,
                'template_type' => $data['template_type'],
                'document_type' => 'offer',
                'subtotal' => $total,
                'total' => $total,
                'reserved_until' => now()->addHours(24),
                'notes' => $data['notes'] ?? null,
            ]);

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

            ActivityLog::record('offer.created', $offer, [
                'offer_number' => $offer->offer_number,
                'customer' => $customer->company_name,
                'total' => $total,
                'reserved_until' => $offer->reserved_until?->toDateTimeString(),
            ]);

            return $offer;
        });

        return redirect()
            ->route('offers.index')
            ->with('success', 'Angebot ' . $offer->offer_number . ' wurde erstellt und Ware wurde bis ' . $offer->reserved_until->format('d.m.Y H:i') . ' reserviert.');
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

    private function customers()
    {
        return Customer::query()
            ->with('group')
            ->orderBy('company_name')
            ->get();
    }

    private function products()
    {
        return Product::query()
            ->orderBy('name')
            ->get();
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
            Offer::STATUS_RESERVED => 'Reserviert',
            Offer::STATUS_READY => 'Abholbereit',
            Offer::STATUS_COMPLETED => 'Erledigt',
            Offer::STATUS_CANCELLED => 'Storniert',
            Offer::STATUS_RESERVATION_EXPIRED => 'Reservierung abgelaufen',
        ];
    }
}
