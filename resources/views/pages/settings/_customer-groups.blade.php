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
                            onsubmit="return confirm('Kundengruppe wirklich löschen?');"
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

<style>
    .customer-group-settings-header {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:18px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .customer-group-settings-header h2 {
        margin:0;
        font-size:22px;
        font-weight:950;
    }

    .customer-group-settings-header p {
        margin:5px 0 0;
    }

    .customer-group-settings-header-actions {
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:10px;
        flex-wrap:wrap;
    }

    .customer-group-create-panel {
        position:relative;
    }

    .customer-group-create-panel > summary,
    .customer-group-table-summary {
        list-style:none;
    }

    .customer-group-create-panel > summary::-webkit-details-marker,
    .customer-group-table-summary::-webkit-details-marker {
        display:none;
    }

    .customer-group-create-editor {
        position:absolute;
        z-index:20;
        right:0;
        top:calc(100% + 10px);
        width:min(760px,calc(100vw - 56px));
        border:1px solid #eadfce;
        border-radius:18px;
        background:#fff;
        box-shadow:0 24px 70px rgba(40,31,18,.16);
    }

    .customer-group-table {
        border:1px solid #eadfce;
        border-radius:18px;
        overflow:hidden;
        background:#fff;
    }

    .customer-group-table-head,
    .customer-group-table-summary {
        display:grid;
        grid-template-columns:minmax(220px,1.25fr) minmax(260px,1fr) 130px 140px;
        gap:16px;
        align-items:center;
    }

    .customer-group-table-head {
        padding:12px 16px;
        background:#fff7e9;
        color:#7a6445;
        font-size:12px;
        font-weight:950;
        letter-spacing:.06em;
        text-transform:uppercase;
    }

    .customer-group-table-row + .customer-group-table-row {
        border-top:1px solid #efe5d6;
    }

    .customer-group-table-summary {
        padding:14px 16px;
        cursor:pointer;
        transition:background .2s ease;
    }

    .customer-group-table-summary:hover,
    .customer-group-table-row[open] > .customer-group-table-summary {
        background:#fffbf4;
    }

    .customer-group-summary-name,
    .customer-group-summary-colors {
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
        min-width:0;
    }

    .customer-group-summary-colors {
        gap:8px 14px;
    }

    .customer-group-color-value {
        display:inline-flex;
        align-items:center;
        gap:7px;
        color:#6b7280;
        font-size:12px;
        font-weight:850;
        white-space:nowrap;
    }

    .customer-group-color-dot {
        width:18px;
        height:18px;
        border:1px solid rgba(17,24,39,.2);
        border-radius:6px;
        box-shadow:inset 0 0 0 2px rgba(255,255,255,.45);
    }

    .customer-group-summary-count {
        white-space:nowrap;
    }

    .customer-group-summary-action {
        display:flex;
        justify-content:flex-end;
    }

    .customer-group-summary-action .premium-btn {
        pointer-events:none;
    }

    .customer-group-editor {
        padding:18px;
        border-top:1px solid #efe5d6;
        background:#fffcf8;
    }

    .customer-group-editor-preview {
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:14px;
    }

    .customer-group-form-grid {
        display:grid;
        grid-template-columns:minmax(180px,1.2fr) minmax(150px,.7fr) minmax(150px,.7fr) minmax(210px,1fr);
        gap:14px;
        align-items:start;
    }

    .customer-group-description-field {
        grid-column:1 / -1;
    }

    .customer-group-auto-field {
        min-width:0;
    }

    .customer-group-checkbox {
        min-height:46px;
        display:flex;
        align-items:center;
        gap:9px;
        padding:10px 12px;
        border:1px solid #d9c9ae;
        border-radius:12px;
        background:#fff;
        cursor:pointer;
        font-weight:850;
    }

    .customer-group-checkbox input {
        width:18px;
        height:18px;
        accent-color:#d4ad16;
        flex:0 0 auto;
    }

    .customer-group-color-input {
        width:100%;
        height:46px;
        padding:4px;
        border:1px solid #d9c9ae;
        border-radius:12px;
        background:#fff;
        cursor:pointer;
    }

    .customer-group-color-input:disabled {
        opacity:.45;
        cursor:not-allowed;
    }

    .customer-group-editor-actions,
    .customer-group-delete-area {
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
        margin-top:14px;
    }

    .customer-group-delete-area {
        padding-top:14px;
        border-top:1px dashed #e3d6c3;
    }

    @media (max-width:1000px) {
        .customer-group-form-grid {
            grid-template-columns:repeat(2,minmax(0,1fr));
        }

        .customer-group-table-head,
        .customer-group-table-summary {
            grid-template-columns:minmax(190px,1fr) minmax(210px,1fr) 110px 125px;
        }
    }

    @media (max-width:760px) {
        .customer-group-settings-header-actions {
            width:100%;
            justify-content:space-between;
        }

        .customer-group-create-panel {
            position:static;
        }

        .customer-group-create-editor {
            position:fixed;
            left:14px;
            right:14px;
            top:80px;
            width:auto;
            max-height:calc(100vh - 100px);
            overflow:auto;
        }

        .customer-group-table-head {
            display:none;
        }

        .customer-group-table-summary {
            grid-template-columns:1fr auto;
            gap:10px;
        }

        .customer-group-summary-name {
            grid-column:1;
        }

        .customer-group-summary-action {
            grid-column:2;
            grid-row:1;
        }

        .customer-group-summary-colors {
            grid-column:1 / -1;
        }

        .customer-group-summary-count {
            grid-column:1 / -1;
            font-size:13px;
        }

        .customer-group-form-grid {
            grid-template-columns:1fr;
        }

        .customer-group-description-field {
            grid-column:auto;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function automaticTextColor(hex) {
            const value = hex.replace('#', '');
            const red = parseInt(value.substring(0, 2), 16);
            const green = parseInt(value.substring(2, 4), 16);
            const blue = parseInt(value.substring(4, 6), 16);
            const luminance = ((red * 299) + (green * 587) + (blue * 114)) / 1000;

            return luminance >= 150 ? '#111827' : '#FFFFFF';
        }

        document.querySelectorAll('[data-customer-group-editor]').forEach(function (form) {
            const backgroundInput = form.querySelector('[data-background-color]');
            const textInput = form.querySelector('[data-text-color]');
            const automaticInput = form.querySelector('[data-auto-text]');
            const nameInput = form.querySelector('[data-group-name]');
            const container = form.closest('details') || form;
            const preview = container.querySelector('[data-group-preview]') || form.querySelector('[data-group-preview]');

            function updatePreview() {
                if (!backgroundInput || !textInput || !automaticInput || !preview) {
                    return;
                }

                const backgroundColor = backgroundInput.value;
                const textColor = automaticInput.checked
                    ? automaticTextColor(backgroundColor)
                    : textInput.value;

                preview.style.backgroundColor = backgroundColor;
                preview.style.borderColor = backgroundColor;
                preview.style.color = textColor;
                textInput.disabled = automaticInput.checked;

                if (nameInput && nameInput.value.trim() !== '') {
                    preview.textContent = nameInput.value.trim();
                }
            }

            backgroundInput?.addEventListener('input', updatePreview);
            textInput?.addEventListener('input', updatePreview);
            automaticInput?.addEventListener('change', updatePreview);
            nameInput?.addEventListener('input', updatePreview);
            updatePreview();
        });
    });
</script>
