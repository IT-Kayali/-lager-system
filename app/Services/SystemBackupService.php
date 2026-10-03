<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class SystemBackupService
{
    public const TYPE_MANUAL = 'manual';

    public const TYPE_AUTOMATIC = 'automatic';

    public const TYPE_PRE_RESTORE = 'pre_restore';

    private const FILE_PATTERN =
        '/\Alager-(manual|automatic|pre_restore)-\d{8}-\d{6}-[a-f0-9]{8}\.sql\.gz\z/';

    /**
     * @return array<string, mixed>
     */
    public function create(
        string $type,
        ?int $userId = null,
        ?string $ipAddress = null
    ): array {
        if (! in_array($type, [
            self::TYPE_MANUAL,
            self::TYPE_AUTOMATIC,
        ], true)) {
            throw new RuntimeException(
                'Ungültiger Backup-Typ.'
            );
        }

        $lock = Cache::store('file')->lock(
            'system-backup:operation',
            1800
        );

        if (! $lock->get()) {
            throw new RuntimeException(
                'Es läuft bereits eine Backup- oder Wiederherstellungsaktion.'
            );
        }

        try {
            return $this->createLocked(
                $type,
                $userId,
                $ipAddress
            );
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function restore(
        string $filename,
        ?int $userId = null,
        ?string $ipAddress = null
    ): array {
        $lock = Cache::store('file')->lock(
            'system-backup:operation',
            1800
        );

        if (! $lock->get()) {
            throw new RuntimeException(
                'Es läuft bereits eine Backup- oder Wiederherstellungsaktion.'
            );
        }

        try {
            $target = $this->findBackup($filename);

            $this->verifyBackup($target);

            $safetyBackup = $this->createLocked(
                self::TYPE_PRE_RESTORE,
                $userId,
                $ipAddress
            );

            $maintenanceStarted = false;
            $leaveMaintenanceMode = false;
            $maintenanceUpFailed = false;

            $downExitCode = Artisan::call(
                'down',
                [
                    '--retry' => 60,
                ]
            );

            if ($downExitCode !== 0) {
                throw new RuntimeException(
                    'Der Wartungsmodus konnte nicht aktiviert werden. '
                    .'Die Datenbank wurde nicht verändert.'
                );
            }

            $maintenanceStarted = true;

            try {
                $this->runRestore(
                    $this->backupPath(
                        $target['filename']
                    )
                );

                $this->verifyMysqlConnectivity();
            } catch (Throwable $restoreException) {
                try {
                    $this->runRestore(
                        $this->backupPath(
                            $safetyBackup['filename']
                        )
                    );

                    $this->verifyMysqlConnectivity();
                } catch (Throwable $rollbackException) {
                    $leaveMaintenanceMode = true;

                    throw new RuntimeException(
                        'Wiederherstellung fehlgeschlagen und auch die '
                        .'automatische Rücksicherung des Sicherheitsbackups '
                        .'ist fehlgeschlagen. Das System bleibt aus '
                        .'Sicherheitsgründen im Wartungsmodus. '
                        .'Restore-Fehler: '
                        .$restoreException->getMessage()
                        .' | Rücksicherungsfehler: '
                        .$rollbackException->getMessage(),
                        0,
                        $restoreException
                    );
                }

                throw new RuntimeException(
                    'Das gewünschte Backup konnte nicht wiederhergestellt '
                    .'werden. Das automatisch erzeugte Sicherheitsbackup '
                    .'wurde erfolgreich zurückgespielt. Ursache: '
                    .$restoreException->getMessage(),
                    0,
                    $restoreException
                );
            } finally {
                if (
                    $maintenanceStarted
                    && ! $leaveMaintenanceMode
                ) {
                    $maintenanceUpFailed =
                        Artisan::call('up') !== 0;
                }
            }

            if ($maintenanceUpFailed) {
                throw new RuntimeException(
                    'Die Datenbank wurde wiederhergestellt, aber der '
                    .'Wartungsmodus konnte nicht automatisch beendet werden.'
                );
            }

            return [
                'restored' => $target,
                'safety_backup' => $safetyBackup,
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listBackups(): array
    {
        $directory = $this->backupDirectory();

        $manifestFiles = glob(
            $directory
            .DIRECTORY_SEPARATOR
            .'lager-*.sql.gz.json'
        ) ?: [];

        $backups = [];

        foreach ($manifestFiles as $manifestPath) {
            $manifest = $this->readManifest(
                $manifestPath
            );

            if ($manifest === null) {
                continue;
            }

            $filename = basename(
                (string) ($manifest['filename'] ?? '')
            );

            if (! $this->isValidFilename($filename)) {
                continue;
            }

            $backupPath = $directory
                .DIRECTORY_SEPARATOR
                .$filename;

            if (! is_file($backupPath)) {
                continue;
            }

            $type = (string) (
                $manifest['type']
                ?? self::TYPE_MANUAL
            );

            if (! in_array($type, [
                self::TYPE_MANUAL,
                self::TYPE_AUTOMATIC,
                self::TYPE_PRE_RESTORE,
            ], true)) {
                $type = self::TYPE_MANUAL;
            }

            $size = filesize($backupPath) ?: 0;

            $backups[] = [
                'filename' => $filename,
                'type' => $type,
                'size' => $size,
                'size_formatted' => $this->formatBytes(
                    $size
                ),
                'sha256' => (string) (
                    $manifest['sha256']
                    ?? hash_file(
                        'sha256',
                        $backupPath
                    )
                ),
                'created_at' => (string) (
                    $manifest['created_at']
                    ?? date(
                        DATE_ATOM,
                        filemtime($backupPath)
                    )
                ),
                'created_by_user_id' => $manifest['created_by_user_id']
                    ?? null,
                'ip_address' => $manifest['ip_address']
                    ?? null,
            ];
        }

        usort(
            $backups,
            static fn (array $left, array $right): int => strcmp(
                $right['created_at'],
                $left['created_at']
            )
        );

        return $backups;
    }

    /**
     * @return array<string, mixed>
     */
    public function findBackup(
        string $filename
    ): array {
        $filename = basename($filename);

        if (! $this->isValidFilename($filename)) {
            throw new RuntimeException(
                'Ungültiger Backup-Dateiname.'
            );
        }

        foreach ($this->listBackups() as $backup) {
            if (
                $backup['filename']
                === $filename
            ) {
                return $backup;
            }
        }

        throw new RuntimeException(
            'Backup wurde nicht gefunden.'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestAutomaticBackup(): ?array
    {
        foreach ($this->listBackups() as $backup) {
            if (
                $backup['type']
                === self::TYPE_AUTOMATIC
            ) {
                return $backup;
            }
        }

        return null;
    }

    public function automaticBackupIsDue(): bool
    {
        if (! ApplicationSetting::backupAutomaticEnabled()) {
            return false;
        }

        $latest = $this->latestAutomaticBackup();

        if ($latest === null) {
            return true;
        }

        try {
            $createdAt = CarbonImmutable::parse(
                $latest['created_at']
            );
        } catch (Throwable) {
            return true;
        }

        return $createdAt
            ->addHours(
                ApplicationSetting::backupAutomaticIntervalHours()
            )
            ->isPast();
    }

    public function pruneAutomaticBackups(): int
    {
        $keep = ApplicationSetting::backupAutomaticRetention();

        $automatic = array_values(
            array_filter(
                $this->listBackups(),
                static fn (array $backup): bool => $backup['type']
                    === self::TYPE_AUTOMATIC
            )
        );

        if (count($automatic) <= $keep) {
            return 0;
        }

        $deleted = 0;

        foreach (
            array_slice($automatic, $keep) as $backup
        ) {
            if ($this->delete($backup['filename'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function backupPath(
        string $filename
    ): string {
        $filename = basename($filename);

        if (! $this->isValidFilename($filename)) {
            throw new RuntimeException(
                'Ungültiger Backup-Dateiname.'
            );
        }

        $path = $this->backupDirectory()
            .DIRECTORY_SEPARATOR
            .$filename;

        if (! is_file($path)) {
            throw new RuntimeException(
                'Backup wurde nicht gefunden.'
            );
        }

        return $path;
    }

    public function delete(
        string $filename
    ): bool {
        $path = $this->backupPath($filename);

        $manifest = $path.'.json';

        if (! unlink($path)) {
            throw new RuntimeException(
                'Backup-Datei konnte nicht gelöscht werden.'
            );
        }

        if (
            is_file($manifest)
            && ! @unlink($manifest)
        ) {
            report(
                new RuntimeException(
                    'Backup wurde gelöscht, aber die Manifest-Datei konnte nicht entfernt werden: '
                    .basename($manifest)
                )
            );
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $backup
     */
    public function verifyBackup(
        array $backup
    ): void {
        $filename = (string) (
            $backup['filename']
            ?? ''
        );

        $path = $this->backupPath(
            $filename
        );

        $expectedSha = trim(
            (string) (
                $backup['sha256']
                ?? ''
            )
        );

        $actualSha = hash_file(
            'sha256',
            $path
        );

        if (
            $expectedSha === ''
            || ! hash_equals(
                $expectedSha,
                $actualSha
            )
        ) {
            throw new RuntimeException(
                'SHA256-Prüfung des Backups ist fehlgeschlagen.'
            );
        }

        $gzipBinary = (string) config(
            'system-backup.gzip_binary',
            '/usr/bin/gzip'
        );

        if (
            $gzipBinary === ''
            || ! is_executable($gzipBinary)
        ) {
            throw new RuntimeException(
                'gzip ist nicht verfügbar.'
            );
        }

        $gzipTest = new Process([
            $gzipBinary,
            '-t',
            $path,
        ]);

        $gzipTest->setTimeout(
            max(
                60,
                (int) config(
                    'system-backup.timeout',
                    600
                )
            )
        );

        $gzipTest->run();

        if (! $gzipTest->isSuccessful()) {
            throw new RuntimeException(
                'Die gzip-Integritätsprüfung des Backups ist fehlgeschlagen.'
            );
        }

        $stream = gzopen(
            $path,
            'rb'
        );

        if ($stream === false) {
            throw new RuntimeException(
                'Backup konnte nicht gelesen werden.'
            );
        }

        $tail = '';

        try {
            while (! gzeof($stream)) {
                $chunk = gzread(
                    $stream,
                    65536
                );

                if ($chunk === false) {
                    throw new RuntimeException(
                        'Backup konnte nicht vollständig gelesen werden.'
                    );
                }

                $tail = substr(
                    $tail.$chunk,
                    -131072
                );
            }
        } finally {
            gzclose($stream);
        }

        if (
            ! str_contains(
                $tail,
                'Dump completed on'
            )
        ) {
            throw new RuntimeException(
                'Die Dump-Endemarkierung fehlt. '
                .'Das Backup wird nicht wiederhergestellt.'
            );
        }
    }

    private function isValidFilename(
        string $filename
    ): bool {
        return preg_match(
            self::FILE_PATTERN,
            $filename
        ) === 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function createLocked(
        string $type,
        ?int $userId,
        ?string $ipAddress
    ): array {
        if (! in_array($type, [
            self::TYPE_MANUAL,
            self::TYPE_AUTOMATIC,
            self::TYPE_PRE_RESTORE,
        ], true)) {
            throw new RuntimeException(
                'Ungültiger Backup-Typ.'
            );
        }

        $connection = config(
            'database.connections.mysql'
        );

        if (! is_array($connection)) {
            throw new RuntimeException(
                'MySQL-Konfiguration fehlt.'
            );
        }

        $database = trim(
            (string) ($connection['database'] ?? '')
        );

        $username = trim(
            (string) ($connection['username'] ?? '')
        );

        if (
            $database === ''
            || $username === ''
        ) {
            throw new RuntimeException(
                'MySQL-Konfiguration ist unvollständig.'
            );
        }

        $directory = $this->backupDirectory();

        $timestamp = now()->format(
            'Ymd-His'
        );

        $random = bin2hex(
            random_bytes(4)
        );

        $filename = sprintf(
            'lager-%s-%s-%s.sql.gz',
            $type,
            $timestamp,
            $random
        );

        $finalPath = $directory
            .DIRECTORY_SEPARATOR
            .$filename;

        $temporaryPath = $finalPath
            .'.part';

        $credentialsFile = $this
            ->createCredentialsFile(
                $connection
            );

        try {
            $this->runDump(
                $database,
                $credentialsFile,
                $temporaryPath
            );

            if (
                ! is_file($temporaryPath)
                || filesize($temporaryPath) === 0
            ) {
                throw new RuntimeException(
                    'Das erzeugte Backup ist leer.'
                );
            }

            if (! rename(
                $temporaryPath,
                $finalPath
            )) {
                throw new RuntimeException(
                    'Backup-Datei konnte nicht finalisiert werden.'
                );
            }

            chmod(
                $finalPath,
                0640
            );

            $manifest = [
                'filename' => $filename,
                'type' => $type,
                'created_at' => now()->toIso8601String(),
                'size' => filesize($finalPath) ?: 0,
                'sha256' => hash_file(
                    'sha256',
                    $finalPath
                ),
                'created_by_user_id' => $userId,
                'ip_address' => $ipAddress,
                'database' => $database,
            ];

            $manifestPath = $finalPath
                .'.json';

            $written = file_put_contents(
                $manifestPath,
                json_encode(
                    $manifest,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                )
                .PHP_EOL
            );

            if ($written === false) {
                @unlink($finalPath);

                throw new RuntimeException(
                    'Backup-Metadaten konnten nicht gespeichert werden.'
                );
            }

            chmod(
                $manifestPath,
                0640
            );

            return [
                ...$manifest,
                'size_formatted' => $this->formatBytes(
                    (int) $manifest['size']
                ),
            ];
        } finally {
            @unlink($credentialsFile);
            @unlink($temporaryPath);
        }
    }

    private function runDump(
        string $database,
        string $credentialsFile,
        string $outputPath
    ): void {
        $binary = (string) config(
            'system-backup.dump_binary',
            '/usr/bin/mysqldump'
        );

        if (
            $binary === ''
            || ! is_executable($binary)
        ) {
            throw new RuntimeException(
                'mysqldump ist nicht verfügbar.'
            );
        }

        $process = new Process(
            [
                $binary,
                '--defaults-extra-file='
                    .$credentialsFile,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--events',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                '--no-tablespaces',
                $database,
            ],
            base_path()
        );

        $process->setTimeout(
            max(
                60,
                (int) config(
                    'system-backup.timeout',
                    600
                )
            )
        );

        $gzip = gzopen(
            $outputPath,
            'wb9'
        );

        if ($gzip === false) {
            throw new RuntimeException(
                'Komprimierte Backup-Datei konnte nicht geöffnet werden.'
            );
        }

        $stderr = '';

        try {
            $exitCode = $process->run(
                static function (
                    string $type,
                    string $buffer
                ) use (
                    $gzip,
                    &$stderr
                ): void {
                    if ($type === Process::OUT) {
                        if (
                            gzwrite(
                                $gzip,
                                $buffer
                            ) === false
                        ) {
                            throw new RuntimeException(
                                'Backup-Datei konnte nicht geschrieben werden.'
                            );
                        }

                        return;
                    }

                    $stderr .= $buffer;
                }
            );
        } finally {
            gzclose($gzip);
        }

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'mysqldump ist fehlgeschlagen: '
                .trim($stderr)
            );
        }
    }

    private function runRestore(
        string $backupPath
    ): void {
        $connection = config(
            'database.connections.mysql'
        );

        if (! is_array($connection)) {
            throw new RuntimeException(
                'MySQL-Konfiguration fehlt.'
            );
        }

        $database = trim(
            (string) ($connection['database'] ?? '')
        );

        if ($database === '') {
            throw new RuntimeException(
                'MySQL-Datenbankname fehlt.'
            );
        }

        $binary = (string) config(
            'system-backup.mysql_binary',
            '/usr/bin/mysql'
        );

        if (
            $binary === ''
            || ! is_executable($binary)
        ) {
            throw new RuntimeException(
                'mysql-Client ist nicht verfügbar.'
            );
        }

        $credentialsFile = $this
            ->createCredentialsFile(
                $connection
            );

        $stream = gzopen(
            $backupPath,
            'rb'
        );

        if ($stream === false) {
            @unlink($credentialsFile);

            throw new RuntimeException(
                'Backup konnte für die Wiederherstellung nicht geöffnet werden.'
            );
        }

        try {
            $process = new Process(
                [
                    $binary,
                    '--defaults-extra-file='
                        .$credentialsFile,
                    $database,
                ],
                base_path()
            );

            $process->setTimeout(
                max(
                    60,
                    (int) config(
                        'system-backup.timeout',
                        600
                    )
                )
            );

            $process->setInput(
                $stream
            );

            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    'mysql-Wiederherstellung ist fehlgeschlagen: '
                    .trim(
                        $process->getErrorOutput()
                    )
                );
            }
        } finally {
            gzclose($stream);
            @unlink($credentialsFile);
        }
    }

    private function verifyMysqlConnectivity(): void
    {
        $connection = config(
            'database.connections.mysql'
        );

        if (! is_array($connection)) {
            throw new RuntimeException(
                'MySQL-Konfiguration fehlt.'
            );
        }

        $database = trim(
            (string) ($connection['database'] ?? '')
        );

        $binary = (string) config(
            'system-backup.mysql_binary',
            '/usr/bin/mysql'
        );

        $credentialsFile = $this
            ->createCredentialsFile(
                $connection
            );

        try {
            $process = new Process(
                [
                    $binary,
                    '--defaults-extra-file='
                        .$credentialsFile,
                    '--batch',
                    '--skip-column-names',
                    '--execute=SELECT 1',
                    $database,
                ],
                base_path()
            );

            $process->setTimeout(60);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    'MySQL ist nach der Wiederherstellung nicht erreichbar: '
                    .trim(
                        $process->getErrorOutput()
                    )
                );
            }
        } finally {
            @unlink($credentialsFile);
        }
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function createCredentialsFile(
        array $connection
    ): string {
        $credentialsFile = tempnam(
            sys_get_temp_dir(),
            'lager-backup-'
        );

        if ($credentialsFile === false) {
            throw new RuntimeException(
                'Temporäre MySQL-Zugangdatei konnte nicht erstellt werden.'
            );
        }

        chmod(
            $credentialsFile,
            0600
        );

        $written = file_put_contents(
            $credentialsFile,
            $this->mysqlCredentials(
                $connection
            )
        );

        if ($written === false) {
            @unlink($credentialsFile);

            throw new RuntimeException(
                'Temporäre MySQL-Zugangdatei konnte nicht geschrieben werden.'
            );
        }

        return $credentialsFile;
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function mysqlCredentials(
        array $connection
    ): string {
        $lines = [
            '[client]',
            'user='.$this->quoteOption(
                (string) (
                    $connection['username']
                    ?? ''
                )
            ),
            'password='.$this->quoteOption(
                (string) (
                    $connection['password']
                    ?? ''
                )
            ),
        ];

        $socket = trim(
            (string) (
                $connection['unix_socket']
                ?? ''
            )
        );

        if ($socket !== '') {
            $lines[] = 'socket='
                .$this->quoteOption(
                    $socket
                );
        } else {
            $lines[] = 'host='
                .$this->quoteOption(
                    (string) (
                        $connection['host']
                        ?? '127.0.0.1'
                    )
                );

            $lines[] = 'port='
                .(int) (
                    $connection['port']
                    ?? 3306
                );
        }

        return implode(
            PHP_EOL,
            $lines
        ).PHP_EOL;
    }

    private function quoteOption(
        string $value
    ): string {
        return '"'
            .str_replace(
                [
                    '\\',
                    '"',
                    "\n",
                    "\r",
                ],
                [
                    '\\\\',
                    '\\"',
                    '\\n',
                    '\\r',
                ],
                $value
            )
            .'"';
    }

    private function backupDirectory(): string
    {
        $directory = (string) config(
            'system-backup.directory',
            storage_path(
                'app/private/system-backups'
            )
        );

        if (! is_dir($directory)) {
            if (
                ! mkdir(
                    $directory,
                    0770,
                    true
                )
                && ! is_dir($directory)
            ) {
                throw new RuntimeException(
                    'Backup-Verzeichnis konnte nicht erstellt werden.'
                );
            }
        }

        if (! is_writable($directory)) {
            throw new RuntimeException(
                'Backup-Verzeichnis ist nicht beschreibbar.'
            );
        }

        return $directory;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readManifest(
        string $path
    ): ?array {
        $json = @file_get_contents($path);

        if ($json === false) {
            return null;
        }

        $decoded = json_decode(
            $json,
            true
        );

        return is_array($decoded)
            ? $decoded
            : null;
    }

    private function formatBytes(
        int $bytes
    ): string {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format(
                $bytes / 1024,
                1,
                ',',
                '.'
            ).' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format(
                $bytes / 1024 / 1024,
                1,
                ',',
                '.'
            ).' MB';
        }

        return number_format(
            $bytes / 1024 / 1024 / 1024,
            2,
            ',',
            '.'
        ).' GB';
    }
}
