@php
    $priceTierDefinitions = \App\Models\PriceTierDefinition::query()->ordered()->get();
    $submittedTiers = old('tiers');
    $tierRows = is_array($submittedTiers)
        ? collect($submittedTiers)
        : $priceTierDefinitions->map(fn ($tier) => [
            'id' => $tier->id,
            'label' => $tier->label,
            'min_grams' => $tier->min_grams,
            'max_grams' => $tier->max_grams,
        ]);
    $nextTierIndex = ((int) $tierRows->keys()->map(fn ($key) => (int) $key)->max()) + 1;
@endphp

<section id="price-tiers" class="premium-card price-tier-settings" data-csp-style="s-1c4a2677">
    <details @if ($errors->has('tiers') || $errors->has('tiers.*')) open @endif>
        <summary class="price-tier-summary">
            <span class="price-tier-summary-title">
                <span class="price-tier-summary-icon">
                    <i class="bi bi-layers"></i>
                </span>
                <span>
                    <strong>Preisstufen</strong>
                    <span class="premium-muted">Gewichtsbereiche für Preise, Angebote und Rechnungen verwalten.</span>
                </span>
            </span>

            <span class="price-tier-summary-meta">
                <span class="premium-badge">{{ $tierRows->count() }} Stufe(n)</span>
                <span>Zum Bearbeiten öffnen <i class="bi bi-chevron-down"></i></span>
            </span>
        </summary>

        <form
            id="price-tier-form"
            method="POST"
            action="{{ route('price-tiers.update') }}"
            class="price-tier-form"
            data-price-tiers-runtime
            data-next-index="{{ $nextTierIndex }}"
        >
            @csrf
            @method('PUT')

            <div class="premium-alert" data-csp-style="s-778013e7">
                <strong>Wichtig:</strong> Die Bereiche müssen direkt aneinander anschließen. Bei der letzten Preisstufe darf „Bis Gramm“ leer bleiben; sie gilt dann ohne Obergrenze. Bestehende Preise bleiben beim Bearbeiten erhalten.
            </div>

            @error('tiers')
                <div class="premium-alert" data-csp-style="s-b2a82328">
                    {{ $message }}
                </div>
            @enderror

            <div class="price-tier-table-wrap">
                <table class="price-tier-table">
                    <thead>
                        <tr>
                            <th>Bezeichnung</th>
                            <th>Von Gramm</th>
                            <th>Bis Gramm</th>
                            <th>Bereich</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody id="price-tier-rows">
                        @foreach ($tierRows as $index => $tier)
                            <tr data-price-tier-row>
                                <td>
                                    <input
                                        type="hidden"
                                        name="tiers[{{ $index }}][id]"
                                        value="{{ $tier['id'] ?? '' }}"
                                    >
                                    <input
                                        name="tiers[{{ $index }}][label]"
                                        class="premium-input"
                                        maxlength="100"
                                        value="{{ $tier['label'] ?? '' }}"
                                        placeholder="z. B. 250g"
                                        data-tier-label
                                        required
                                    >
                                    @error("tiers.$index.label")
                                        <div class="premium-error">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input
                                        name="tiers[{{ $index }}][min_grams]"
                                        type="number"
                                        min="1"
                                        step="1"
                                        class="premium-input"
                                        value="{{ $tier['min_grams'] ?? '' }}"
                                        data-tier-min
                                        required
                                    >
                                    @error("tiers.$index.min_grams")
                                        <div class="premium-error">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input
                                        name="tiers[{{ $index }}][max_grams]"
                                        type="number"
                                        min="1"
                                        step="1"
                                        class="premium-input"
                                        value="{{ $tier['max_grams'] ?? '' }}"
                                        placeholder="Leer = unendlich"
                                        data-tier-max
                                    >
                                    <div class="premium-muted price-tier-help">Nur bei der letzten Stufe leer lassen.</div>
                                    @error("tiers.$index.max_grams")
                                        <div class="premium-error">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <strong class="price-tier-range" data-tier-range>
                                        @if (filled($tier['max_grams'] ?? null))
                                            {{ $tier['min_grams'] ?? '–' }}–{{ $tier['max_grams'] }} Gramm
                                        @else
                                            ab {{ $tier['min_grams'] ?? '–' }} Gramm
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="price-tier-remove"
                                        data-remove-price-tier
                                        title="Preisstufe entfernen"
                                    >
                                        <i class="bi bi-trash"></i>
                                        Entfernen
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="price-tier-actions">
                <button class="premium-btn" type="button" id="add-price-tier">
                    <i class="bi bi-plus-lg"></i>
                    Preisstufe hinzufügen
                </button>

                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-save"></i>
                    Alle Preisstufen speichern
                </button>
            </div>
        </form>
    </details>
</section>

<template id="price-tier-row-template">
    <tr data-price-tier-row>
        <td>
            <input type="hidden" data-field="id" value="">
            <input class="premium-input" maxlength="100" placeholder="z. B. 5000g+" data-field="label" data-tier-label required>
        </td>
        <td>
            <input type="number" min="1" step="1" class="premium-input" data-field="min_grams" data-tier-min required>
        </td>
        <td>
            <input type="number" min="1" step="1" class="premium-input" placeholder="Leer = unendlich" data-field="max_grams" data-tier-max>
            <div class="premium-muted price-tier-help">Nur bei der letzten Stufe leer lassen.</div>
        </td>
        <td>
            <strong class="price-tier-range" data-tier-range>ab – Gramm</strong>
        </td>
        <td>
            <button type="button" class="price-tier-remove" data-remove-price-tier title="Preisstufe entfernen">
                <i class="bi bi-trash"></i>
                Entfernen
            </button>
        </td>
    </tr>
</template>



<script src="{{ asset('js/price-tiers-runtime.js') }}" defer></script>