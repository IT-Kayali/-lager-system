<section id="system-data-reset" class="premium-card">
    <div>
        <h2>
            <i class="bi bi-exclamation-octagon"></i>
            Systemdaten zurücksetzen
        </h2>

        <p class="premium-muted">
            Löscht die fachlichen Geschäfts- und Testdaten und versetzt das
            Lager in einen leeren Ausgangszustand.
        </p>
    </div>

    <div class="premium-alert">
        <strong>Wichtig:</strong>
        Benutzerkonten, Admins, Passwörter, 2FA, Systemeinstellungen,
        Kundengruppen, Preisstufen-Definitionen und PDF-Vorlagen bleiben erhalten.
    </div>

    <div class="premium-form-grid two">
        <div>
            <strong>Wird gelöscht</strong>
            <ul>
                <li>Angebote, Positionen und interne Notizen</li>
                <li>Kunden und Guthabenbuchungen</li>
                <li>Produkte und Kategorien</li>
                <li>Chargen und Lagerbewegungen</li>
                <li>Produktpreise und Preisregeln</li>
                <li>Lieferanten</li>
                <li>Filialausgänge und deren Positionen</li>
                <li>Lager-Benachrichtigungen</li>
                <li>Bisherige Aktivitätslogs</li>
            </ul>
        </div>

        <div>
            <strong>Bleibt erhalten</strong>
            <ul>
                <li>Benutzer und Admin-Konten</li>
                <li>Passwörter und Zwei-Faktor-Authentifizierung</li>
                <li>Allgemeine Systemeinstellungen</li>
                <li>Kundengruppen</li>
                <li>Preisstufen-Definitionen</li>
                <li>Dokument- und PDF-Vorlagen</li>
                <li>Login- und Button-Design</li>
            </ul>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('settings.system-data-reset') }}"
        data-confirm="Letzte Warnung: Alle Geschäfts- und Testdaten wirklich dauerhaft löschen?"
    >
        @csrf

        <div class="premium-form-grid two">
            <div class="premium-form-field">
                <label for="system_reset_password">
                    Aktuelles Admin-Passwort *
                </label>

                <input
                    id="system_reset_password"
                    name="current_password"
                    type="password"
                    class="premium-input @error('current_password') premium-invalid-field @enderror"
                    autocomplete="current-password"
                    required
                >

                @error('current_password')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-field">
                <label for="system_reset_confirmation">
                    Bestätigung *
                </label>

                <input
                    id="system_reset_confirmation"
                    name="confirmation"
                    type="text"
                    class="premium-input @error('confirmation') premium-invalid-field @enderror"
                    placeholder="SYSTEM RESETTEN"
                    autocomplete="one-time-code"
                    autocapitalize="characters"
                    spellcheck="false"
                    required
                >

                <div class="premium-muted">
                    Gib exakt <strong>SYSTEM RESETTEN</strong> ein.
                </div>

                @error('confirmation')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="premium-form-field">
            <label>
                <input
                    type="checkbox"
                    name="acknowledge"
                    value="1"
                    required
                >
                Ich verstehe, dass die Geschäfts- und Testdaten dauerhaft
                gelöscht werden und nicht über diesen Button wiederhergestellt
                werden können.
            </label>

            @error('acknowledge')
                <div class="premium-error">{{ $message }}</div>
            @enderror
        </div>

        <button
            type="submit"
            class="premium-btn premium-danger"
        >
            <i class="bi bi-trash3"></i>
            Systemdaten endgültig zurücksetzen
        </button>
    </form>
</section>
