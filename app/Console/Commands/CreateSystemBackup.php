<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ApplicationSetting;
use App\Services\SystemBackupService;
use Illuminate\Console\Command;
use Throwable;

class CreateSystemBackup extends Command
{
    protected $signature = 'system:backup
        {--automatic : Nur ausführen, wenn ein automatisches Backup fällig ist}
        {--force : Automatische Fälligkeitsprüfung überspringen}';

    protected $description =
        'Erstellt ein vollständiges MySQL-Datenbank-Backup';

    public function handle(
        SystemBackupService $backupService
    ): int {
        $automatic = (bool) $this->option(
            'automatic'
        );

        if ($automatic) {
            if (
                ! ApplicationSetting::backupAutomaticEnabled()
            ) {
                $this->components->info(
                    'Automatische Backups sind deaktiviert.'
                );

                return self::SUCCESS;
            }

            if (
                ! $this->option('force')
                && ! $backupService
                    ->automaticBackupIsDue()
            ) {
                $this->components->info(
                    'Noch kein automatisches Backup fällig.'
                );

                return self::SUCCESS;
            }
        }

        try {
            $backup = $backupService->create(
                $automatic
                    ? SystemBackupService::TYPE_AUTOMATIC
                    : SystemBackupService::TYPE_MANUAL
            );

            $removed = 0;

            if ($automatic) {
                $removed = $backupService
                    ->pruneAutomaticBackups();
            }

            ActivityLog::query()->create([
                'user_id' => null,
                'action' => 'system.backup_created',
                'entity' => null,
                'entity_id' => null,
                'ip_address' => null,
                'properties' => [
                    'filename' => $backup['filename'],
                    'type' => $backup['type'],
                    'size' => $backup['size'],
                    'sha256' => $backup['sha256'],
                    'pruned_automatic_backups' => $removed,
                ],
            ]);

            $this->components->info(
                'Backup erstellt: '
                .$backup['filename']
            );

            if ($removed > 0) {
                $this->components->info(
                    $removed
                    .' alte automatische Backups entfernt.'
                );
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
