<?php

namespace App\Services;

use RuntimeException;

class SystemWriteLock
{
    /**
     * @var resource|null
     */
    private $requestSharedHandle = null;

    /**
     * @return resource
     */
    public function acquireShared(
        bool $nonBlocking = false
    ) {
        return $this->acquire(
            LOCK_SH,
            $nonBlocking
        );
    }

    /**
     * @return resource
     */
    public function acquireExclusive(
        bool $nonBlocking = false
    ) {
        return $this->acquire(
            LOCK_EX,
            $nonBlocking
        );
    }

    public function acquireRequestShared(): void
    {
        if (is_resource($this->requestSharedHandle)) {
            throw new RuntimeException(
                'Die Request-Schreibsperre ist bereits aktiv.'
            );
        }

        $this->requestSharedHandle =
            $this->acquireShared();
    }

    public function suspendRequestShared(): bool
    {
        if (! is_resource($this->requestSharedHandle)) {
            return false;
        }

        if (! flock(
            $this->requestSharedHandle,
            LOCK_UN
        )) {
            throw new RuntimeException(
                'Die Request-Schreibsperre konnte nicht '
                .'vorübergehend freigegeben werden.'
            );
        }

        return true;
    }

    public function resumeRequestShared(): void
    {
        if (! is_resource($this->requestSharedHandle)) {
            throw new RuntimeException(
                'Die Request-Schreibsperre kann nicht '
                .'wiederhergestellt werden.'
            );
        }

        if (! flock(
            $this->requestSharedHandle,
            LOCK_SH
        )) {
            throw new RuntimeException(
                'Die Request-Schreibsperre konnte nicht '
                .'wiederhergestellt werden.'
            );
        }
    }

    public function releaseRequestShared(): void
    {
        $handle = $this->requestSharedHandle;
        $this->requestSharedHandle = null;
        $this->release($handle);
    }

    /**
     * @param  resource|null  $handle
     */
    public function release(
        $handle
    ): void {
        if (! is_resource($handle)) {
            return;
        }

        flock(
            $handle,
            LOCK_UN
        );

        fclose($handle);
    }

    /**
     * @return resource
     */
    private function acquire(
        int $lockType,
        bool $nonBlocking
    ) {
        $path = (string) config(
            'system-backup.write_lock_path',
            storage_path(
                'framework/system-write.lock'
            )
        );

        if ($path === '') {
            throw new RuntimeException(
                'Pfad der System-Schreibsperre fehlt.'
            );
        }

        $directory = dirname($path);

        if (! is_dir($directory)) {
            throw new RuntimeException(
                'Verzeichnis der System-Schreibsperre fehlt.'
            );
        }

        $previousUmask = umask(0007);

        try {
            $handle = @fopen(
                $path,
                'c+'
            );
        } finally {
            umask($previousUmask);
        }

        if ($handle === false) {
            throw new RuntimeException(
                'Die System-Schreibsperre konnte '
                .'nicht geöffnet werden.'
            );
        }

        $operation = $lockType;

        if ($nonBlocking) {
            $operation |= LOCK_NB;
        }

        if (! flock(
            $handle,
            $operation
        )) {
            fclose($handle);

            throw new RuntimeException(
                'Die System-Schreibsperre konnte '
                .'nicht erhalten werden.'
            );
        }

        return $handle;
    }
}
