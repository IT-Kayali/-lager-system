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
                    <div class="premium-muted" data-csp-style="s-98fb48e9">
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