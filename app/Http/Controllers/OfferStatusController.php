<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Offer;
use App\Services\OfferFulfillmentService;
use App\Services\WarehouseNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferStatusController extends Controller
{
    public function update(
        Request $request,
        Offer $offer,
        OfferFulfillmentService $fulfillmentService,
        WarehouseNotificationService $warehouseNotifications
    ): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys($this->statuses()))],
        ]);

        $newStatus = $data['status'];
        $oldStatus = $offer->status;

        if (auth()->user()?->isSales()) {
            if ($oldStatus !== Offer::STATUS_OFFER || $newStatus !== Offer::STATUS_IN_PROGRESS) {
                return redirect()
                    ->route('offers.show', $offer)
                    ->with('error', 'Verkauf darf das Angebot nur von „Angebot“ auf „In Bearbeitung“ an das Lager übergeben.');
            }
        }

        if ($offer->isFinal()) {
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', 'Dieses Angebot ist bereits abgeschlossen und kann nicht mehr geändert werden.');
        }

        try {
            if ($newStatus === Offer::STATUS_COMPLETED) {
                $fulfillmentService->complete($offer);

                return redirect()
                    ->route('offers.show', $offer)
                    ->with('success', 'Angebot wurde erledigt. Der Gesamtbestand wurde nach FIFO abgezogen.');
            }

            if (in_array($newStatus, [Offer::STATUS_CANCELLED, Offer::STATUS_RESERVATION_EXPIRED], true)) {
                $fulfillmentService->cancel($offer, $newStatus);
                $warehouseNotifications->dismissOffer($offer);

                return redirect()
                    ->route('offers.show', $offer)
                    ->with('success', 'Status wurde geändert. Die Reservierung wurde freigegeben.');
            }

            $offer->update([
                'status' => $newStatus,
            ]);

            ActivityLog::record('offer.status.updated', $offer, [
                'offer_number' => $offer->offer_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            if ($newStatus === Offer::STATUS_IN_PROGRESS && $oldStatus !== Offer::STATUS_IN_PROGRESS) {
                $warehouseNotifications->notifyOfferHandoff($offer);
            } elseif ($newStatus === Offer::STATUS_OFFER && $oldStatus === Offer::STATUS_IN_PROGRESS) {
                $warehouseNotifications->dismissOffer($offer);
            }

            return redirect()
                ->route('offers.show', $offer)
                ->with('success', 'Status wurde geändert.');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('offers.show', $offer)
                ->with('error', 'Der Vorgang konnte nicht abgeschlossen werden. Bitte erneut versuchen oder einen Administrator kontaktieren.');
        }
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
