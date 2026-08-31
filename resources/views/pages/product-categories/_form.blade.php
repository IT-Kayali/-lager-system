@csrf

<div class="category-editor-grid">
    <div class="category-editor-main">
        <div class="category-editor-section">
            <div class="category-editor-section-head">
                <span class="category-editor-section-icon">
                    <i class="bi bi-tag"></i>
                </span>

                <div>
                    <h2>Basisdaten</h2>
                    <p>Name, Priorität, Slug und Beschreibung der Kategorie.</p>
                </div>
            </div>

            <div class="category-editor-fields">
                <div class="premium-form-field">
                    <label for="name">Kategoriename *</label>
                    <input id="name" name="name" class="premium-input" value="{{ old('name', $category->name) }}" required>
                    @error('name') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="priority">Priorität *</label>
                    <input
                        id="priority"
                        name="priority"
                        type="number"
                        min="1"
                        step="1"
                        class="premium-input"
                        value="{{ old('priority', $category->priority) }}"
                        required
                    >
                    <div class="category-priority-hint">1 = zuerst auf Angebot, Rechnung und Lieferscheinen. Andere Kategorien werden beim Verschieben automatisch angepasst.</div>
                    @error('priority') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="slug">Slug optional</label>
                    <input id="slug" name="slug" class="premium-input" value="{{ old('slug', $category->slug) }}" placeholder="wird automatisch erzeugt">
                    @error('slug') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="description">Beschreibung optional</label>
                    <textarea id="description" name="description" rows="5" class="premium-textarea">{{ old('description', $category->description) }}</textarea>
                    @error('description') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="category-editor-side">
        <div class="category-editor-section category-appearance-section">
            <div class="category-editor-section-head">
                <span class="category-editor-section-icon">
                    <i class="bi bi-palette"></i>
                </span>

                <div>
                    <h2>Darstellung</h2>
                    <p>Farbe, Status und Preislogik festlegen.</p>
                </div>
            </div>

            <div class="premium-form-field">
                <label for="color">Farbe</label>

                <div class="category-color-control">
                    <input
                        id="color"
                        name="color"
                        type="color"
                        class="category-color-input"
                        value="{{ old('color', $category->color ?: '#d4af37') }}"
                    >

                    <div class="category-color-preview">
                        <span style="background: {{ old('color', $category->color ?: '#d4af37') }};"></span>
                        <strong>{{ old('color', $category->color ?: '#d4af37') }}</strong>
                    </div>
                </div>

                @error('color') <div class="premium-error">{{ $message }}</div> @enderror
            </div>

            <div class="premium-form-field">
                <label>Status</label>

                <label class="category-active-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>

                    <span class="category-active-switch"></span>

                    <span class="category-active-copy">
                        <strong>Aktiv</strong>
                        <small>Kategorie ist in Formularen auswählbar.</small>
                    </span>
                </label>
            </div>

            <div class="premium-form-field">
                <label>Preislogik</label>

                <label class="category-active-toggle">
                    <input type="hidden" name="price_tiers_enabled" value="0">
                    <input type="checkbox" name="price_tiers_enabled" value="1" @checked(old('price_tiers_enabled', $category->price_tiers_enabled ?? true))>

                    <span class="category-active-switch"></span>

                    <span class="category-active-copy">
                        <strong>Preisstaffel aktiv</strong>
                        <small>Aktiv: bestehende Preisstaffeln pro Produkt und Kundengruppe. Aus: freie Mengenregeln pro Produkt und Kundengruppe.</small>
                    </span>
                </label>
                @error('price_tiers_enabled') <div class="premium-error">{{ $message }}</div> @enderror
            </div>

            <div class="category-preview-card">
                <div class="category-preview-kicker">Vorschau</div>

                <div class="category-preview-name">
                    <span style="background: {{ old('color', $category->color ?: '#d4af37') }};"></span>
                    {{ old('name', $category->name ?: 'Neue Kategorie') }}
                </div>

                <div class="category-preview-description">
                    Priorität {{ old('priority', $category->priority ?: '—') }} · {{ old('description', $category->description ?: 'Beschreibung der Kategorie') }}
                </div>
            </div>
        </div>
    </aside>
</div>

<div class="category-form-actions">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        {{ $submitLabel }}
    </button>

    <a href="{{ route('product-categories.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>

<style>
    .category-editor-card {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .category-editor-form {
        display: grid;
        gap: 22px;
    }

    .category-editor-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 420px);
        gap: 22px;
        align-items: start;
    }

    .category-editor-section {
        border: 1px solid #d8cbb7;
        border-radius: 22px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
        padding: 26px;
    }

    .category-editor-section-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding-bottom: 18px;
        margin-bottom: 20px;
        border-bottom: 1px solid #e7dece;
    }

    .category-editor-section-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #f3e8be;
        color: #111111;
        font-size: 22px;
    }

    .category-editor-section-head h2 {
        margin: 0;
        color: #111111;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .category-editor-section-head p {
        margin: 6px 0 0;
        color: #665f54;
        font-size: 14px;
        font-weight: 700;
    }

    .category-editor-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .category-editor-fields .full {
        grid-column: 1 / -1;
    }

    .category-editor-form label {
        display: inline-flex;
        align-items: center;
        margin-bottom: 9px;
        color: #111111;
        font-size: 14px;
        font-weight: 900;
    }

    .category-editor-form .premium-input,
    .category-editor-form .premium-textarea {
        width: 100%;
        min-height: 52px;
        border: 1px solid #c9b895 !important;
        border-radius: 14px !important;
        background: #fffdf8 !important;
        color: #111111 !important;
        font-size: 16px;
        font-weight: 750;
    }

    .category-editor-form .premium-input:focus,
    .category-editor-form .premium-textarea:focus {
        border-color: #d4aa20 !important;
        box-shadow: 0 0 0 4px rgba(212, 170, 32, .18) !important;
        outline: none !important;
    }

    .category-editor-form .premium-textarea {
        min-height: 150px;
        resize: vertical;
    }

    .category-priority-hint {
        margin-top: 7px;
        color: #665f54;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    .category-color-control {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
    }

    .category-color-input {
        width: 64px;
        height: 52px;
        border: 1px solid #c9b895;
        border-radius: 14px;
        background: #fffdf8;
        cursor: pointer;
        padding: 6px;
    }

    .category-color-preview {
        min-height: 52px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid #c9b895;
        border-radius: 14px;
        background: #fffdf8;
    }

    .category-color-preview span {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        box-shadow: 0 0 0 5px rgba(0,0,0,.05);
    }

    .category-color-preview strong {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 13px;
        font-weight: 950;
    }

    .category-active-toggle {
        display: flex !important;
        align-items: center !important;
        gap: 14px;
        padding: 16px;
        border: 1px solid #c9b895;
        border-radius: 18px;
        background: #fffdf8;
        cursor: pointer;
    }

    .category-active-toggle input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .category-active-switch {
        position: relative;
        width: 54px;
        height: 30px;
        flex: 0 0 54px;
        border-radius: 999px;
        background: #e7dece;
        transition: background .16s ease;
    }

    .category-active-switch::after {
        content: "";
        position: absolute;
        top: 4px;
        left: 4px;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,.18);
        transition: transform .16s ease;
    }

    .category-active-toggle input[type="checkbox"]:checked + .category-active-switch {
        background: #d4aa20;
    }

    .category-active-toggle input[type="checkbox"]:checked + .category-active-switch::after {
        transform: translateX(24px);
    }

    .category-active-copy {
        display: grid;
        gap: 3px;
        min-width: 0;
    }

    .category-active-copy strong {
        color: #111111;
        font-size: 15px;
        font-weight: 950;
    }

    .category-active-copy small {
        color: #665f54;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
    }

    .category-preview-card {
        margin-top: 20px;
        padding: 18px;
        border-radius: 18px;
        background: #2d2b25;
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
    }

    .category-preview-kicker {
        color: #ffe690;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .category-preview-name {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        font-size: 20px;
        font-weight: 950;
    }

    .category-preview-name span {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        flex: 0 0 16px;
    }

    .category-preview-description {
        margin-top: 9px;
        color: rgba(255,255,255,.72);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.45;
    }

    .category-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .category-form-actions .premium-btn {
        min-width: 170px;
    }

    @media (max-width: 1150px) {
        .category-editor-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .category-editor-section {
            padding: 20px;
        }

        .category-editor-fields {
            grid-template-columns: 1fr;
        }

        .category-form-actions {
            display: grid;
        }

        .category-form-actions .premium-btn {
            width: 100%;
        }
    }
</style>
