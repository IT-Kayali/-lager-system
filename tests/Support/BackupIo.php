<?php

namespace App\Services;

class BackupIo
{
    public static int|false|null $writeResult = null;

    public static bool $closeResult = true;

    public static ?\Closure $afterClose = null;

    public static int|false|null $manifestWriteResult = null;
}

function is_executable(string $path): bool
{
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

function file_put_contents(
    string $filename,
    mixed $data,
    int $flags = 0,
    $context = null
): int|false {
    if (
        BackupIo::$manifestWriteResult !== null
        && str_ends_with(
            $filename,
            '.sql.gz.json'
        )
    ) {
        return BackupIo::$manifestWriteResult;
    }

    if ($context !== null) {
        return \file_put_contents(
            $filename,
            $data,
            $flags,
            $context
        );
    }

    return \file_put_contents(
        $filename,
        $data,
        $flags
    );
}
