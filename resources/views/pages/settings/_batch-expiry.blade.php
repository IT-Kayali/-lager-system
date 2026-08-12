<section id="batch-expiry" class="premium-card" style="margin-top:22px;scroll-margin-top:24px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;">
            <i class="bi bi-calendar-check"></i>
        </span>
        <div>
            <h2 style="margin:0;font-size:22px;font-weight:950;">Standard-Ablaufdatum für Chargen</h2>
            <p class="premium-muted" style="margin:4px 0 0;">
                Lege fest, wie viele Monate nach dem Wareneingang das Ablaufdatum bei neuen Chargen automatisch vorgeschlagen wird.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.batch-expiry.update') }}">
        @csrf
        @method('PUT')

        <div class="premium-form-field" style="max-width:520px;">
            <label for="default_batch_expiry_months">Standard-Ablaufzeit in Monaten *</label>
            <input
                id="default_batch_expiry_months"
                name="default_batch_expiry_months"
                type="number"
                min="1"
                max="240"
                step="1"
                class="premium-input"
                value="{{ old('default_batch_expiry_months', $defaultBatchExpiryMonths) }}"
                required
            >

            <div class="premium-muted" style="margin-top:8px;line-height:1.55;">
                Standard: 24 Monate. Beispiel: Wareneingang 12.08.2026 ergibt bei 24 Monaten automatisch 12.08.2028.
                Das Ablaufdatum kann in jeder Charge manuell kürzer oder länger gesetzt werden. Es dient nur als Information und sperrt weder Verkauf noch FIFO-Warenausgang.
            </div>

            @error('default_batch_expiry_months')
                <div class="premium-error">{{ $message }}</div>
            @enderror
        </div>

        <button class="premium-btn gold" type="submit" style="margin-top:18px;">
            <i class="bi bi-save"></i>
            Ablaufzeit speichern
        </button>
    </form>
</section>
