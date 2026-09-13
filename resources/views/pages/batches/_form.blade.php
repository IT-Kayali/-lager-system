@csrf

@php
    $receivedValue = old(
        'received_at',
        ! empty($batch->received_at)
            ? \Illuminate\Support\Carbon::parse($batch->received_at)->format('Y-m-d')
            : now()->format('Y-m-d')
    );

    $expiresValue = old(
        'expires_at',
        ! empty($batch->expires_at)
            ? \Illuminate\Support\Carbon::parse($batch->expires_at)->format('Y-m-d')
            : \Illuminate\Support\Carbon::parse($receivedValue)
                ->addMonthsNoOverflow($defaultBatchExpiryMonths ?? 24)
                ->format('Y-m-d')
    );

    $selectedProduct = ($products ?? collect())->firstWhere('id', (int) old('product_id', $batch->product_id ?? 0));
@endphp

<div class="batch-editor-grid">
    <div class="batch-editor-main">
        <section class="batch-editor-section">
            <div class="batch-editor-section-head">
                <span class="batch-editor-section-icon">
                    <i class="bi bi-layers"></i>
                </span>

                <div>
                    <h2>Chargendaten</h2>
                    <p>Produkt, Batchnummer und Bestand erfassen.</p>
                </div>
            </div>

            <div class="batch-editor-fields">
                <div class="premium-form-field full">
                    <label for="product_id">Produkt *</label>
                    <select id="product_id" name="product_id" class="premium-select" required>
                        <option value="">Produkt auswählen</option>
                        @foreach (($products ?? collect()) as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id', $batch->product_id ?? '') === (string) $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="batch_number">Batchnummer</label>
                    <input
                        id="batch_number"
                        name="batch_number"
                        class="premium-input"
                        value="{{ old('batch_number', $batch->batch_number ?? '') }}"
                        placeholder="wird sonst automatisch vergeben"
                    >
                    @error('batch_number') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="quantity">Menge *</label>
                    <input
                        id="quantity"
                        name="quantity"
                        type="number"
                        step="0.01"
                        min="0"
                        class="premium-input"
                        value="{{ old('quantity', $batch->quantity ?? '') }}"
                        placeholder="z. B. 1000"
                        required
                    >
                    @error('quantity') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        <section class="batch-editor-section">
            <div class="batch-editor-section-head">
                <span class="batch-editor-section-icon">
                    <i class="bi bi-calendar3"></i>
                </span>

                <div>
                    <h2>Datum & FIFO</h2>
                    <p>Wareneingang steuert die FIFO-Reihenfolge: ältere Chargen werden zuerst entnommen.</p>
                </div>
            </div>

            <div class="batch-editor-fields">
                <div class="premium-form-field">
                    <label for="received_at">Wareneingangsdatum *</label>
                    <input
                        id="received_at"
                        name="received_at"
                        type="date"
                        class="premium-input"
                        value="{{ $receivedValue }}"
                        required
                    >
                    @error('received_at') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="expires_at">Ablaufdatum</label>
                    <input
                        id="expires_at"
                        name="expires_at"
                        type="date"
                        class="premium-input"
                        value="{{ $expiresValue }}"
                        data-batch-expiry-runtime
                        data-default-months="{{ $defaultBatchExpiryMonths ?? 24 }}"
                        data-auto-expiry="{{ $batch->exists ? '0' : '1' }}"
                    >
                    <div class="premium-muted" style="margin-top:8px;line-height:1.45;">
                        Wird bei neuen Chargen automatisch {{ $defaultBatchExpiryMonths ?? 24 }} Monate nach dem Wareneingang gesetzt. Du kannst das Datum jederzeit manuell früher oder später wählen. Das Ablaufdatum ist nur eine Information und sperrt den Verkauf nicht.
                    </div>
                    @error('expires_at') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>
    </div>

    <aside class="batch-editor-side">
        <section class="batch-summary-card">
            <div class="batch-summary-kicker">Vorschau</div>

            <div class="batch-summary-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <h3>{{ $selectedProduct?->name ?: 'Produkt auswählen' }}</h3>

            <div class="batch-summary-list">
                <div>
                    <span>Batchnummer</span>
                    <strong>{{ old('batch_number', $batch->batch_number ?? '') ?: 'Automatisch' }}</strong>
                </div>

                <div>
                    <span>Menge</span>
                    <strong>{{ old('quantity', $batch->quantity ?? '') ?: '0' }}</strong>
                </div>

                <div>
                    <span>Wareneingang</span>
                    <strong>{{ $receivedValue ? \Illuminate\Support\Carbon::parse($receivedValue)->format('d.m.Y') : '—' }}</strong>
                </div>

                <div>
                    <span>Ablaufdatum</span>
                    <strong>{{ $expiresValue ? \Illuminate\Support\Carbon::parse($expiresValue)->format('d.m.Y') : '—' }}</strong>
                </div>
            </div>

            <div class="batch-fifo-note">
                <i class="bi bi-info-circle"></i>
                FIFO nutzt ausschließlich das Wareneingangsdatum. Das Ablaufdatum ist informativ und hat keine Auswirkung auf Verkauf oder Entnahme.
            </div>
        </section>
    </aside>
</div>

<div class="batch-form-actions">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('batches.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>

<style>
    .batch-editor-card {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .batch-editor-form {
        display: grid;
        gap: 22px;
    }

    .batch-editor-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 420px);
        gap: 22px;
        align-items: start;
    }

    .batch-editor-main {
        display: grid;
        gap: 22px;
    }

    .batch-editor-section,
    .batch-summary-card {
        border: 1px solid #d8cbb7;
        border-radius: 22px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
        padding: 26px;
    }

    .batch-editor-section-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding-bottom: 18px;
        margin-bottom: 20px;
        border-bottom: 1px solid #e7dece;
    }

    .batch-editor-section-icon {
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

    .batch-editor-section-head h2 {
        margin: 0;
        color: #111111;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .batch-editor-section-head p {
        margin: 6px 0 0;
        color: #665f54;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.4;
    }

    .batch-editor-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .batch-editor-fields .full {
        grid-column: 1 / -1;
    }

    .batch-editor-form label {
        display: inline-flex;
        align-items: center;
        margin-bottom: 9px;
        color: #111111;
        font-size: 14px;
        font-weight: 900;
    }

    .batch-editor-form .premium-input,
    .batch-editor-form .premium-select {
        width: 100%;
        min-height: 52px;
        border: 1px solid #c9b895 !important;
        border-radius: 14px !important;
        background: #fffdf8 !important;
        color: #111111 !important;
        font-size: 16px;
        font-weight: 750;
    }

    .batch-editor-form .premium-input:focus,
    .batch-editor-form .premium-select:focus {
        border-color: #d4aa20 !important;
        box-shadow: 0 0 0 4px rgba(212, 170, 32, .18) !important;
        outline: none !important;
    }

    .batch-summary-card {
        position: sticky;
        top: 96px;
        background:
            radial-gradient(circle at 90% 10%, rgba(212, 170, 32, .18), transparent 32%),
            #2d2b25;
        color: #ffffff;
        border-color: rgba(255, 232, 169, .18);
    }

    .batch-summary-kicker {
        color: #ffe690;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .batch-summary-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 18px;
        border-radius: 18px;
        background: #d4aa20;
        color: #111111;
        font-size: 26px;
    }

    .batch-summary-card h3 {
        margin: 18px 0 0;
        font-size: 25px;
        font-weight: 950;
        letter-spacing: -.04em;
        line-height: 1.1;
    }

    .batch-summary-list {
        display: grid;
        gap: 12px;
        margin-top: 22px;
    }

    .batch-summary-list div {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255,255,255,.12);
    }

    .batch-summary-list span {
        color: rgba(255,255,255,.66);
        font-size: 13px;
        font-weight: 750;
    }

    .batch-summary-list strong {
        color: #ffffff;
        font-size: 14px;
        font-weight: 950;
        text-align: right;
    }

    .batch-fifo-note {
        display: flex;
        gap: 10px;
        margin-top: 22px;
        padding: 14px;
        border-radius: 16px;
        background: rgba(255, 232, 169, .10);
        color: rgba(255,255,255,.82);
        font-size: 13px;
        font-weight: 750;
        line-height: 1.45;
    }

    .batch-fifo-note i {
        color: #ffe690;
        flex: 0 0 auto;
    }

    .batch-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .batch-form-actions .premium-btn {
        min-width: 150px;
    }

    @media (max-width: 1150px) {
        .batch-editor-grid {
            grid-template-columns: 1fr;
        }

        .batch-summary-card {
            position: static;
        }
    }

    @media (max-width: 700px) {
        .batch-editor-section,
        .batch-summary-card {
            padding: 20px;
        }

        .batch-editor-fields {
            grid-template-columns: 1fr;
        }

        .batch-form-actions {
            display: grid;
        }

        .batch-form-actions .premium-btn {
            width: 100%;
        }
    }
</style>
