@csrf

@php
    $oldItems = old('items');
    $itemsForForm = $oldItems !== null ? collect($oldItems) : collect($formItems ?? []);

    if ($itemsForForm->isEmpty()) {
        $itemsForForm = collect([['product_id' => '', 'quantity' => '']]);
    }

    $statusValue = old('status', $withdrawal->status ?: \App\Models\BranchWithdrawal::STATUS_OPEN);
    $branchValue = old('branch_name', $withdrawal->branch_name ?: \App\Models\BranchWithdrawal::BRANCH_MAIN);
    $unitLabels = [
        'gram' => 'g',
        'liter' => 'L',
        'piece' => 'Stk.',
    ];
@endphp

@if ($errors->any())
    <div class="premium-alert" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.10);color:#991b1b;">
        <strong>Bitte prüfe die markierten Angaben.</strong>
        <ul style="margin:8px 0 0 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="branch-editor-grid">
    <div class="branch-editor-main">
        <section class="branch-editor-section">
            <div class="branch-editor-section-head">
                <span class="branch-editor-section-icon"><i class="bi bi-shop"></i></span>
                <div>
                    <h2>Filiale und Status</h2>
                    <p>Lege fest, wohin die Ware geht und ob sie bereits ausgegeben wurde.</p>
                </div>
            </div>

            <div class="branch-editor-fields">
                <div class="premium-form-field">
                    <label for="branch_name">Filiale *</label>
                    <select id="branch_name" name="branch_name" class="premium-select" data-sort="false" required>
                        @foreach (\App\Models\BranchWithdrawal::branches() as $value => $label)
                            <option value="{{ $value }}" @selected($branchValue === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('branch_name') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="status">Status *</label>
                    @php
                        $statusOptions = auth()->user()?->isSales()
                            ? [
                                \App\Models\BranchWithdrawal::STATUS_OPEN => 'Offen',
                                \App\Models\BranchWithdrawal::STATUS_IN_PROGRESS => 'In Bearbeitung',
                            ]
                            : (auth()->user()?->isWarehouse()
                                ? [
                                    \App\Models\BranchWithdrawal::STATUS_IN_PROGRESS => 'In Bearbeitung',
                                    \App\Models\BranchWithdrawal::STATUS_ISSUED => 'Ausgegeben',
                                ]
                                : \App\Models\BranchWithdrawal::statusLabels());
                    @endphp
                    <select id="status" name="status" class="premium-select" data-sort="false" required>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="note">Notiz optional</label>
                    <textarea
                        id="note"
                        name="note"
                        rows="3"
                        class="premium-textarea"
                        placeholder="Optionaler Hinweis für den Lagermitarbeiter"
                    >{{ old('note', $withdrawal->note) }}</textarea>
                    @error('note') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        <section class="branch-editor-section branch-items-section">
            <div class="branch-editor-section-head">
                <span class="branch-editor-section-icon"><i class="bi bi-table"></i></span>
                <div>
                    <h2>Produktpositionen</h2>
                    <p>Die nächste leere Position wird automatisch vorbereitet. Beim Tippen bleibt der Cursor im Mengenfeld.</p>
                </div>
            </div>

            <div class="branch-item-table-wrap">
                <table class="branch-item-table">
                    <thead>
                        <tr>
                            <th class="branch-item-position-column">Pos.</th>
                            <th>Produkt</th>
                            <th class="branch-item-quantity-column">Menge</th>
                            <th class="branch-item-action-column">Aktion</th>
                        </tr>
                    </thead>
                    <tbody id="branch-withdrawal-items">
                        @foreach ($itemsForForm as $index => $item)
                            <tr data-item-row>
                                <td class="branch-item-position" data-position>{{ $index + 1 }}</td>
                                <td>
                                    <select name="items[{{ $index }}][product_id]" class="premium-select" data-search="true" aria-label="Produkt Position {{ $index + 1 }}">
                                        <option value="">Produkt auswählen</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->id)>
                                                {{ $product->name }}
                                                @if ($product->product_code)
                                                    — {{ $product->product_code }}
                                                @endif
                                                | {{ number_format((float) $product->available_stock, 2, ',', '.') }} {{ $unitLabels[$product->unit] ?? $product->unit }} verfügbar
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input
                                        name="items[{{ $index }}][quantity]"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        max="999999999"
                                        class="premium-input"
                                        value="{{ $item['quantity'] ?? '' }}"
                                        placeholder="z. B. 50"
                                        aria-label="Menge Position {{ $index + 1 }}"
                                    >
                                </td>
                                <td class="branch-item-action">
                                    <button type="button" class="premium-icon-btn premium-danger remove-branch-item" title="Position entfernen" aria-label="Position entfernen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="branch-item-help">
                <i class="bi bi-info-circle"></i>
                Enter im Mengenfeld öffnet direkt das Produkt-Dropdown der nächsten Position.
            </div>
        </section>
    </div>

    <aside class="branch-editor-side">
        <section class="branch-status-card">
            <div class="branch-status-kicker">Arbeitsablauf</div>
            <h3>Status steuert den Bestand</h3>

            <div class="branch-status-list">
                <div><strong>Offen</strong><span>Ware muss noch vorbereitet werden. Kein Lagerabzug.</span></div>
                <div><strong>In Bearbeitung</strong><span>Lagermitarbeiter stellt die Ware zusammen. Kein Lagerabzug.</span></div>
                <div><strong>Ausgegeben</strong><span>Alle Mengen werden sofort per FIFO aus den Chargen abgezogen.</span></div>
                <div><strong>Storniert</strong><span>Bereits ausgegebene Mengen werden automatisch zurückgebucht.</span></div>
            </div>

            <div class="branch-status-note">
                <i class="bi bi-shield-check"></i>
                Änderungen und Löschungen werden vollständig in einer Datenbanktransaktion ausgeführt.
            </div>
        </section>
    </aside>
</div>

<div class="branch-editor-actions">
    <button type="submit" class="premium-btn gold">
        <i class="bi bi-check2-circle"></i>
        {{ $submitLabel }}
    </button>

    <a href="{{ route(auth()->user()?->isSales() ? 'sales.branch-withdrawals.index' : 'branch-withdrawals.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>

<template id="branch-item-template">
    <tr data-item-row>
        <td class="branch-item-position" data-position></td>
        <td>
            <select data-name="product_id" class="premium-select" data-search="true" aria-label="Produkt">
                <option value="">Produkt auswählen</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">
                        {{ $product->name }}
                        @if ($product->product_code)
                            — {{ $product->product_code }}
                        @endif
                        | {{ number_format((float) $product->available_stock, 2, ',', '.') }} {{ $unitLabels[$product->unit] ?? $product->unit }} verfügbar
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input data-name="quantity" type="number" step="0.001" min="0.001" max="999999999" class="premium-input" placeholder="z. B. 50" aria-label="Menge">
        </td>
        <td class="branch-item-action">
            <button type="button" class="premium-icon-btn premium-danger remove-branch-item" title="Position entfernen" aria-label="Position entfernen">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script src="{{ asset('js/branch-withdrawal-runtime.js') }}" defer></script>