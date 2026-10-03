<?php

use App\Models\ActivityLog;
use App\Models\ApplicationSetting;
use App\Models\User;
use App\Services\SystemBackupService;
use App\Services\SystemWriteLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ViewErrorBag;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $directory = storage_path(
        'framework/testing/system-backups'
    );

    File::deleteDirectory($directory);
    File::ensureDirectoryExists($directory);

    config([
        'system-backup.directory' => $directory,
        'system-backup.write_lock_path' => storage_path(
            'framework/testing/system-write.lock'
        ),
    ]);

    File::delete(
        storage_path(
            'framework/testing/system-write.lock'
        )
    );
});

afterEach(function () {
    File::deleteDirectory(
        storage_path(
            'framework/testing/system-backups'
        )
    );

    File::delete(
        storage_path(
            'framework/testing/system-write.lock'
        )
    );
});

it('shows backup management only to admins', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Datenbank-Backups')
        ->assertSee('Backup jetzt erstellen');

    $this
        ->actingAs($manager)
        ->get(route('settings.index'))
        ->assertForbidden();
});

it('prevents non admins from creating a manual backup', function () {
    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $this
        ->actingAs($manager)
        ->post(route('settings.backups.store'))
        ->assertForbidden();
});

it('allows admins to save automatic backup settings', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(
            route('settings.backups.settings'),
            [
                'backup_automatic_enabled' => '1',
                'backup_automatic_interval' => '12_hours',
                'backup_automatic_retention' => 21,
            ]
        );

    $response
        ->assertRedirect(
            route('settings.index')
            .'#system-backups'
        )
        ->assertSessionHas('success');

    expect(
        ApplicationSetting::backupAutomaticEnabled()
    )->toBeTrue();

    expect(
        ApplicationSetting::backupAutomaticInterval()
    )->toBe('12_hours');

    expect(
        ApplicationSetting::backupAutomaticRetention()
    )->toBe(21);
});

it('creates a manual backup through the admin controller', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->mock(
        SystemBackupService::class,
        function (MockInterface $mock) use ($admin): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->with(
                    SystemBackupService::TYPE_MANUAL,
                    $admin->id,
                    Mockery::type('string')
                )
                ->andReturn([
                    'filename' => 'lager-manual-20261003-130000-abcdef12.sql.gz',
                    'type' => SystemBackupService::TYPE_MANUAL,
                    'size' => 12345,
                    'size_formatted' => '12,1 KB',
                    'sha256' => str_repeat('a', 64),
                    'created_at' => now()->toIso8601String(),
                ]);
        }
    );

    $response = $this
        ->actingAs($admin)
        ->post(route('settings.backups.store'));

    $response
        ->assertRedirect(
            route('settings.index')
            .'#system-backups'
        )
        ->assertSessionHas('success');

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'user_id' => $admin->id,
            'action' => 'system.backup_created',
        ]
    );
});

it('prunes only old automatic backups', function () {
    ApplicationSetting::putValue(
        'backup_automatic_retention',
        1,
        'integer'
    );

    $directory = config(
        'system-backup.directory'
    );

    $files = [
        [
            'filename' => 'lager-automatic-20261003-090000-aaaaaaaa.sql.gz',
            'type' => 'automatic',
            'created_at' => '2026-10-03T09:00:00+02:00',
        ],
        [
            'filename' => 'lager-automatic-20261003-120000-bbbbbbbb.sql.gz',
            'type' => 'automatic',
            'created_at' => '2026-10-03T12:00:00+02:00',
        ],
        [
            'filename' => 'lager-manual-20261003-080000-cccccccc.sql.gz',
            'type' => 'manual',
            'created_at' => '2026-10-03T08:00:00+02:00',
        ],
    ];

    foreach ($files as $file) {
        $path = $directory
            .DIRECTORY_SEPARATOR
            .$file['filename'];

        file_put_contents(
            $path,
            'test-backup'
        );

        file_put_contents(
            $path.'.json',
            json_encode([
                ...$file,
                'size' => filesize($path),
                'sha256' => hash_file('sha256', $path),
            ])
        );
    }

    $service = app(
        SystemBackupService::class
    );

    expect(
        $service->pruneAutomaticBackups()
    )->toBe(1);

    expect(
        file_exists(
            $directory
            .'/lager-automatic-20261003-090000-aaaaaaaa.sql.gz'
        )
    )->toBeFalse();

    expect(
        file_exists(
            $directory
            .'/lager-automatic-20261003-120000-bbbbbbbb.sql.gz'
        )
    )->toBeTrue();

    expect(
        file_exists(
            $directory
            .'/lager-manual-20261003-080000-cccccccc.sql.gz'
        )
    )->toBeTrue();
});

it('prevents non admins from restoring a backup', function () {
    $manager = User::factory()->create([
        'role' => User::ROLE_MANAGER,
        'is_active' => true,
    ]);

    $filename =
        'lager-manual-20261003-130000-abcdef12.sql.gz';

    $this
        ->actingAs($manager)
        ->post(
            route(
                'settings.backups.restore',
                ['filename' => $filename]
            ),
            [
                'current_password' => 'password',
                'confirmation' => 'BACKUP WIEDERHERSTELLEN',
                'acknowledge' => '1',
            ]
        )
        ->assertForbidden();
});

it('requires the exact restore confirmation phrase', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $filename =
        'lager-manual-20261003-130000-abcdef12.sql.gz';

    $this->mock(
        SystemBackupService::class,
        function (MockInterface $mock): void {
            $mock
                ->shouldNotReceive('restore');
        }
    );

    $this
        ->actingAs($admin)
        ->post(
            route(
                'settings.backups.restore',
                ['filename' => $filename]
            ),
            [
                'current_password' => 'password',
                'confirmation' => 'RESTORE',
                'acknowledge' => '1',
            ]
        )
        ->assertSessionHasErrors(
            'confirmation'
        );
});

it('allows an admin to restore a backup after confirmation', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $filename =
        'lager-manual-20261003-130000-abcdef12.sql.gz';

    $restoredSha = str_repeat(
        'a',
        64
    );

    $safetySha = str_repeat(
        'b',
        64
    );

    $this->mock(
        SystemBackupService::class,
        function (
            MockInterface $mock
        ) use (
            $admin,
            $filename,
            $restoredSha,
            $safetySha
        ): void {
            $mock
                ->shouldReceive('restore')
                ->once()
                ->with(
                    $filename,
                    $admin->id,
                    Mockery::type('string')
                )
                ->andReturn([
                    'restored' => [
                        'filename' => $filename,
                        'sha256' => $restoredSha,
                    ],
                    'safety_backup' => [
                        'filename' => 'lager-pre_restore-20261003-140000-12345678.sql.gz',
                        'sha256' => $safetySha,
                    ],
                ]);
        }
    );

    $response = $this
        ->actingAs($admin)
        ->post(
            route(
                'settings.backups.restore',
                ['filename' => $filename]
            ),
            [
                'current_password' => 'password',
                'confirmation' => 'BACKUP WIEDERHERSTELLEN',
                'acknowledge' => '1',
            ]
        );

    $response
        ->assertRedirect(
            route('login')
        );

    $this->assertGuest();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'action' => 'system.backup_restored',
        ]
    );
});

it('separates regular backups from pre restore safety backups', function () {
    $html = view(
        'pages.settings._system-backups',
        [
            'errors' => new ViewErrorBag,
            'backupAutomaticEnabled' => true,
            'backupAutomaticInterval' => 'daily',
            'backupAutomaticRetention' => 14,
            'systemBackups' => [
                [
                    'filename' => 'lager-manual-20261003-163145-615e2924.sql.gz',
                    'type' => 'manual',
                    'created_at' => '2026-10-03T16:31:46+02:00',
                    'size_formatted' => '6,3 KB',
                    'sha256' => str_repeat('a', 64),
                ],
                [
                    'filename' => 'lager-pre_restore-20261003-163720-9585ebd7.sql.gz',
                    'type' => 'pre_restore',
                    'created_at' => '2026-10-03T16:37:20+02:00',
                    'size_formatted' => '6,2 KB',
                    'sha256' => str_repeat('b', 64),
                ],
            ],
        ]
    )->render();

    expect($html)
        ->toContain('Reguläre Backups')
        ->toContain(
            'Sicherheitskopien vor Wiederherstellung'
        )
        ->toContain(
            'lager-manual-20261003-163145-615e2924.sql.gz'
        )
        ->toContain(
            'lager-pre_restore-20261003-163720-9585ebd7.sql.gz'
        )
        ->toContain(
            'Sicherheitskopie zurückspielen'
        );
});

it('does not expose backup engine errors to the browser', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->mock(
        SystemBackupService::class,
        function (MockInterface $mock): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->andThrow(
                    new RuntimeException(
                        'SECRET MYSQL INTERNAL DETAIL'
                    )
                );
        }
    );

    $response = $this
        ->actingAs($admin)
        ->post(
            route('settings.backups.store')
        );

    $response->assertSessionHas(
        'error',
        'Backup konnte nicht erstellt werden. Bitte Serverprotokoll prüfen.'
    );

    expect(
        (string) session('error')
    )->not->toContain(
        'SECRET MYSQL INTERNAL DETAIL'
    );
});

it('does not expose restore engine errors to the browser', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $filename =
        'lager-manual-20261003-163145-615e2924.sql.gz';

    $this->mock(
        SystemBackupService::class,
        function (MockInterface $mock): void {
            $mock
                ->shouldReceive('restore')
                ->once()
                ->andThrow(
                    new RuntimeException(
                        'SECRET MYSQL RESTORE DETAIL'
                    )
                );
        }
    );

    $response = $this
        ->actingAs($admin)
        ->from(
            route(
                'settings.backups.restore.confirm',
                ['filename' => $filename]
            )
        )
        ->post(
            route(
                'settings.backups.restore',
                ['filename' => $filename]
            ),
            [
                'current_password' => 'password',
                'confirmation' => 'BACKUP WIEDERHERSTELLEN',
                'acknowledge' => '1',
            ]
        );

    $response->assertSessionHas(
        'error',
        'Die Wiederherstellung konnte nicht abgeschlossen werden. Bitte Serverprotokoll prüfen.'
    );

    expect(
        (string) session('error')
    )->not->toContain(
        'SECRET MYSQL RESTORE DETAIL'
    );
});

it('rejects a backup from a different schema before maintenance mode', function () {
    $directory = config(
        'system-backup.directory'
    );

    $filename =
        'lager-manual-20261003-180000-deadbeef.sql.gz';

    $path = $directory
        .DIRECTORY_SEPARATOR
        .$filename;

    $content =
        "-- Schema mismatch test\n"
        ."-- Dump completed on 2026-10-03\n";

    file_put_contents(
        $path,
        gzencode(
            $content,
            9
        )
    );

    file_put_contents(
        $path.'.json',
        json_encode([
            'filename' => $filename,
            'type' => SystemBackupService::TYPE_MANUAL,
            'created_at' => now()->toIso8601String(),
            'size' => filesize($path),
            'sha256' => hash_file('sha256', $path),
            'schema_fingerprint' => str_repeat('f', 64),
        ])
    );

    Artisan::shouldReceive('call')
        ->never();

    expect(
        fn () => app(
            SystemBackupService::class
        )->restore($filename)
    )->toThrow(
        RuntimeException::class,
        'Datenbankschema-Stand'
    );
});

it('uses Laravel parsed DB URL values for backup clients', function () {
    $original = config(
        'database.connections.mysql'
    );

    try {
        config([
            'database.connections.mysql' => [
                ...$original,
                'url' => 'mysql://db_url_user:db_url_pass@db-url.example.test:3307/db_url_database',
                'host' => 'fallback.invalid',
                'port' => '3306',
                'database' => 'fallback_database',
                'username' => 'fallback_user',
                'password' => 'fallback_password',
            ],
        ]);

        DB::purge('mysql');

        $service = app(
            SystemBackupService::class
        );

        $method = new ReflectionMethod(
            $service,
            'mysqlConnectionConfig'
        );

        $connection =
            $method->invoke($service);

        expect(
            $connection['host']
        )->toBe('db-url.example.test');

        expect(
            (string) $connection['port']
        )->toBe('3307');

        expect(
            $connection['database']
        )->toBe('db_url_database');

        expect(
            $connection['username']
        )->toBe('db_url_user');

        expect(
            $connection['password']
        )->toBe('db_url_pass');
    } finally {
        DB::purge('mysql');

        config([
            'database.connections.mysql' => $original,
        ]);

        DB::purge('mysql');
    }
});

it('purges restored database sessions', function () {
    config([
        'session.driver' => 'database',
        'session.connection' => config('database.default'),
        'session.table' => 'sessions',
    ]);

    DB::table('sessions')->insert([
        [
            'id' => 'restore-session-test-1',
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'restore-test',
            'payload' => 'test',
            'last_activity' => time(),
        ],
        [
            'id' => 'restore-session-test-2',
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'restore-test',
            'payload' => 'test',
            'last_activity' => time(),
        ],
    ]);

    expect(
        DB::table('sessions')->count()
    )->toBeGreaterThanOrEqual(2);

    $service = app(
        SystemBackupService::class
    );

    $method = new ReflectionMethod(
        $service,
        'purgeRestoredDatabaseSessions'
    );

    $method->invoke($service);

    expect(
        DB::table('sessions')->count()
    )->toBe(0);
});

it('prevents concurrent backup operations with the direct file lock', function () {
    $service = app(
        SystemBackupService::class
    );

    $acquire = new ReflectionMethod(
        $service,
        'acquireOperationLock'
    );

    $release = new ReflectionMethod(
        $service,
        'releaseOperationLock'
    );

    $firstHandle =
        $acquire->invoke($service);

    expect(
        is_resource($firstHandle)
    )->toBeTrue();

    try {
        expect(
            fn () => $acquire->invoke(
                $service
            )
        )->toThrow(
            RuntimeException::class,
            'Es läuft bereits eine Backup- oder Wiederherstellungsaktion.'
        );
    } finally {
        $release->invoke(
            $service,
            $firstHandle
        );
    }

    $secondHandle =
        $acquire->invoke($service);

    expect(
        is_resource($secondHandle)
    )->toBeTrue();

    $release->invoke(
        $service,
        $secondHandle
    );

    $lockPath = config(
        'system-backup.directory'
    )
        .DIRECTORY_SEPARATOR
        .'.operation.lock';

    expect(
        is_file($lockPath)
    )->toBeTrue();
});

it('passes the configured mysql ssl ca to backup clients', function () {
    $sslCaKey = null;

    foreach ([
        'Pdo\\Mysql::ATTR_SSL_CA',
        'PDO::MYSQL_ATTR_SSL_CA',
    ] as $constantName) {
        if (defined($constantName)) {
            $sslCaKey = constant(
                $constantName
            );

            break;
        }
    }

    expect($sslCaKey)->not->toBeNull();

    $service = app(
        SystemBackupService::class
    );

    $method = new ReflectionMethod(
        $service,
        'mysqlCredentials'
    );

    $credentials = $method->invoke(
        $service,
        [
            'username' => 'backup-user',
            'password' => 'backup-password',
            'host' => 'db.example.test',
            'port' => 3306,
            'unix_socket' => '',
            'options' => [
                $sslCaKey => '/etc/mysql/certs/ca.pem',
            ],
        ]
    );

    expect($credentials)
        ->toContain(
            'ssl-ca="/etc/mysql/certs/ca.pem"'
        );
});

it('keeps settings available when backup storage listing fails', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $this->mock(
        SystemBackupService::class,
        function (
            MockInterface $mock
        ): void {
            $mock
                ->shouldReceive('listBackups')
                ->once()
                ->andThrow(
                    new RuntimeException(
                        'BACKUP STORAGE TEST FAILURE'
                    )
                );
        }
    );

    $this
        ->actingAs($admin)
        ->get(
            route('settings.index')
        )
        ->assertOk()
        ->assertSee(
            'Backup-Speicher nicht verfügbar'
        )
        ->assertSee(
            'Die übrigen Einstellungen können weiterhin verwendet werden.'
        );
});

it('disables gtid purged statements in mysql dumps', function () {
    $service = app(
        SystemBackupService::class
    );

    $method = new ReflectionMethod(
        $service,
        'dumpCommand'
    );

    $command = $method->invoke(
        $service,
        '/usr/bin/mysqldump',
        '/tmp/mysql-options.cnf',
        'lager_test'
    );

    expect($command)
        ->toContain(
            '--set-gtid-purged=OFF'
        );
});

it('waits for active writers before an exclusive restore lock', function () {
    $writeLock = app(
        SystemWriteLock::class
    );

    $sharedHandle =
        $writeLock->acquireShared();

    try {
        expect(
            fn () => $writeLock->acquireExclusive(
                true
            )
        )->toThrow(
            RuntimeException::class,
            'Die System-Schreibsperre konnte nicht erhalten werden.'
        );
    } finally {
        $writeLock->release(
            $sharedHandle
        );
    }

    $exclusiveHandle =
        $writeLock->acquireExclusive(
            true
        );

    expect(
        is_resource(
            $exclusiveHandle
        )
    )->toBeTrue();

    $writeLock->release(
        $exclusiveHandle
    );
});

it('keeps the admin logged out when restore audit logging fails', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    $filename =
        'lager-manual-20261003-130000-abcdef12.sql.gz';

    $this->mock(
        SystemBackupService::class,
        function (
            MockInterface $mock
        ) use (
            $admin,
            $filename
        ): void {
            $mock
                ->shouldReceive('restore')
                ->once()
                ->with(
                    $filename,
                    $admin->id,
                    Mockery::type('string')
                )
                ->andReturn([
                    'restored' => [
                        'filename' => $filename,
                        'sha256' => str_repeat(
                            'a',
                            64
                        ),
                    ],
                    'safety_backup' => [
                        'filename' => 'lager-pre_restore-20261003-140000-12345678.sql.gz',
                        'sha256' => str_repeat(
                            'b',
                            64
                        ),
                    ],
                ]);
        }
    );

    ActivityLog::creating(
        static function (): void {
            throw new RuntimeException(
                'AUDIT FAILURE TEST'
            );
        }
    );

    try {
        $response = $this
            ->actingAs($admin)
            ->post(
                route(
                    'settings.backups.restore',
                    [
                        'filename' => $filename,
                    ]
                ),
                [
                    'current_password' => 'password',
                    'confirmation' => 'BACKUP WIEDERHERSTELLEN',
                    'acknowledge' => '1',
                ]
            );

        $response->assertRedirect(
            route('login')
        );

        $this->assertGuest();
    } finally {
        ActivityLog::flushEventListeners();
    }
});
