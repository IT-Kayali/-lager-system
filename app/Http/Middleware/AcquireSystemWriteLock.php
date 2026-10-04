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
         * Auch Restore-POSTs bleiben außerhalb des exklusiven
         * Restore-Fensters durch den Shared-Lock geschützt.
         */
        $this->writeLock
            ->acquireRequestShared();

        try {
            return $next($request);
        } finally {
            $this->writeLock
                ->releaseRequestShared();
        }
    }
}
