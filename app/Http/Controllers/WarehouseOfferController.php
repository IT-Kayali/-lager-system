<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        if (! in_array($status, self::VISIBLE_STATUSES, true)) {
            $status = '';
        }

        $offers = Offer::query()
            ->with(['customer.group', 'items'])
            ->whereIn('status', self::VISIBLE_STATUSES)
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

        return view('pages.warehouse-offers.index', [
            'offers' => $offers,
            'search' => $search,
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
            Offer::STATUS_IN_PROGRESS => [Offer::STATUS_READY => Offer::STATUS_LABELS[Offer::STATUS_READY]],
            Offer::STATUS_READY => [Offer::STATUS_COMPLETED => Offer::STATUS_LABELS[Offer::STATUS_COMPLETED]],
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
