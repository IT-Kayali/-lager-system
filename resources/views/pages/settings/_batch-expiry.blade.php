<section id="offer-numbering" class="premium-card" style="margin-top:22px;scroll-margin-top:24px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;"><i class="bi bi-hash"></i></span>
        <div>
            <h2 style="margin:0;font-size:22px;font-weight:950;">Angebotsnummern</h2>
            <p class="premium-muted" style="margin:4px 0 0;">Startnummer und optionales Präfix für neue Angebote festlegen.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.offer-numbering.update') }}">
        @csrf
        @method('PUT')
        <div class="premium-form-grid" style="grid-template-columns:minmax(0,520px) minmax(220px,1fr);align-items:end;gap:18px;">
            <div class="premium-form-field">
                <label for="offer_number_start">Startwert für neue Angebote *</label>
                <input id="offer_number_start" name="offer_number_start" type="text" maxlength="30" class="premium-input" value="{{ old('offer_number_start', $offerNumberStart) }}" required>
                <div class="premium-muted" style="margin-top:8px;line-height:1.55;">
                    Beispiele: <strong>00111</strong> → 00111, 00112, 00113 oder <strong>AL00000</strong> → AL00000, AL00001, AL00002. Buchstaben, Zahlen, Bindestrich und Unterstrich werden unterstützt. Die Nummer muss mit Ziffern enden. Bereits vorhandene Angebotsnummern bleiben unverändert.
                </div>
                @error('offer_number_start')<div class="premium-error">{{ $message }}</div>@enderror
            </div>
            <div style="padding:16px;border:1px solid #e7dece;border-radius:16px;background:#fffdf8;">
                <div class="premium-muted" style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">Vorschau / Startwert</div>
                <div style="font-size:28px;font-weight:950;margin-top:5px;letter-spacing:.04em;">{{ $offerNumberPreview }}</div>
                <div class="premium-muted" style="margin-top:4px;">Der Zahlenteil wird automatisch fortlaufend erhöht.</div>
            </div>
        </div>
        <button class="premium-btn gold" type="submit" style="margin-top:18px;"><i class="bi bi-save"></i> Nummerierung speichern</button>
    </form>
</section>

<section id="batch-expiry" class="premium-card" style="margin-top:22px;scroll-margin-top:24px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <span style="width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#f3e8be;color:#7b5c00;font-size:20px;"><i class="bi bi-calendar-check"></i></span>
        <div><h2 style="margin:0;font-size:22px;font-weight:950;">Standard-Ablaufdatum für Chargen</h2><p class="premium-muted" style="margin:4px 0 0;">Lege fest, wie viele Monate nach dem Wareneingang das Ablaufdatum bei neuen Chargen automatisch vorgeschlagen wird.</p></div>
    </div>
    <form method="POST" action="{{ route('settings.batch-expiry.update') }}">
        @csrf @method('PUT')
        <div class="premium-form-field" style="max-width:520px;">
            <label for="default_batch_expiry_months">Standard-Ablaufzeit in Monaten *</label>
            <input id="default_batch_expiry_months" name="default_batch_expiry_months" type="number" min="1" max="240" step="1" class="premium-input" value="{{ old('default_batch_expiry_months', $defaultBatchExpiryMonths) }}" required>
            <div class="premium-muted" style="margin-top:8px;line-height:1.55;">Standard: 24 Monate. Beispiel: Wareneingang 12.08.2026 ergibt bei 24 Monaten automatisch 12.08.2028. Das Ablaufdatum kann in jeder Charge manuell kürzer oder länger gesetzt werden. Es dient nur als Information und sperrt weder Verkauf noch FIFO-Warenausgang.</div>
            @error('default_batch_expiry_months')<div class="premium-error">{{ $message }}</div>@enderror
        </div>
        <button class="premium-btn gold" type="submit" style="margin-top:18px;"><i class="bi bi-save"></i> Ablaufzeit speichern</button>
    </form>
</section>
