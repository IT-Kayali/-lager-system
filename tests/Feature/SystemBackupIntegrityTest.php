<?php

use App\Models\ApplicationSetting;
use App\Services\BackupIo;
use App\Services\SystemBackupService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

require_once __DIR__.'/../Support/BackupIo.php';

beforeEach(function () {
    $directory = storage_path('framework/testing/backup-integrity');
    File::ensureDirectoryExists($directory);
    config(['system-backup.directory' => $directory]);

    // A separate in-memory MySQL-named connection supplies the migration set.
    config(['database.connections.mysql' => [
        ...config('database.connections.sqlite'),
        'database' => ':memory:',
        'username' => 'test',
    ]]);
    DB::purge('mysql');
    DB::connection('mysql')->getSchemaBuilder()->create('migrations', function ($table) {
        $table->string('migration');
    });
    DB::connection('mysql')->table('migrations')->insert(['migration' => 'test_schema']);

    $binary = $directory.'/dump'.(PHP_OS_FAMILY === 'Windows' ? '.bat' : '.sh');
    file_put_contents($binary, PHP_OS_FAMILY === 'Windows'
        ? "@echo off\r\necho -- Backup test\r\necho -- Dump completed on 2026-10-04\r\n"
        : "#!/bin/sh\nprintf '%s\\n' '-- Backup test' '-- Dump completed on 2026-10-04'\n");
    chmod($binary, 0700);
    config(['system-backup.dump_binary' => $binary]);
});

afterEach(function () {
    BackupIo::$writeResult = null;
    BackupIo::$closeResult = true;
    BackupIo::$afterClose = null;
    BackupIo::$manifestWriteResult = null;
    DB::purge('mysql');
    File::deleteDirectory(config('system-backup.directory'));
});

it('rejects failed zero and short gzip writes without publishing a backup', function (int|false $written) {
    BackupIo::$writeResult = $written;

    expect(fn () => app(SystemBackupService::class)->create(SystemBackupService::TYPE_MANUAL))
        ->toThrow(RuntimeException::class, 'Backup-Datei konnte nicht geschrieben werden.');

    expect(glob(config('system-backup.directory').'/*.sql.gz*'))->toBe([]);
})->with([false, 0, 1]);

it('rejects a gzip close failure without publishing a backup', function () {
    BackupIo::$closeResult = false;

    expect(fn () => app(SystemBackupService::class)->create(SystemBackupService::TYPE_MANUAL))
        ->toThrow(RuntimeException::class, 'konnte nicht abgeschlossen werden');

    expect(glob(config('system-backup.directory').'/*.sql.gz*'))->toBe([]);
});

it('checks the closed gzip before publishing a manifest', function () {
    BackupIo::$afterClose = function () {
        $path = glob(config('system-backup.directory').'/*.part')[0];
        $contents = file_get_contents($path);
        // Keep the compressed data but remove its CRC/size footer.
        file_put_contents($path, substr($contents, 0, -8));
    };

    expect(fn () => app(SystemBackupService::class)->create(SystemBackupService::TYPE_MANUAL))
        ->toThrow(RuntimeException::class, 'gzip-Integritätsprüfung');

    expect(glob(config('system-backup.directory').'/*.sql.gz*'))->toBe([]);
});

it('publishes a complete verified gzip with its schema fingerprint', function () {
    $service = app(SystemBackupService::class);
    $backup = $service->create(SystemBackupService::TYPE_MANUAL);
    $service->verifyBackup($backup);

    expect($backup['schema_fingerprint'])->toBe(hash('sha256', 'test_schema'));
    expect(is_file($service->backupPath($backup['filename']).'.json'))->toBeTrue();
});

it('does not restore when the safety backup receives a short write', function () {
    $service = app(SystemBackupService::class);
    $backup = $service->create(SystemBackupService::TYPE_MANUAL);
    BackupIo::$writeResult = 0;

    Artisan::shouldReceive('call')->with('down', ['--retry' => 60])->once()->andReturn(0);
    Artisan::shouldReceive('call')->with('up')->once()->andReturn(0);

    expect(fn () => $service->restore($backup['filename']))
        ->toThrow(RuntimeException::class, 'Die Datenbank wurde nicht verändert.');
    expect($service->listBackups())->toHaveCount(1);
});

it('blocks deletion and retention while a restore owns the operation lock', function () {
    $service = app(SystemBackupService::class);
    $backup = $service->create(SystemBackupService::TYPE_AUTOMATIC);
    ApplicationSetting::putValue('backup_automatic_retention', 1, 'integer');
    $older = $service->create(SystemBackupService::TYPE_AUTOMATIC);
    $acquire = new ReflectionMethod($service, 'acquireOperationLock');
    $release = new ReflectionMethod($service, 'releaseOperationLock');
    $handle = $acquire->invoke($service);

    try {
        expect(fn () => $service->delete($backup['filename']))->toThrow(RuntimeException::class, 'Es läuft bereits');
        expect(fn () => $service->pruneAutomaticBackups())->toThrow(RuntimeException::class, 'Es läuft bereits');
        expect(is_file($service->backupPath($backup['filename'])))->toBeTrue();
        expect(is_file($service->backupPath($older['filename'])))->toBeTrue();
    } finally {
        $release->invoke($service, $handle);
    }

    expect($service->delete($backup['filename']))->toBeTrue();
});

it('locks schema commands before any changes while backup or restore is running', function (string $command) {
    $service = app(SystemBackupService::class);
    $acquire = new ReflectionMethod($service, 'acquireOperationLock');
    $release = new ReflectionMethod($service, 'releaseOperationLock');
    $handle = $acquire->invoke($service);

    try {
        expect(fn () => Artisan::call($command, ['--force' => true]))
            ->toThrow(RuntimeException::class, 'Es läuft bereits');
    } finally {
        $release->invoke($service, $handle);
    }
})->with(['migrate', 'migrate:rollback', 'migrate:reset', 'migrate:refresh', 'migrate:fresh', 'migrate:install', 'db:wipe']);

it('allows nested migrations and releases the lock after failure', function () {
    $service = app(SystemBackupService::class);

    expect(fn () => $service->withMigrationLock(function () use ($service) {
        expect(fn () => $service->create(SystemBackupService::TYPE_AUTOMATIC))
            ->toThrow(RuntimeException::class, 'Es läuft bereits');
        expect(Artisan::call('migrate', ['--pretend' => true, '--force' => true]))->toBe(0);
        throw new RuntimeException('Migration failed');
    }))->toThrow(RuntimeException::class, 'Migration failed');

    expect($service->create(SystemBackupService::TYPE_MANUAL)['schema_fingerprint'])
        ->toBe(hash('sha256', 'test_schema'));
});

it('cannot change the schema between fingerprint capture and dump creation', function () {
    $service = app(SystemBackupService::class);
    $directory = config('system-backup.directory').'/migrations';
    File::ensureDirectoryExists($directory);
    file_put_contents($directory.'/2026_10_04_000000_backup_schema_test.php', <<<'PHP'
<?php
return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::create('backup_schema_test', function ($table) {
            $table->id();
        });
    }
};
PHP);
    // The migration repository requires a batch column for new records.
    DB::connection('mysql')->getSchemaBuilder()->table('migrations', function ($table) {
        $table->integer('batch')->default(1);
    });
    $arguments = ['--database' => 'mysql', '--path' => $directory, '--realpath' => true, '--force' => true];
    $acquire = new ReflectionMethod($service, 'acquireOperationLock');
    $release = new ReflectionMethod($service, 'releaseOperationLock');
    $fingerprint = new ReflectionMethod($service, 'currentSchemaFingerprint');
    $handle = $acquire->invoke($service);

    try {
        expect($fingerprint->invoke($service))->toBe(hash('sha256', 'test_schema'));
        expect(fn () => Artisan::call('migrate', $arguments))->toThrow(RuntimeException::class, 'Es läuft bereits');
        expect(DB::connection('mysql')->getSchemaBuilder()->hasTable('backup_schema_test'))->toBeFalse();
        expect($fingerprint->invoke($service))->toBe(hash('sha256', 'test_schema'));
    } finally {
        $release->invoke($service, $handle);
    }

    expect(Artisan::call('migrate', $arguments))->toBe(0);
    expect(DB::connection('mysql')->getSchemaBuilder()->hasTable('backup_schema_test'))->toBeTrue();
    $backup = $service->create(SystemBackupService::TYPE_AUTOMATIC);
    expect($backup['schema_fingerprint'])->toBe($fingerprint->invoke($service))
        ->not->toBe(hash('sha256', 'test_schema'));
});

it('rejects partial manifest writes and removes the published dump', function () {
    BackupIo::$manifestWriteResult = 1;

    expect(
        fn () => app(SystemBackupService::class)
            ->create(SystemBackupService::TYPE_MANUAL)
    )->toThrow(
        RuntimeException::class,
        'Backup-Metadaten konnten nicht vollständig gespeichert werden.'
    );

    expect(
        glob(
            config('system-backup.directory')
            .'/*.sql.gz*'
        )
    )->toBe([]);
});

it('preserves a maintenance mode that restore did not start', function () {
    $service = app(SystemBackupService::class);

    $backup = $service->create(
        SystemBackupService::TYPE_MANUAL
    );

    $maintenanceMode =
        app()->maintenanceMode();

    $existingPayload = [
        'time' => time(),
        'retry' => 321,
        'secret' => 'existing-maintenance-secret',
    ];

    $maintenanceMode->activate(
        $existingPayload
    );

    BackupIo::$writeResult = 0;

    Artisan::shouldReceive('call')
        ->never();

    try {
        expect(
            fn () => $service->restore(
                $backup['filename']
            )
        )->toThrow(
            RuntimeException::class,
            'Die Datenbank wurde nicht verändert.'
        );

        expect(
            $maintenanceMode->active()
        )->toBeTrue();

        expect(
            $maintenanceMode->data()
        )->toMatchArray(
            $existingPayload
        );
    } finally {
        $maintenanceMode->deactivate();
    }
});
