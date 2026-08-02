<x-layouts.premium title="Einstellungen" subtitle="Systemoptionen, Reservierungsdauer und PDF-Vorlagen verwalten.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">{{ session('error') }}</div>
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

    @include('pages.settings._button-appearance')
    @include('pages.settings._customer-groups')
    @include('pages.settings._price-tiers')

    <section class="premium-card" style="margin-top:22px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;">
                <i class="bi bi-image"></i>
            </span>
            <div>
                <h2 style="margin:0;font-size:22px;font-weight:950;">Anmeldeseite</h2>
                <p class="premium-muted" style="margin:4px 0 0;">Modernes Login-Design verwalten und optional ein eigenes Hintergrundbild hinterlegen.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.login-appearance.update') }}" enctype="multipart/form-data" style="display:grid;gap:16px;">
            @csrf
            @method('PUT')

            <div style="min-height:180px;display:grid;place-items:center;border:1px dashed #d9c9ae;border-radius:22px;background:linear-gradient(135deg,#fbf6ed,#f3e5cf);background-size:cover;background-position:center;@if (! empty($loginBackgroundUrl)) background-image:linear-gradient(135deg,rgba(18,18,18,.52),rgba(212,173,22,.20)),url('{{ $loginBackgroundUrl }}'); @endif">
                <div style="width:min(86%,280px);display:grid;gap:8px;justify-items:center;padding:24px;border:1px solid rgba(227,202,110,.42);border-radius:24px;background:rgba(18,18,18,.88);color:#fff;text-align:center;box-shadow:0 24px 70px rgba(18,18,18,.25);">
                    <span style="width:48px;height:48px;display:grid;place-items:center;border-radius:16px;background:#d4ad16;color:#121212;font-size:22px;">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                    <strong style="font-size:19px;font-weight:950;">Login Vorschau</strong>
                    <small style="max-width:100%;color:#f3e8be;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ ! empty($loginBackgroundPath) ? basename($loginBackgroundPath) : 'Standard-Verlauf ohne Bild' }}
                    </small>
                </div>
            </div>

            <div class="premium-form-field">
                <label for="login_background">Hintergrundbild optional</label>
                <input
                    id="login_background"
                    name="login_background"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    class="premium-input"
                    style="padding:12px !important;"
                >
                <div class="premium-muted" style="margin-top:8px;">Empfohlen: JPG, PNG oder WebP im Querformat. Maximal 4 MB.</div>
                @error('login_background')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-field">
                <label for="login_logo">Logo optional</label>
                <input
                    id="login_logo"
                    name="login_logo"
                    type="file"
                    accept="image/png,image/jpeg,image/webp,image/svg+xml"
                    class="premium-input"
                    style="padding:12px !important;"
                >
                <div class="premium-muted" style="margin-top:8px;">Empfohlen: transparentes PNG/SVG. Maximal 2 MB.</div>
                @error('login_logo')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-grid two" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
                <div class="premium-form-field">
                    <label for="login_eyebrow">Kleine Überschrift</label>
                    <input
                        id="login_eyebrow"
                        name="login_eyebrow"
                        class="premium-input"
                        value="{{ old('login_eyebrow', $loginEyebrow ?? 'Sicherer Zugriff') }}"
                        maxlength="80"
                    >
                    @error('login_eyebrow')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="login_title">Hauptüberschrift</label>
                    <input
                        id="login_title"
                        name="login_title"
                        class="premium-input"
                        value="{{ old('login_title', $loginTitle ?? 'Alles im Lager sofort im Blick.') }}"
                        maxlength="120"
                    >
                    @error('login_title')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-field">
                <label for="login_subtitle">Beschreibungstext</label>
                <textarea
                    id="login_subtitle"
                    name="login_subtitle"
                    class="premium-input"
                    rows="3"
                    maxlength="240"
                >{{ old('login_subtitle', $loginSubtitle ?? 'Modernes Dashboard für Bestände, Angebote, Rechnungen und Warnungen — schnell, klar und sicher.') }}</textarea>
                <div class="premium-muted" style="margin-top:8px;">Dieser Text erscheint links auf der Login-Seite.</div>
                @error('login_subtitle')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-upload"></i>
                    Hintergrund speichern
                </button>

                @if (! empty($loginBackgroundUrl))
                    <button class="premium-btn" type="submit" name="remove_login_background" value="1">
                        <i class="bi bi-trash"></i>
                        Hintergrund entfernen
                    </button>
                @endif

                @if (! empty($loginLogoUrl))
                    <button class="premium-btn" type="submit" name="remove_login_logo" value="1">
                        <i class="bi bi-x-circle"></i>
                        Logo entfernen
                    </button>
                @endif
            </div>
        </form>
    </section>
</x-layouts.premium>
