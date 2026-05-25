<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Offer;
use App\Services\OfferFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferStatusController extends Controller
{
    public function update(Request $request, Offer $offer, OfferFulfillmentService $fulfillmentService): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys($this->statuses()))],
        ]);

        $newStatus = $data['status'];
        $oldStatus = $offer->status;

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

            return redirect()
                ->route('offers.show', $offer)
                ->with('success', 'Status wurde geändert.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('offers.show', $offer)
                ->with('error', $exception->getMessage());
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
