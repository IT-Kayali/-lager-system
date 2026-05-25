<x-layouts.premium title="Einstellungen" subtitle="Systemoptionen, Reservierungsdauer und PDF-Vorlagen verwalten.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    <div class="premium-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); align-items:start;">
        <section class="premium-card">
            <h2 style="font-size:22px; font-weight:950; margin:0 0 10px;">
                Reservierungen
            </h2>

            <p class="premium-muted" style="margin-bottom:18px;">
                Hier legst du fest, wie lange Ware nach dem Erstellen eines Angebots automatisch reserviert bleibt.
            </p>

            <form method="POST" action="{{ route('settings.reservation.update') }}">
                @csrf
                @method('PUT')

                <div class="premium-form-field">
                    <label for="reservation_hours">Reservierungsdauer in Stunden *</label>
                    <input
                        id="reservation_hours"
                        name="reservation_hours"
                        type="number"
                        min="1"
                        max="720"
                        step="1"
                        class="premium-input"
                        value="{{ old('reservation_hours', $reservationHours) }}"
                        required
                    >

                    <div class="premium-muted" style="margin-top:8px;">
                        Beispiel: 72 bedeutet, dass neue Angebote 72 Stunden reserviert bleiben.
                    </div>

                    @error('reservation_hours')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="premium-btn gold" type="submit" style="margin-top:18px;">
                    <i class="bi bi-save"></i>
                    Einstellung speichern
                </button>
            </form>
        </section>

        <section class="premium-card">
            <h2 style="font-size:22px; font-weight:950; margin:0 0 10px;">
                PDF-Vorlagen
            </h2>

            <p class="premium-muted" style="margin-bottom:18px;">
                Hier kannst du Firmenlogo, Firmendaten, Zahlungsinformationen und Footer-Hinweise für Angebote und Rechnungen bearbeiten.
            </p>

            @if (Route::has('document-templates.index'))
                <a href="{{ route('document-templates.index') }}" class="premium-btn gold">
                    <i class="bi bi-file-earmark-pdf"></i>
                    PDF-Vorlagen bearbeiten
                </a>
            @else
                <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
                    Die Route für PDF-Vorlagen wurde nicht gefunden.
                </div>
            @endif
        </section>
    </div>
</x-layouts.premium>
