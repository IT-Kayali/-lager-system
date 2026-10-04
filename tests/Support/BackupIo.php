<?php

namespace App\Services;

class BackupIo
{
    public static int|false|null $writeResult = null;

    public static bool $closeResult = true;

    public static ?\Closure $afterClose = null;
}

function is_executable(string $path): bool
{
    // PHP's Windows check excludes .bat, although Symfony Process can run it.
    return \is_executable($path)
        || (PHP_OS_FAMILY === 'Windows' && basename($path) === 'dump.bat' && is_file($path));
}

function gzwrite($stream, string $buffer): int|false
{
    if (BackupIo::$writeResult !== null) {
        return BackupIo::$writeResult;
    }

    return \gzwrite($stream, $buffer);
}

function gzclose($stream): bool
{
    $result = \gzclose($stream);
    $callback = BackupIo::$afterClose;
    BackupIo::$afterClose = null;
    $callback?->__invoke();

    return $result && BackupIo::$closeResult;
}
