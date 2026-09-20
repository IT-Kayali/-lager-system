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