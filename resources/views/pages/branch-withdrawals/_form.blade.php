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
                            : \App\Models\BranchWithdrawal::statusLabels();
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('branch-withdrawal-items');
        const template = document.getElementById('branch-item-template');

        if (!wrapper || !template) return;

        function rows() {
            return Array.from(wrapper.querySelectorAll('[data-item-row]'));
        }

        function fields(row) {
            return {
                product: row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]'),
                quantity: row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]'),
            };
        }

        function complete(row) {
            const current = fields(row);
            return Boolean(current.product?.value && current.quantity?.value);
        }

        function hasData(row) {
            const current = fields(row);
            return Boolean(current.product?.value || current.quantity?.value);
        }

        function reindex() {
            rows().forEach((row, index) => {
                const current = fields(row);
                const position = row.querySelector('[data-position]');

                if (current.product) {
                    current.product.name = `items[${index}][product_id]`;
                    current.product.setAttribute('aria-label', `Produkt Position ${index + 1}`);
                }

                if (current.quantity) {
                    current.quantity.name = `items[${index}][quantity]`;
                    current.quantity.setAttribute('aria-label', `Menge Position ${index + 1}`);
                }

                if (position) position.textContent = String(index + 1);
            });
        }

        function focusProduct(row) {
            window.setTimeout(function () {
                const product = fields(row).product;
                if (!product) return;

                if (product.tomselect) {
                    product.tomselect.focus();
                    product.tomselect.open();
                } else {
                    product.focus();
                }
            }, 80);
        }

        function addRow() {
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('[data-item-row]');
            wrapper.appendChild(fragment);
            reindex();
            bind();
            window.initSearchableSelects?.();
            return row;
        }

        function ensureTrailingRow() {
            const currentRows = rows();
            const last = currentRows[currentRows.length - 1];

            if (!last || complete(last)) {
                return addRow();
            }

            return last;
        }

        function removeExtraEmptyRows() {
            const currentRows = rows();

            currentRows.forEach((row, index) => {
                const isLast = index === currentRows.length - 1;

                if (!isLast && !hasData(row) && currentRows.length > 1) {
                    row.querySelector('select')?.tomselect?.destroy();
                    row.remove();
                }
            });

            reindex();
        }

        function prepareNextRow() {
            ensureTrailingRow();
            removeExtraEmptyRows();
        }

        function nextRowAfter(row) {
            const currentRows = rows();
            const index = currentRows.indexOf(row);
            return index >= 0 ? currentRows[index + 1] : null;
        }

        function bind() {
            rows().forEach((row) => {
                const current = fields(row);
                const removeButton = row.querySelector('.remove-branch-item');

                if (current.product && current.product.dataset.branchBound !== '1') {
                    current.product.dataset.branchBound = '1';
                    current.product.addEventListener('change', prepareNextRow);
                }

                if (current.quantity && current.quantity.dataset.branchBound !== '1') {
                    current.quantity.dataset.branchBound = '1';

                    current.quantity.addEventListener('input', function () {
                        prepareNextRow();
                    });

                    current.quantity.addEventListener('change', function () {
                        prepareNextRow();
                    });

                    current.quantity.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter' || !complete(row)) return;

                        event.preventDefault();
                        prepareNextRow();

                        const next = nextRowAfter(row);
                        if (next) focusProduct(next);
                    });
                }

                if (removeButton && removeButton.dataset.branchBound !== '1') {
                    removeButton.dataset.branchBound = '1';

                    removeButton.addEventListener('click', function () {
                        if (rows().length === 1) {
                            current.product?.tomselect?.clear();
                            if (current.product) current.product.value = '';
                            if (current.quantity) current.quantity.value = '';
                            return;
                        }

                        current.product?.tomselect?.destroy();
                        row.remove();
                        reindex();
                        ensureTrailingRow();
                        removeExtraEmptyRows();
                    });
                }
            });
        }

        bind();
        reindex();
        ensureTrailingRow();
        removeExtraEmptyRows();
    });
</script>

<style>
    .branch-editor-form { display:grid; gap:22px; }
    .branch-editor-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(320px,420px); gap:22px; align-items:start; }
    .branch-editor-main { display:grid; gap:22px; min-width:0; }
    .branch-editor-section,
    .branch-status-card { border:1px solid #d8cbb7; border-radius:22px; background:rgba(255,255,255,.9); box-shadow:0 18px 45px rgba(42,36,25,.08); padding:26px; }
    .branch-editor-section-head { display:flex; gap:14px; padding-bottom:18px; margin-bottom:20px; border-bottom:1px solid #e7dece; }
    .branch-editor-section-icon { width:48px; height:48px; flex:0 0 48px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:#f3e8be; font-size:22px; }
    .branch-editor-section-head h2 { margin:0; font-size:24px; font-weight:950; color:#111; }
    .branch-editor-section-head p { margin:6px 0 0; color:#665f54; font-weight:700; }
    .branch-editor-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
    .branch-editor-fields .full { grid-column:1/-1; }
    .branch-editor-form label { display:inline-flex; margin-bottom:9px; color:#111; font-size:14px; font-weight:900; }
    .branch-editor-form .premium-input,
    .branch-editor-form .premium-select,
    .branch-editor-form .premium-textarea { width:100%; min-height:52px; border:1px solid #c9b895 !important; border-radius:14px !important; background:#fffdf8 !important; color:#111 !important; font-size:16px; font-weight:750; }
    .branch-editor-form .premium-textarea { min-height:110px; resize:vertical; }
    .branch-items-section { padding:0; overflow:hidden; }
    .branch-items-section .branch-editor-section-head { margin:0; padding:24px 26px 18px; }
    .branch-item-table-wrap { width:100%; overflow-x:auto; }
    .branch-item-table { width:100%; min-width:760px; border-collapse:collapse; table-layout:fixed; }
    .branch-item-table th { padding:13px 14px; background:#eee7dc; color:#3a332a; border-bottom:1px solid #8d8069; text-align:left; font-size:12px; font-weight:950; text-transform:uppercase; letter-spacing:.04em; }
    .branch-item-table td { padding:12px 14px; border-bottom:1px solid #e7dece; vertical-align:middle; background:#fff; }
    .branch-item-table tbody tr:last-child td { border-bottom:0; }
    .branch-item-table .premium-input,
    .branch-item-table .premium-select,
    .branch-item-table .ts-wrapper { margin:0 !important; }
    .branch-item-position-column { width:68px; text-align:center !important; }
    .branch-item-position { color:#8a6a00; text-align:center; font-weight:950; }
    .branch-item-quantity-column { width:210px; }
    .branch-item-action-column { width:86px; text-align:center !important; }
    .branch-item-action { text-align:center; }
    .branch-item-help { display:flex; align-items:center; gap:8px; padding:13px 18px; border-top:1px solid #e7dece; background:#faf7f0; color:#665f54; font-size:13px; font-weight:750; }
    .branch-item-help i { color:#8a6a00; }
    .branch-status-card { position:sticky; top:96px; background:radial-gradient(circle at 90% 10%,rgba(212,170,32,.18),transparent 32%),#2d2b25; color:#fff; border-color:rgba(255,232,169,.18); }
    .branch-status-kicker { color:#ffe690; font-size:11px; font-weight:950; text-transform:uppercase; letter-spacing:.1em; }
    .branch-status-card h3 { margin:14px 0 20px; font-size:25px; font-weight:950; }
    .branch-status-list { display:grid; gap:14px; }
    .branch-status-list div { display:grid; gap:4px; padding-bottom:13px; border-bottom:1px solid rgba(255,255,255,.12); }
    .branch-status-list strong { color:#fff; font-weight:950; }
    .branch-status-list span { color:rgba(255,255,255,.72); font-size:13px; font-weight:700; line-height:1.4; }
    .branch-status-note { display:flex; gap:10px; margin-top:20px; padding:14px; border-radius:16px; background:rgba(255,232,169,.10); color:rgba(255,255,255,.82); font-size:13px; font-weight:750; line-height:1.45; }
    .branch-status-note i { color:#ffe690; }
    .branch-editor-actions { display:flex; justify-content:flex-end; gap:12px; flex-wrap:wrap; }
    .branch-editor-actions .premium-btn { min-width:180px; }
    @media (max-width:1150px) { .branch-editor-grid { grid-template-columns:1fr; } .branch-status-card { position:static; } }
    @media (max-width:760px) { .branch-editor-section,.branch-status-card { padding:20px; } .branch-items-section { padding:0; } .branch-editor-fields { grid-template-columns:1fr; } .branch-editor-actions { display:grid; } .branch-editor-actions .premium-btn { width:100%; } }
</style>
