<?php

return [
    'directory' => storage_path('app/private/system-backups'),

    'write_lock_path' => storage_path(
        'framework/system-write.lock'
    ),

    'dump_binary' => env(
        'SYSTEM_BACKUP_DUMP_BINARY',
        '/usr/bin/mysqldump'
    ),

    'mysql_binary' => env(
        'SYSTEM_BACKUP_MYSQL_BINARY',
        '/usr/bin/mysql'
    ),

    'gzip_binary' => env(
        'SYSTEM_BACKUP_GZIP_BINARY',
        '/usr/bin/gzip'
    ),

    'timeout' => (int) env(
        'SYSTEM_BACKUP_TIMEOUT',
        600
    ),
];
