<x-layouts.premium title="Einstellungen" subtitle="Systemoptionen, Reservierungsdauer und PDF-Vorlagen verwalten.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" data-csp-style="s-b2a82328">{{ session('error') }}</div>
    @endif

    <div class="premium-grid" data-csp-style="s-c0b6f432">
        <section class="premium-card">
            <h2 data-csp-style="s-2131aab4">
                Reservierungen
            </h2>

            <p class="premium-muted" data-csp-style="s-d9fcbe51">
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

                    <div class="premium-muted" data-csp-style="s-f773241c">
                        Beispiel: 72 bedeutet, dass neue Angebote 72 Stunden reserviert bleiben.
                    </div>

                    @error('reservation_hours')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="premium-btn gold" type="submit" data-csp-style="s-e9f7b175">
                    <i class="bi bi-save"></i>
                    Einstellung speichern
                </button>
            </form>
        </section>

        <section class="premium-card">
            <h2 data-csp-style="s-2131aab4">
                PDF-Vorlagen
            </h2>

            <p class="premium-muted" data-csp-style="s-d9fcbe51">
                Hier kannst du Firmenlogo, Firmendaten, Zahlungsinformationen und Footer-Hinweise für Angebote und Rechnungen bearbeiten.
            </p>

            @if (Route::has('document-templates.index'))
                <a href="{{ route('document-templates.index') }}" class="premium-btn gold">
                    <i class="bi bi-file-earmark-pdf"></i>
                    PDF-Vorlagen bearbeiten
                </a>
            @else
                <div class="premium-alert" data-csp-style="s-0b92fcc8">
                    Die Route für PDF-Vorlagen wurde nicht gefunden.
                </div>
            @endif
        </section>
    </div>

    <section id="low-stock-warning" class="premium-card" data-csp-style="s-1c4a2677">
        <div data-csp-style="s-c309ae69">
            <span data-csp-style="s-a9cc23e5">
                <i class="bi bi-exclamation-triangle"></i>
            </span>
            <div>
                <h2 data-csp-style="s-6191170d">Warnung bei niedrigem Bestand</h2>
                <p class="premium-muted" data-csp-style="s-3ca59c3a">Lege den prozentualen Zuschlag auf den Mindestbestand fest, ab dem ein Produkt als „Niedriger Bestand“ markiert wird.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.low-stock-warning.update') }}">
            @csrf
            @method('PUT')

            <div class="premium-form-field" data-csp-style="s-cd459318">
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

                <div class="premium-muted" data-csp-style="s-b33fa592">
                    Beispiel: Mindestbestand 500 und Einstellung 100 % ergibt eine Warnschwelle von 1.000. Von 501 bis 1.000 gilt der Bestand als niedrig, bei 500 oder weniger als kritisch.
                </div>

                @error('low_stock_warning_percentage')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <button class="premium-btn gold" type="submit" data-csp-style="s-e9f7b175">
                <i class="bi bi-save"></i>
                Warnschwelle speichern
            </button>
        </form>
    </section>

    @include('pages.settings._button-appearance')
    @include('pages.settings._customer-groups')
    @include('pages.settings._price-tiers')

    <section id="login-appearance" class="premium-card" data-csp-style="s-1c4a2677">
        <div data-csp-style="s-c309ae69">
            <span data-csp-style="s-a9cc23e5">
                <i class="bi bi-window"></i>
            </span>
            <div>
                <h2 data-csp-style="s-6191170d">Anmeldeseite & Browser</h2>
                <p class="premium-muted" data-csp-style="s-3ca59c3a">Login-Texte, Logo, Hintergrundbild, Seitenname und Favicon zentral verwalten.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.login-appearance.update') }}" enctype="multipart/form-data" data-csp-style="s-de59f6e4">
            @csrf
            @method('PUT')

            <div class="csp-login-preview-stage @if (! empty($loginBackgroundUrl)) has-background @endif">
                @if (! empty($loginBackgroundUrl))
                    <img src="{{ $loginBackgroundUrl }}" alt="" class="csp-login-preview-background" aria-hidden="true">
                @endif
                <div data-csp-style="s-553c8641">
                    @if (! empty($loginLogoUrl))
                        <img src="{{ $loginLogoUrl }}" alt="Login-Logo Vorschau" data-csp-style="s-b532908e">
                    @else
                        <span data-csp-style="s-e7bbe53a">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                    @endif
                    <strong data-csp-style="s-c64e7aea">{{ $siteName }}</strong>
                    <small data-csp-style="s-49daa5cd">
                        {{ ! empty($loginBackgroundPath) ? basename($loginBackgroundPath) : 'Standard-Verlauf ohne Bild' }}
                    </small>
                </div>
            </div>

            <div class="premium-form-grid two" data-csp-style="s-0aee8db6">
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
                    <div class="premium-muted" data-csp-style="s-f773241c">Erscheint im Browser-Tab, z. B. „Login · {{ $siteName }}“.</div>
                    @error('site_name')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="site_favicon">Favicon optional</label>
                    <div data-csp-style="s-089bd37d">
                        @if (! empty($siteFaviconUrl))
                            <img src="{{ $siteFaviconUrl }}" alt="Aktuelles Favicon" data-csp-style="s-c73ee4a1">
                        @endif
                        <input
                            id="site_favicon"
                            name="site_favicon"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="premium-input"
                            data-csp-style="s-4457d3ee"
                        >
                    </div>
                    <div class="premium-muted" data-csp-style="s-f773241c">Empfohlen: quadratisches PNG oder WebP, z. B. 64×64 oder 128×128 Pixel. Maximal 1 MB.</div>
                    @error('site_favicon')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-grid two" data-csp-style="s-0aee8db6">
                <div class="premium-form-field">
                    <label for="login_background">Hintergrundbild optional</label>
                    <input
                        id="login_background"
                        name="login_background"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="premium-input"
                        data-csp-style="s-8414e8e3"
                    >
                    <div class="premium-muted" data-csp-style="s-f773241c">Empfohlen: JPG, PNG oder WebP im Querformat. Maximal 4 MB.</div>
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
                        data-csp-style="s-8414e8e3"
                    >
                    <div class="premium-muted" data-csp-style="s-f773241c">Empfohlen: transparentes PNG oder WebP. Maximal 2 MB.</div>
                    @error('login_logo')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="premium-form-grid two" data-csp-style="s-0aee8db6">
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
                <div class="premium-muted" data-csp-style="s-f773241c">Dieser Text erscheint links auf der Login-Seite.</div>
                @error('login_subtitle')
                    <div class="premium-error">{{ $message }}</div>
                @enderror
            </div>

            <div data-csp-style="s-d4c80558">
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


</x-layouts.premium>