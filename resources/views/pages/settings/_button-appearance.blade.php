@include('pages.settings._batch-expiry')

<section id="button-appearance" class="premium-card" data-csp-style="s-1c4a2677">
    <details @if ($errors->hasAny([
        'primary_button_background',
        'primary_button_text',
        'secondary_button_background',
        'secondary_button_text',
    ])) open @endif>
        <summary data-csp-style="s-26f3d8ca">
            <span data-csp-style="s-089bd37d">
                <span data-csp-style="s-a9cc23e5">
                    <i class="bi bi-palette"></i>
                </span>
                <span>
                    <strong data-csp-style="s-9bba2dfe">Button-Design</strong>
                    <span class="premium-muted">Farben für Speichern, Weiter, Zurück und andere Standardbuttons.</span>
                </span>
            </span>

            <span class="premium-muted" data-csp-style="s-9b869097">
                Zum Bearbeiten öffnen
                <i class="bi bi-chevron-down" data-csp-style="s-9b335410"></i>
            </span>
        </summary>

        <form
            id="button-appearance-form"
            method="POST"
            action="{{ route('settings.button-appearance.update') }}"
            data-csp-style="s-a24a9e90"
            data-button-appearance-runtime
        >
            @csrf
            @method('PUT')

            <div class="premium-form-grid" data-csp-style="s-f3ddba0f">
                <div data-csp-style="s-2108331b">
                    <div>
                        <strong data-csp-style="s-cf4e872a">Primäre Buttons</strong>
                        <span class="premium-muted">Zum Beispiel Speichern, Weiter und Angebot erstellen.</span>
                    </div>

                    <div class="premium-form-grid">
                        <div class="premium-form-field">
                            <label for="primary_button_background">Hintergrundfarbe</label>
                            <input
                                id="primary_button_background"
                                name="primary_button_background"
                                type="color"
                                value="{{ old('primary_button_background', $buttonTheme['primary_button_background']) }}"
                                data-csp-style="s-0fdab794"
                                required
                            >
                            @error('primary_button_background')
                                <div class="premium-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="premium-form-field">
                            <label for="primary_button_text">Schriftfarbe</label>
                            <input
                                id="primary_button_text"
                                name="primary_button_text"
                                type="color"
                                value="{{ old('primary_button_text', $buttonTheme['primary_button_text']) }}"
                                data-csp-style="s-0fdab794"
                                required
                            >
                            @error('primary_button_text')
                                <div class="premium-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button id="primary-button-preview" class="premium-btn gold" type="button">
                        <i class="bi bi-check2-circle"></i>
                        Primärer Button
                    </button>
                </div>

                <div data-csp-style="s-2108331b">
                    <div>
                        <strong data-csp-style="s-cf4e872a">Sekundäre Buttons</strong>
                        <span class="premium-muted">Zum Beispiel Zurück, Abbrechen und neutrale Aktionen.</span>
                    </div>

                    <div class="premium-form-grid">
                        <div class="premium-form-field">
                            <label for="secondary_button_background">Hintergrundfarbe</label>
                            <input
                                id="secondary_button_background"
                                name="secondary_button_background"
                                type="color"
                                value="{{ old('secondary_button_background', $buttonTheme['secondary_button_background']) }}"
                                data-csp-style="s-0fdab794"
                                required
                            >
                            @error('secondary_button_background')
                                <div class="premium-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="premium-form-field">
                            <label for="secondary_button_text">Schriftfarbe</label>
                            <input
                                id="secondary_button_text"
                                name="secondary_button_text"
                                type="color"
                                value="{{ old('secondary_button_text', $buttonTheme['secondary_button_text']) }}"
                                data-csp-style="s-0fdab794"
                                required
                            >
                            @error('secondary_button_text')
                                <div class="premium-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button id="secondary-button-preview" class="premium-btn" type="button">
                        <i class="bi bi-arrow-left"></i>
                        Sekundärer Button
                    </button>
                </div>
            </div>

            <div data-csp-style="s-d4c80558">
                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-save"></i>
                    Button-Design speichern
                </button>

                <button
                    class="premium-btn"
                    type="submit"
                    name="reset_button_appearance"
                    value="1"
                    data-confirm="Standardfarben der Buttons wiederherstellen?"
                >
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Standard wiederherstellen
                </button>
            </div>
        </form>
    </details>
</section>