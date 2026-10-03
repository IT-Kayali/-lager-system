@php
    $regularBackups = collect($systemBackups)
        ->reject(
            fn (array $backup): bool =>
                $backup['type'] === 'pre_restore'
        )
        ->values();

    $safetyBackups = collect($systemBackups)
        ->filter(
            fn (array $backup): bool =>
                $backup['type'] === 'pre_restore'
        )
        ->values();
@endphp

<section id="system-backups" class="premium-card">
    <div>
        <h2>
            <i class="bi bi-database-check"></i>
            Datenbank-Backups
        </h2>

        <p class="premium-muted">
            Erstelle manuelle Sicherungen oder lasse die Datenbank
            automatisch sichern. Backups werden privat auf dem Server
            gespeichert.
        </p>
    </div>

    <div class="premium-alert">
        <strong>Hinweis:</strong>
        Diese Sicherungen enthalten die MySQL-Datenbank.
        Hochgeladene Dateien außerhalb der Datenbank sind darin
        nicht enthalten.
    </div>

    <div class="premium-form-grid two">
        <div>
            <h3>Manuelles Backup</h3>

            <p class="premium-muted">
                Manuelle Backups werden nicht automatisch durch die
                Aufbewahrungsregel gelöscht.
            </p>

            <form
                method="POST"
                action="{{ route('settings.backups.store') }}"
                data-confirm="Jetzt ein vollständiges Datenbank-Backup erstellen?"
            >
                @csrf

                <button
                    type="submit"
                    class="premium-btn gold"
                >
                    <i class="bi bi-database-add"></i>
                    Backup jetzt erstellen
                </button>
            </form>
        </div>

        <div>
            <h3>Automatische Backups</h3>

            <form
                method="POST"
                action="{{ route('settings.backups.settings') }}"
            >
                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="backup_automatic_enabled"
                    value="0"
                >

                <div class="premium-form-field">
                    <label>
                        <input
                            type="checkbox"
                            name="backup_automatic_enabled"
                            value="1"
                            @checked(
                                old(
                                    'backup_automatic_enabled',
                                    $backupAutomaticEnabled
                                )
                            )
                        >
                        Automatische Backups aktivieren
                    </label>
                </div>

                <div class="premium-form-field">
                    <label for="backup_automatic_interval">
                        Intervall
                    </label>

                    <select
                        id="backup_automatic_interval"
                        name="backup_automatic_interval"
                        class="premium-input"
                        required
                    >
                        <option
                            value="6_hours"
                            @selected(
                                old(
                                    'backup_automatic_interval',
                                    $backupAutomaticInterval
                                ) === '6_hours'
                            )
                        >
                            Alle 6 Stunden
                        </option>

                        <option
                            value="12_hours"
                            @selected(
                                old(
                                    'backup_automatic_interval',
                                    $backupAutomaticInterval
                                ) === '12_hours'
                            )
                        >
                            Alle 12 Stunden
                        </option>

                        <option
                            value="daily"
                            @selected(
                                old(
                                    'backup_automatic_interval',
                                    $backupAutomaticInterval
                                ) === 'daily'
                            )
                        >
                            Täglich
                        </option>

                        <option
                            value="weekly"
                            @selected(
                                old(
                                    'backup_automatic_interval',
                                    $backupAutomaticInterval
                                ) === 'weekly'
                            )
                        >
                            Wöchentlich
                        </option>
                    </select>

                    @error('backup_automatic_interval')
                        <div class="premium-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="backup_automatic_retention">
                        Automatische Backups aufbewahren
                    </label>

                    <input
                        id="backup_automatic_retention"
                        name="backup_automatic_retention"
                        type="number"
                        min="1"
                        max="90"
                        step="1"
                        class="premium-input"
                        value="{{ old(
                            'backup_automatic_retention',
                            $backupAutomaticRetention
                        ) }}"
                        required
                    >

                    <div class="premium-muted">
                        Es werden 1 bis 90 automatische Backups
                        aufbewahrt. Manuelle und Vor-Restore-
                        Sicherheitskopien werden hiervon nicht gelöscht.
                    </div>

                    @error('backup_automatic_retention')
                        <div class="premium-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="premium-btn gold"
                >
                    <i class="bi bi-save"></i>
                    Backup-Einstellungen speichern
                </button>
            </form>
        </div>
    </div>

    <div>
        <h3>
            <i class="bi bi-archive"></i>
            Reguläre Backups
        </h3>

        <p class="premium-muted">
            Hier stehen manuell erstellte und automatische
            Datenbank-Backups.
        </p>
    </div>

    @if ($regularBackups->isEmpty())
        <div class="premium-alert">
            Noch keine regulären Backups vorhanden.
        </div>
    @else
        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Erstellt</th>
                        <th>Typ</th>
                        <th>Größe</th>
                        <th>SHA256</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($regularBackups as $backup)
                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $backup['created_at']
                                )->format('d.m.Y H:i:s') }}
                            </td>

                            <td>
                                @if ($backup['type'] === 'automatic')
                                    <span class="premium-badge">
                                        Automatisch
                                    </span>
                                @else
                                    <span class="premium-badge ok">
                                        Manuell
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $backup['size_formatted'] }}
                            </td>

                            <td>
                                <code
                                    title="{{ $backup['sha256'] }}"
                                >
                                    {{ \Illuminate\Support\Str::limit(
                                        $backup['sha256'],
                                        18,
                                        '…'
                                    ) }}
                                </code>
                            </td>

                            <td>
                                <div>
                                    <a
                                        href="{{ route(
                                            'settings.backups.download',
                                            [
                                                'filename' =>
                                                    $backup['filename'],
                                            ]
                                        ) }}"
                                        class="premium-btn"
                                    >
                                        <i class="bi bi-download"></i>
                                        Download
                                    </a>

                                    <a
                                        href="{{ route(
                                            'settings.backups.restore.confirm',
                                            [
                                                'filename' =>
                                                    $backup['filename'],
                                            ]
                                        ) }}"
                                        class="premium-btn"
                                    >
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Wiederherstellen
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'settings.backups.destroy',
                                            [
                                                'filename' =>
                                                    $backup['filename'],
                                            ]
                                        ) }}"
                                        data-confirm="Dieses Backup wirklich dauerhaft löschen?"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="premium-btn premium-danger"
                                        >
                                            <i class="bi bi-trash3"></i>
                                            Löschen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div>
        <h3>
            <i class="bi bi-shield-check"></i>
            Sicherheitskopien vor Wiederherstellung
        </h3>

        <p class="premium-muted">
            Diese Sicherungen werden automatisch direkt vor einer
            Wiederherstellung erzeugt. Sie enthalten den Datenbankstand,
            der unmittelbar vor dem jeweiligen Restore vorhanden war.
        </p>
    </div>

    <div class="premium-alert">
        <strong>Nicht mit normalen Backups verwechseln:</strong>
        Diese Dateien dienen dazu, bei Bedarf wieder auf den Zustand
        unmittelbar vor einer Wiederherstellung zurückzugehen.
    </div>

    @if ($safetyBackups->isEmpty())
        <div class="premium-alert">
            Noch keine Vor-Restore-Sicherheitskopien vorhanden.
        </div>
    @else
        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Erstellt</th>
                        <th>Typ</th>
                        <th>Größe</th>
                        <th>SHA256</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($safetyBackups as $backup)
                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $backup['created_at']
                                )->format('d.m.Y H:i:s') }}
                            </td>

                            <td>
                                <span class="premium-badge">
                                    Vor Restore
                                </span>
                            </td>

                            <td>
                                {{ $backup['size_formatted'] }}
                            </td>

                            <td>
                                <code
                                    title="{{ $backup['sha256'] }}"
                                >
                                    {{ \Illuminate\Support\Str::limit(
                                        $backup['sha256'],
                                        18,
                                        '…'
                                    ) }}
                                </code>
                            </td>

                            <td>
                                <div>
                                    <a
                                        href="{{ route(
                                            'settings.backups.download',
                                            [
                                                'filename' =>
                                                    $backup['filename'],
                                            ]
                                        ) }}"
                                        class="premium-btn"
                                    >
                                        <i class="bi bi-download"></i>
                                        Download
                                    </a>

                                    <a
                                        href="{{ route(
                                            'settings.backups.restore.confirm',
                                            [
                                                'filename' =>
                                                    $backup['filename'],
                                            ]
                                        ) }}"
                                        class="premium-btn"
                                    >
                                        <i class="bi bi-shield-arrow-up"></i>
                                        Sicherheitskopie zurückspielen
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'settings.backups.destroy',
                                            [
                                                'filename' =>
                                                    $backup['filename'],
                                            ]
                                        ) }}"
                                        data-confirm="Diese Vor-Restore-Sicherheitskopie wirklich dauerhaft löschen?"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="premium-btn premium-danger"
                                        >
                                            <i class="bi bi-trash3"></i>
                                            Löschen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
