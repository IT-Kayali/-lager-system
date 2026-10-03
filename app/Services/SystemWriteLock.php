<?php

namespace App\Services;

use RuntimeException;

class SystemWriteLock
{
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

    /**
     * @param  resource  $handle
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
