@include('pages.settings._batch-expiry')

<section id="button-appearance" class="premium-card" style="margin-top:22px;scroll-margin-top:24px;">
    <details @if ($errors->hasAny([
        'primary_button_background',
        'primary_button_text',
        'secondary_button_background',
        'secondary_button_text',
    ])) open @endif>
        <summary style="display:flex;align-items:center;justify-content:space-between;gap:16px;cursor:pointer;list-style:none;">
            <span style="display:flex;align-items:center;gap:12px;">
                <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;">
                    <i class="bi bi-palette"></i>
                </span>
                <span>
                    <strong style="display:block;font-size:22px;font-weight:950;">Button-Design</strong>
                    <span class="premium-muted">Farben für Speichern, Weiter, Zurück und andere Standardbuttons.</span>
                </span>
            </span>

            <span class="premium-muted" style="font-weight:900;">
                Zum Bearbeiten öffnen
                <i class="bi bi-chevron-down" style="margin-left:6px;"></i>
            </span>
        </summary>

        <form
            id="button-appearance-form"
            method="POST"
            action="{{ route('settings.button-appearance.update') }}"
            style="display:grid;gap:18px;margin-top:20px;padding-top:20px;border-top:1px solid #e7dece;"
            data-button-appearance-runtime
        >
            @csrf
            @method('PUT')

            <div class="premium-form-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));">
                <div style="display:grid;gap:14px;padding:16px;border:1px solid #e7dece;border-radius:16px;background:#fffdf8;">
                    <div>
                        <strong style="display:block;font-size:17px;font-weight:950;">Primäre Buttons</strong>
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
                                style="width:100%;height:50px;padding:4px;border:1px solid #c9b895;border-radius:12px;background:#fff;cursor:pointer;"
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
                                style="width:100%;height:50px;padding:4px;border:1px solid #c9b895;border-radius:12px;background:#fff;cursor:pointer;"
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

                <div style="display:grid;gap:14px;padding:16px;border:1px solid #e7dece;border-radius:16px;background:#fffdf8;">
                    <div>
                        <strong style="display:block;font-size:17px;font-weight:950;">Sekundäre Buttons</strong>
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
                                style="width:100%;height:50px;padding:4px;border:1px solid #c9b895;border-radius:12px;background:#fff;cursor:pointer;"
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
                                style="width:100%;height:50px;padding:4px;border:1px solid #c9b895;border-radius:12px;background:#fff;cursor:pointer;"
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

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
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