<x-layouts.premium
    title="Backup wiederherstellen"
    subtitle="Datenbank auf einen früheren Sicherungsstand zurücksetzen."
>
    @if (session('error'))
        <div class="premium-alert" data-csp-style="s-b2a82328">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div>
            <h2>
                <i class="bi bi-arrow-counterclockwise"></i>
                Datenbank-Backup wiederherstellen
            </h2>

            <p class="premium-muted">
                Der aktuelle Datenbankstand wird durch den Stand dieses
                Backups ersetzt.
            </p>
        </div>

        <div class="premium-alert">
            <strong>Sicherheitsmechanismus:</strong>
            Direkt vor der Wiederherstellung wird automatisch ein vollständiges
            Backup des aktuellen Datenbankstands angelegt. Falls der Restore
            fehlschlägt, versucht das System diese Sicherheitskopie automatisch
            zurückzuspielen.
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <tbody>
                    <tr>
                        <th>Datei</th>
                        <td>
                            <code>{{ $backup['filename'] }}</code>
                        </td>
                    </tr>

                    <tr>
                        <th>Erstellt</th>
                        <td>
                            {{ \Carbon\Carbon::parse(
                                $backup['created_at']
                            )->format('d.m.Y H:i:s') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Typ</th>
                        <td>
                            @if ($backup['type'] === 'automatic')
                                Automatisch
                            @elseif ($backup['type'] === 'pre_restore')
                                Sicherheitsbackup vor Wiederherstellung
                            @else
                                Manuell
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Größe</th>
                        <td>{{ $backup['size_formatted'] }}</td>
                    </tr>

                    <tr>
                        <th>SHA256</th>
                        <td>
                            <code>{{ $backup['sha256'] }}</code>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="premium-alert">
            <strong>Achtung:</strong>
            Daten, die erst nach Erstellung dieses Backups angelegt oder
            geändert wurden, können dadurch verloren gehen. Dieses Backup
            enthält die MySQL-Datenbank, aber keine hochgeladenen Dateien
            außerhalb der Datenbank.
        </div>

        <form
            method="POST"
            action="{{ route(
                'settings.backups.restore',
                ['filename' => $backup['filename']]
            ) }}"
            data-confirm="Letzte Warnung: Diesen Datenbankstand wirklich wiederherstellen?"
        >
            @csrf

            <div class="premium-form-grid two">
                <div class="premium-form-field">
                    <label for="backup_restore_password">
                        Aktuelles Admin-Passwort *
                    </label>

                    <input
                        id="backup_restore_password"
                        name="current_password"
                        type="password"
                        class="premium-input @error('current_password') premium-invalid-field @enderror"
                        autocomplete="current-password"
                        required
                    >

                    @error('current_password')
                        <div class="premium-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="backup_restore_confirmation">
                        Bestätigung *
                    </label>

                    <input
                        id="backup_restore_confirmation"
                        name="confirmation"
                        type="text"
                        class="premium-input @error('confirmation') premium-invalid-field @enderror"
                        placeholder="BACKUP WIEDERHERSTELLEN"
                        autocomplete="one-time-code"
                        autocapitalize="characters"
                        spellcheck="false"
                        value="{{ old('confirmation') }}"
                        required
                    >

                    <div class="premium-muted">
                        Gib exakt
                        <strong>BACKUP WIEDERHERSTELLEN</strong>
                        ein.
                    </div>

                    @error('confirmation')
                        <div class="premium-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-field">
                <label>
                    <input
                        type="checkbox"
                        name="acknowledge"
                        value="1"
                        @checked(old('acknowledge'))
                        required
                    >

                    Ich verstehe, dass der aktuelle Datenbankstand durch den
                    gewählten Sicherungsstand ersetzt wird.
                </label>

                @error('acknowledge')
                    <div class="premium-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div>
                <a
                    href="{{ route('settings.index') }}#system-backups"
                    class="premium-btn"
                >
                    <i class="bi bi-arrow-left"></i>
                    Abbrechen
                </a>

                <button
                    type="submit"
                    class="premium-btn premium-danger"
                >
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Backup jetzt wiederherstellen
                </button>
            </div>
        </form>
    </section>
</x-layouts.premium>
