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
         * Reine Lesezugriffe benötigen keine Schreibsperre.
         */
        if ($request->isMethodSafe()) {
            return $next($request);
        }

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
