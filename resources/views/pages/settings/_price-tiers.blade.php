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

<section id="price-tiers" class="premium-card price-tier-settings" style="margin-top:22px;scroll-margin-top:24px;">
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

            <div class="premium-alert" style="background:#fff8df;border-color:#e3ca6e;color:#5f4a00;">
                <strong>Wichtig:</strong> Die Bereiche müssen direkt aneinander anschließen. Bei der letzten Preisstufe darf „Bis Gramm“ leer bleiben; sie gilt dann ohne Obergrenze. Bestehende Preise bleiben beim Bearbeiten erhalten.
            </div>

            @error('tiers')
                <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">
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

<style>
    .price-tier-summary {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:18px;
        cursor:pointer;
        list-style:none;
    }

    .price-tier-summary::-webkit-details-marker {
        display:none;
    }

    .price-tier-summary-title,
    .price-tier-summary-meta {
        display:flex;
        align-items:center;
        gap:12px;
    }

    .price-tier-summary-title > span:last-child {
        display:grid;
        gap:4px;
    }

    .price-tier-summary-title strong {
        font-size:22px;
        font-weight:950;
    }

    .price-tier-summary-icon {
        width:44px;
        height:44px;
        display:grid;
        place-items:center;
        border-radius:14px;
        background:#f3e8be;
        color:#7b5c00;
        font-size:20px;
    }

    .price-tier-summary-meta {
        font-weight:900;
        white-space:nowrap;
    }

    .price-tier-form {
        display:grid;
        gap:18px;
        margin-top:20px;
        padding-top:20px;
        border-top:1px solid #e7dece;
    }

    .price-tier-table-wrap {
        overflow-x:auto;
        border:1px solid #e7dece;
        border-radius:18px;
        background:#fff;
    }

    .price-tier-table {
        width:100%;
        min-width:850px;
        border-collapse:collapse;
    }

    .price-tier-table th,
    .price-tier-table td {
        padding:12px;
        text-align:left;
        vertical-align:middle;
        border-bottom:1px solid #eee5d8;
    }

    .price-tier-table th {
        background:#fff8ec;
        color:#6d5c42;
        font-size:12px;
        font-weight:950;
        letter-spacing:.05em;
        text-transform:uppercase;
    }

    .price-tier-table tbody tr:last-child td {
        border-bottom:0;
    }

    .price-tier-table .premium-input {
        min-width:140px;
    }

    .price-tier-help {
        margin-top:6px;
        font-size:11px;
        white-space:nowrap;
    }

    .price-tier-range {
        white-space:nowrap;
    }

    .price-tier-remove {
        min-height:42px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        border:1px solid #b91c1c;
        border-radius:12px;
        background:#fff;
        color:#991b1b;
        padding:9px 12px;
        font-weight:900;
        cursor:pointer;
    }

    .price-tier-remove:hover {
        background:#991b1b;
        color:#fff;
    }

    .price-tier-actions {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }

    @media (max-width:760px) {
        .price-tier-summary,
        .price-tier-summary-meta {
            align-items:flex-start;
            flex-direction:column;
        }

        .price-tier-actions .premium-btn {
            width:100%;
        }
    }
</style>

<script src="{{ asset('js/price-tiers-runtime.js') }}" defer></script>
