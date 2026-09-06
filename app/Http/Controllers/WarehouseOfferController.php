<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApplicationSetting;
use App\Models\Offer;
use App\Services\OfferFulfillmentService;
use App\Services\ReservationReleaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WarehouseOfferController extends Controller
{
    private const VISIBLE_STATUSES = [
        Offer::STATUS_IN_PROGRESS,
        Offer::STATUS_READY,
        Offer::STATUS_COMPLETED,
    ];

    public function index(Request $request, ReservationReleaseService $reservationReleaseService): View
    {
        $reservationReleaseService->releaseExpired();

        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $searchField = (string) $request->query('search_field', 'all');
        $exact = $request->boolean('exact');

        $allowedSearchFields = ['all', 'number', 'customer', 'customer_number', 'product'];
        if (! in_array($searchField, $allowedSearchFields, true)) {
            $searchField = 'all';
        }

        if (! in_array($status, self::VISIBLE_STATUSES, true)) {
            $status = '';
        }

        $offers = Offer::query()
            ->with(['customer.group', 'items'])
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->when($search !== '', function ($query) use ($search, $searchField, $exact) {
                $operator = $exact ? '=' : 'like';
                $value = $exact ? $search : "%{$search}%";

                $query->where(function ($subQuery) use ($searchField, $operator, $value) {
                    match ($searchField) {
                        'number' => $subQuery->where('offer_number', $operator, $value),
                        'customer' => $subQuery->whereHas('customer', fn ($customerQuery) => $customerQuery->where('company_name', $operator, $value)),
                        'customer_number' => $subQuery->whereHas('customer', fn ($customerQuery) => $customerQuery->where('customer_number', $operator, $value)),
                        'product' => $subQuery->whereHas('items', fn ($itemQuery) => $itemQuery->where('product_name', $operator, $value)),
                        default => $subQuery
                            ->where('offer_number', $operator, $value)
                            ->orWhereHas('customer', function ($customerQuery) use ($operator, $value) {
                                $customerQuery
                                    ->where('customer_number', $operator, $value)
                                    ->orWhere('company_name', $operator, $value);
                            })
                            ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('product_name', $operator, $value)),
                    };
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.warehouse-offers.index', [
            'offers' => $offers,
            'search' => $search,
            'searchField' => $searchField,
            'exact' => $exact,
            'selectedStatus' => $status,
            'statuses' => $this->statusLabels(),
        ]);
    }

    public function show(Offer $offer): View
    {
        $this->authorizeWarehouseOffer($offer);
        $offer->load(['customer.group', 'items.product', 'internalNotes.user']);

        return view('pages.warehouse-offers.show', [
            'offer' => $offer,
            'nextStatuses' => $this->nextStatuses($offer),
        ]);
    }

    public function updateStatus(Request $request, Offer $offer, OfferFulfillmentService $fulfillmentService): RedirectResponse
    {
        $this->authorizeWarehouseOffer($offer);
        $data = $request->validate(['status' => ['required', 'string']]);
        $newStatus = $data['status'];
        $allowedStatuses = array_keys($this->nextStatuses($offer));

        if (! in_array($newStatus, $allowedStatuses, true)) {
            return redirect()->route('warehouse.offers.show', $offer)->with('error', 'Dieser Statuswechsel ist für Lager nicht erlaubt.');
        }

        $oldStatus = $offer->status;

        try {
            if ($newStatus === Offer::STATUS_COMPLETED) {
                $fulfillmentService->complete($offer);
            } else {
                $offer->update(['status' => $newStatus]);
                ActivityLog::record('offer.status.updated', $offer, [
                    'offer_number' => $offer->offer_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'source' => 'warehouse',
                ]);
            }

            if ($newStatus === Offer::STATUS_OFFER) {
                $offer->update([
                    'reserved_until' => now()->addHours(ApplicationSetting::reservationHours()),
                ]);

                return redirect()
                    ->route('warehouse.offers.index')
                    ->with('success', 'Angebot wurde zurück an den Verkauf gegeben.');
            }

            return redirect()->route('warehouse.offers.show', $offer)->with('success', 'Status wurde geändert.');
        } catch (\Throwable $exception) {
            return redirect()->route('warehouse.offers.show', $offer)->with('error', $exception->getMessage());
        }
    }

    public function deliveryNote(Offer $offer): Response
    {
        $this->authorizeWarehouseOffer($offer);
        return app(OfferPdfController::class)->stream($offer, 'delivery-note');
    }

    private function authorizeWarehouseOffer(Offer $offer): void
    {
        abort_unless(in_array($offer->status, self::VISIBLE_STATUSES, true), 403, 'Dieses Angebot ist für Lager nicht freigegeben.');
    }

    private function nextStatuses(Offer $offer): array
    {
        return match ($offer->status) {
            Offer::STATUS_IN_PROGRESS => [
                Offer::STATUS_OFFER => Offer::STATUS_LABELS[Offer::STATUS_OFFER],
                Offer::STATUS_READY => Offer::STATUS_LABELS[Offer::STATUS_READY],
            ],
            Offer::STATUS_READY => [
                Offer::STATUS_IN_PROGRESS => Offer::STATUS_LABELS[Offer::STATUS_IN_PROGRESS],
                Offer::STATUS_COMPLETED => Offer::STATUS_LABELS[Offer::STATUS_COMPLETED],
            ],
            default => [],
        };
    }

    private function statusLabels(): array
    {
        return [
            Offer::STATUS_IN_PROGRESS => Offer::STATUS_LABELS[Offer::STATUS_IN_PROGRESS],
            Offer::STATUS_READY => Offer::STATUS_LABELS[Offer::STATUS_READY],
            Offer::STATUS_COMPLETED => Offer::STATUS_LABELS[Offer::STATUS_COMPLETED],
        ];
    }
}
