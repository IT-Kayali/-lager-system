<?php

namespace App\Console\Commands;

use App\Services\ReservationReleaseService;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'reservations:release-expired';

    protected $description = 'Release expired offer reservations and mark offers as reservation_expired';

    public function handle(ReservationReleaseService $service): int
    {
        $count = $service->releaseExpired();

        $this->info("Expired reservations released: {$count}");

        return self::SUCCESS;
    }
}
