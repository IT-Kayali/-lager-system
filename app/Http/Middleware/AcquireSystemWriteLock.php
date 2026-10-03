<?php

namespace App\Http\Middleware;

use App\Services\SystemWriteLock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcquireSystemWriteLock
{
    public function __construct(
        private SystemWriteLock $writeLock
    ) {}

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
         * Auch vermeintlich sichere GET-Requests können in dieser
         * Anwendung Datenbankänderungen auslösen, z. B. durch
         * ReservationReleaseService oder markAsRead().
         *
         * Deshalb schützt der Shared-Lock grundsätzlich den
         * vollständigen HTTP-Request.
         *
         * Der Restore selbst ist die einzige Ausnahme.
         */
        /*
         * Der Restore selbst darf keinen Shared-Lock halten,
         * weil SystemBackupService nach Aktivierung des
         * Wartungsmodus einen exklusiven Lock benötigt.
         */
        if (
            $request->isMethod('POST')
            && $request->routeIs(
                'settings.backups.restore'
            )
        ) {
            return $next($request);
        }

        $handle =
            $this->writeLock
                ->acquireShared();

        try {
            return $next($request);
        } finally {
            $this->writeLock
                ->release(
                    $handle
                );
        }
    }
}
