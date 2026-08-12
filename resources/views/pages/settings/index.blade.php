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

    <section id="low-stock-warning" class="premium-card" style="margin-top:22px;scroll-margin-top:24px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;">
                <i class="bi bi-exclamation-triangle"></i>
            </span>
            <div>
                <h2 style="margin:0;font-size:22px;font-weight:950;">Warnung bei niedrigem Bestand</h2>
                <p class="premium-muted" style="margin:4px 0 0;">Lege den prozentualen Zuschlag auf den Mindestbestand fest, ab dem ein Produkt als „Niedriger Bestand“ markiert wird.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.low-stock-warning.update') }}">
            @csrf
            @method('PUT')

            <div class="premium-form-field" style="max-width:520px;">
                <label for="low_stock_warning_percentage">Zuschlag auf Mindestbestand in % *</label>
                <input
                    id="low_stock_warning_percentage"
                    name="low_stock_warning_percentage"
                    type="number"
                    min="0"
                    max="1000"
                    step="0.1"
                    class="premium-input"
                    value="{{ old('low_stock_warning_percentage', $lowStockWarningPercentage) }}"
                    required
                >

                <div class="premium-muted" style="margin-top:8px;line-height:1.55;">
                    Beispiel: Mindestbestand 500 und Einstellung 100 % ergibt eine Warnschwelle von 1.000. Von 501 bis 1.000 gilt der Bestand als niedrig, bei 500 oder weniger als kritisch.
                </div>

                @error('low_stock_warning_percentage')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <button class="premium-btn gold" type="submit" style="margin-top:18px;">
                <i class="bi bi-save"></i>
                Warnschwelle speichern
            </button>
        </form>
    </section>

    @include('pages.settings._button-appearance')
    @include('pages.settings._customer-groups')
    @include('pages.settings._price-tiers')

    <section id="login-appearance" class="premium-card" style="margin-top:22px;scroll-margin-top:24px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;">
                <i class="bi bi-window"></i>
            </span>
            <div>
                <h2 style="margin:0;font-size:22px;font-weight:950;">Anmeldeseite & Browser</h2>
                <p class="premium-muted" style="margin:4px 0 0;">Login-Texte, Logo, Hintergrundbild, Seitenname und Favicon zentral verwalten.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.login-appearance.update') }}" enctype="multipart/form-data" style="display:grid;gap:16px;">
            @csrf
            @method('PUT')

            <div style="min-height:180px;display:grid;place-items:center;border:1px dashed #d9c9ae;border-radius:22px;background:linear-gradient(135deg,#fbf6ed,#f3e5cf);background-size:cover;background-position:center;@if (! empty($loginBackgroundUrl)) background-image:linear-gradient(135deg,rgba(18,18,18,.52),rgba(212,173,22,.20)),url('{{ $loginBackgroundUrl }}'); @endif">
                <div style="width:min(86%,320px);display:grid;gap:8px;justify-items:center;padding:24px;border:1px solid rgba(227,202,110,.42);border-radius:24px;background:rgba(18,18,18,.88);color:#fff;text-align:center;box-shadow:0 24px 70px rgba(18,18,18,.25);">
                    @if (! empty($loginLogoUrl))
                        <img src="{{ $loginLogoUrl }}" alt="Login-Logo Vorschau" style="display:block;max-width:220px;max-height:72px;width:auto;height:auto;object-fit:contain;">
                    @else
                        <span style="width:48px;height:48px;display:grid;place-items:center;border-radius:16px;background:#d4ad16;color:#121212;font-size:22px;">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                    @endif
                    <strong style="font-size:19px;font-weight:950;">{{ $siteName }}</strong>
                    <small style="max-width:100%;color:#f3e8be;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ ! empty($loginBackgroundPath) ? basename($loginBackgroundPath) : 'Standard-Verlauf ohne Bild' }}
                    </small>
                </div>
            </div>

            <div class="premium-form-grid two" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
                <div class="premium-form-field">
                    <label for="site_name">Seitenname *</label>
                    <input
                        id="site_name"
                        name="site_name"
                        class="premium-input"
                        value="{{ old('site_name', $siteName) }}"
                        maxlength="80"
                        required
                    >
                    <div class="premium-muted" style="margin-top:8px;">Erscheint im Browser-Tab, z. B. „Login · {{ $siteName }}“.</div>
                    @error('site_name')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="site_favicon">Favicon optional</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        @if (! empty($siteFaviconUrl))
                            <img src="{{ $siteFaviconUrl }}" alt="Aktuelles Favicon" style="width:40px;height:40px;object-fit:contain;border:1px solid #d9c9ae;border-radius:10px;background:#fff;padding:5px;flex:0 0 40px;">
                        @endif
                        <input
                            id="site_favicon"
                            name="site_favicon"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="premium-input"
                            style="padding:12px !important;flex:1;"
                        >
                    </div>
                    <div class="premium-muted" style="margin-top:8px;">Empfohlen: quadratisches PNG oder WebP, z. B. 64×64 oder 128×128 Pixel. Maximal 1 MB.</div>
                    @error('site_favicon')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-grid two" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
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
                    <label for="login_logo">Login-Logo optional</label>
                    <input
                        id="login_logo"
                        name="login_logo"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="premium-input"
                        style="padding:12px !important;"
                    >
                    <div class="premium-muted" style="margin-top:8px;">Empfohlen: transparentes PNG oder WebP. Maximal 2 MB.</div>
                    @error('login_logo')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-grid two" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
                <div class="premium-form-field">
                    <label for="login_eyebrow">Kleine Überschrift *</label>
                    <input
                        id="login_eyebrow"
                        name="login_eyebrow"
                        class="premium-input"
                        value="{{ old('login_eyebrow', $loginEyebrow) }}"
                        maxlength="80"
                        required
                    >
                    @error('login_eyebrow')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="login_title">Hauptüberschrift *</label>
                    <input
                        id="login_title"
                        name="login_title"
                        class="premium-input"
                        value="{{ old('login_title', $loginTitle) }}"
                        maxlength="120"
                        required
                    >
                    @error('login_title')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-field">
                <label for="login_subtitle">Beschreibungstext *</label>
                <textarea
                    id="login_subtitle"
                    name="login_subtitle"
                    class="premium-input"
                    rows="3"
                    maxlength="240"
                    required
                >{{ old('login_subtitle', $loginSubtitle) }}</textarea>
                <div class="premium-muted" style="margin-top:8px;">Dieser Text erscheint links auf der Login-Seite.</div>
                @error('login_subtitle')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-save"></i>
                    Login-Einstellungen speichern
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

                @if (! empty($siteFaviconUrl))
                    <button class="premium-btn" type="submit" name="remove_site_favicon" value="1">
                        <i class="bi bi-x-circle"></i>
                        Favicon entfernen
                    </button>
                @endif
            </div>
        </form>
    </section>

    <style>
        @media (max-width: 820px) {
            #login-appearance .premium-form-grid.two {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</x-layouts.premium>
