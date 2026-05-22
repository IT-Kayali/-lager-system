<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class ReservationReleaseService
{
    public function releaseExpired(): int
    {
        return DB::transaction(function (): int {
            $offers = Offer::query()
                ->whereIn('status', Offer::RESERVING_STATUSES)
                ->whereNotNull('reserved_until')
                ->where('reserved_until', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($offers as $offer) {
                $offer->update([
                    'status' => Offer::STATUS_RESERVATION_EXPIRED,
                    'reservation_released_at' => now(),
                ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'offer.reservation_expired',
                    'entity' => 'Offer',
                    'entity_id' => $offer->id,
                    'ip_address' => request()?->ip(),
                    'properties' => [
                        'offer_number' => $offer->offer_number,
                        'reserved_until' => $offer->reserved_until?->toDateTimeString(),
                    ],
                ]);
            }

            return $offers->count();
        });
    }
}
