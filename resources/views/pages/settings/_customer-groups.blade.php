@php
    $createBag = $errors->getBag('customerGroupCreate');
    $createHasErrors = $createBag->any();
    $createAutoText = $createHasErrors ? (string) old('text_color_auto', '1') === '1' : true;
    $createBackground = $createHasErrors ? old('color', '#475569') : '#475569';
    $createTextColor = $createHasErrors ? old('text_color', '#FFFFFF') : '#FFFFFF';
@endphp

<section id="customer-groups" class="premium-card customer-group-settings" style="margin-top:22px;scroll-margin-top:24px;">
    <div class="customer-group-settings-header">
        <div>
            <h2>Kundengruppen</h2>
            <p class="premium-muted">Kompakte Übersicht. Eine Gruppe wird nur zum Bearbeiten aufgeklappt.</p>
        </div>

        <div class="customer-group-settings-header-actions">
            <span class="premium-badge">{{ $customerGroups->count() }} Gruppe(n)</span>

            <details class="customer-group-create-panel" @if ($createHasErrors) open @endif>
                <summary class="premium-btn gold">
                    <i class="bi bi-plus-lg"></i>
                    Neue Gruppe
                </summary>

                <div class="customer-group-editor customer-group-create-editor">
                    <form
                        method="POST"
                        action="{{ route('customer-groups.store') }}"
                        data-customer-group-editor
                    >
                        @csrf

                        <div class="customer-group-editor-preview">
                            <span
                                class="premium-badge"
                                data-group-preview
                                style="background-color:{{ $createBackground }};color:{{ $createAutoText ? '#FFFFFF' : $createTextColor }};border-color:{{ $createBackground }};"
                            >Neue Gruppe</span>
                            <span class="premium-muted">Live-Vorschau</span>
                        </div>

                        <div class="customer-group-form-grid">
                            <div class="premium-form-field">
                                <label for="customer_group_create_name">Name *</label>
                                <input
                                    id="customer_group_create_name"
                                    name="name"
                                    class="premium-input"
                                    maxlength="100"
                                    value="{{ $createHasErrors ? old('name') : '' }}"
                                    data-group-name
                                    required
                                >
                                @error('name', 'customerGroupCreate')
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="customer_group_create_color">Hintergrundfarbe *</label>
                                <input
                                    id="customer_group_create_color"
                                    name="color"
                                    type="color"
                                    value="{{ $createBackground }}"
                                    class="customer-group-color-input"
                                    data-background-color
                                    required
                                >
                                @error('color', 'customerGroupCreate')
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="customer_group_create_text_color">Schriftfarbe *</label>
                                <input type="hidden" name="text_color_auto" value="0">
                                <input
                                    id="customer_group_create_text_color"
                                    name="text_color"
                                    type="color"
                                    value="{{ $createTextColor }}"
                                    class="customer-group-color-input"
                                    data-text-color
                                    required
                                >
                                @error('text_color', 'customerGroupCreate')
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field customer-group-auto-field">
                                <label for="customer_group_create_text_auto">Schrift automatisch</label>
                                <label class="customer-group-checkbox">
                                    <input
                                        id="customer_group_create_text_auto"
                                        name="text_color_auto"
                                        type="checkbox"
                                        value="1"
                                        data-auto-text
                                        @checked($createAutoText)
                                    >
                                    <span>Automatisch gut lesbar wählen</span>
                                </label>
                                @error('text_color_auto', 'customerGroupCreate')
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field customer-group-description-field">
                                <label for="customer_group_create_description">Beschreibung</label>
                                <textarea
                                    id="customer_group_create_description"
                                    name="description"
                                    class="premium-textarea"
                                    rows="2"
                                    maxlength="1000"
                                >{{ $createHasErrors ? old('description') : '' }}</textarea>
                                @error('description', 'customerGroupCreate')
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="customer-group-editor-actions">
                            <button class="premium-btn gold" type="submit">
                                <i class="bi bi-plus-lg"></i>
                                Gruppe hinzufügen
                            </button>
                        </div>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <div class="customer-group-table" role="table" aria-label="Kundengruppen">
        <div class="customer-group-table-head" role="row">
            <span role="columnheader">Gruppe</span>
            <span role="columnheader">Farben</span>
            <span role="columnheader">Kunden</span>
            <span role="columnheader">Aktion</span>
        </div>

        @foreach ($customerGroups as $group)
            @php
                $updateBagName = 'customerGroupUpdate' . $group->id;
                $updateBag = $errors->getBag($updateBagName);
                $updateHasErrors = $updateBag->any();
                $automaticText = $updateHasErrors
                    ? (string) old('text_color_auto', $group->usesAutomaticTextColor() ? '1' : '0') === '1'
                    : $group->usesAutomaticTextColor();
                $backgroundColor = $updateHasErrors ? old('color', $group->displayColor()) : $group->displayColor();
                $manualTextColor = $updateHasErrors ? old('text_color', $group->textColor()) : $group->textColor();
                $deleteLocked = $group->customers_count > 0 || $customerGroups->count() <= 1;
            @endphp

            <details class="customer-group-table-row" role="rowgroup" @if ($updateHasErrors) open @endif>
                <summary class="customer-group-table-summary" role="row">
                    <span class="customer-group-summary-name" role="cell">
                        <span
                            class="premium-badge"
                            data-group-preview
                            style="{{ $group->badgeStyle() }}"
                        >{{ $group->name }}</span>
                        <span class="premium-code">{{ $group->slug }}</span>
                    </span>

                    <span class="customer-group-summary-colors" role="cell">
                        <span class="customer-group-color-value">
                            <span class="customer-group-color-dot" style="background:{{ $group->displayColor() }};"></span>
                            {{ $group->displayColor() }}
                        </span>
                        <span class="customer-group-color-value">
                            <span class="customer-group-color-dot" style="background:{{ $group->textColor() }};"></span>
                            {{ $group->usesAutomaticTextColor() ? 'Schrift automatisch' : $group->textColor() }}
                        </span>
                    </span>

                    <strong class="customer-group-summary-count" role="cell">
                        {{ $group->customers_count }} Kunde(n)
                    </strong>

                    <span class="customer-group-summary-action" role="cell">
                        <span class="premium-btn">
                            <i class="bi bi-pencil"></i>
                            Bearbeiten
                        </span>
                    </span>
                </summary>

                <div class="customer-group-editor">
                    <form
                        method="POST"
                        action="{{ route('customer-groups.update', $group) }}"
                        data-customer-group-editor
                    >
                        @csrf
                        @method('PUT')

                        <div class="customer-group-form-grid">
                            <div class="premium-form-field">
                                <label for="customer_group_name_{{ $group->id }}">Name *</label>
                                <input
                                    id="customer_group_name_{{ $group->id }}"
                                    name="name"
                                    class="premium-input"
                                    maxlength="100"
                                    value="{{ $updateHasErrors ? old('name', $group->name) : $group->name }}"
                                    data-group-name
                                    required
                                >
                                @error('name', $updateBagName)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="customer_group_color_{{ $group->id }}">Hintergrundfarbe *</label>
                                <input
                                    id="customer_group_color_{{ $group->id }}"
                                    name="color"
                                    type="color"
                                    value="{{ $backgroundColor }}"
                                    class="customer-group-color-input"
                                    data-background-color
                                    required
                                >
                                @error('color', $updateBagName)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="customer_group_text_color_{{ $group->id }}">Schriftfarbe *</label>
                                <input type="hidden" name="text_color_auto" value="0">
                                <input
                                    id="customer_group_text_color_{{ $group->id }}"
                                    name="text_color"
                                    type="color"
                                    value="{{ $manualTextColor }}"
                                    class="customer-group-color-input"
                                    data-text-color
                                    required
                                >
                                @error('text_color', $updateBagName)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field customer-group-auto-field">
                                <label for="customer_group_text_auto_{{ $group->id }}">Schrift automatisch</label>
                                <label class="customer-group-checkbox">
                                    <input
                                        id="customer_group_text_auto_{{ $group->id }}"
                                        name="text_color_auto"
                                        type="checkbox"
                                        value="1"
                                        data-auto-text
                                        @checked($automaticText)
                                    >
                                    <span>Automatisch gut lesbar wählen</span>
                                </label>
                                @error('text_color_auto', $updateBagName)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field customer-group-description-field">
                                <label for="customer_group_description_{{ $group->id }}">Beschreibung</label>
                                <textarea
                                    id="customer_group_description_{{ $group->id }}"
                                    name="description"
                                    class="premium-textarea"
                                    rows="2"
                                    maxlength="1000"
                                >{{ $updateHasErrors ? old('description', $group->description) : $group->description }}</textarea>
                                @error('description', $updateBagName)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="customer-group-editor-actions">
                            <button class="premium-btn gold" type="submit">
                                <i class="bi bi-save"></i>
                                Änderungen speichern
                            </button>
                        </div>
                    </form>

                    <div class="customer-group-delete-area">
                        <form
                            method="POST"
                            action="{{ route('customer-groups.destroy', $group) }}"
                            data-confirm="Kundengruppe wirklich löschen?"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                class="premium-btn"
                                type="submit"
                                @disabled($deleteLocked)
                                title="{{ $group->customers_count > 0 ? 'Zuerst alle Kunden einer anderen Gruppe zuweisen.' : ($customerGroups->count() <= 1 ? 'Die letzte Gruppe kann nicht gelöscht werden.' : 'Kundengruppe löschen') }}"
                                style="background:#991b1b;{{ $deleteLocked ? 'opacity:.45;cursor:not-allowed;' : '' }}"
                            >
                                <i class="bi bi-trash"></i>
                                Gruppe löschen
                            </button>
                        </form>

                        @if ($group->customers_count > 0)
                            <span class="premium-muted">Löschen ist gesperrt, solange Kunden dieser Gruppe zugeordnet sind.</span>
                        @elseif ($customerGroups->count() <= 1)
                            <span class="premium-muted">Die letzte Kundengruppe kann nicht gelöscht werden.</span>
                        @endif
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</section>

{{-- CSP static styles moved to public/css/csp-static-bulk.css: resources/views/pages/settings/_customer-groups.blade.php --}}