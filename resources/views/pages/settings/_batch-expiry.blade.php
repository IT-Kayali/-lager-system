<section id="offer-numbering" class="premium-card" data-csp-style="s-1c4a2677">
    <div data-csp-style="s-c309ae69">
        <span data-csp-style="s-a9cc23e5"><i class="bi bi-hash"></i></span>
        <div>
            <h2 data-csp-style="s-6191170d">Angebotsnummern</h2>
            <p class="premium-muted" data-csp-style="s-3ca59c3a">Startnummer und optionales Präfix für neue Angebote festlegen.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.offer-numbering.update') }}">
        @csrf
        @method('PUT')
        <div class="premium-form-grid" data-csp-style="s-9d581cfb">
            <div class="premium-form-field">
                <label for="offer_number_start">Startwert für neue Angebote *</label>
                <input id="offer_number_start" name="offer_number_start" type="text" maxlength="30" class="premium-input" value="{{ old('offer_number_start', $offerNumberStart) }}" required>
                <div class="premium-muted" data-csp-style="s-b33fa592">
                    Beispiele: <strong>00111</strong> → 00111, 00112, 00113 oder <strong>AL00000</strong> → AL00000, AL00001, AL00002. Buchstaben, Zahlen, Bindestrich und Unterstrich werden unterstützt. Die Nummer muss mit Ziffern enden. Bereits vorhandene Angebotsnummern bleiben unverändert.
                </div>
                @error('offer_number_start')<div class="premium-error">{{ $message }}</div>@enderror
            </div>
            <div data-csp-style="s-93bb75ac">
                <div class="premium-muted" data-csp-style="s-f1e1c593">Vorschau / Startwert</div>
                <div data-csp-style="s-3d270107">{{ $offerNumberPreview }}</div>
                <div class="premium-muted" data-csp-style="s-2d421cb0">Der Zahlenteil wird automatisch fortlaufend erhöht.</div>
            </div>
        </div>
        <button class="premium-btn gold" type="submit" data-csp-style="s-e9f7b175"><i class="bi bi-save"></i> Nummerierung speichern</button>
    </form>
</section>

<section id="batch-expiry" class="premium-card" data-csp-style="s-1c4a2677">
    <div data-csp-style="s-c309ae69">
        <span data-csp-style="s-a9cc23e5"><i class="bi bi-calendar-check"></i></span>
        <div><h2 data-csp-style="s-6191170d">Standard-Ablaufdatum für Chargen</h2><p class="premium-muted" data-csp-style="s-3ca59c3a">Lege fest, wie viele Monate nach dem Wareneingang das Ablaufdatum bei neuen Chargen automatisch vorgeschlagen wird.</p></div>
    </div>
    <form method="POST" action="{{ route('settings.batch-expiry.update') }}">
        @csrf @method('PUT')
        <div class="premium-form-field" data-csp-style="s-cd459318">
            <label for="default_batch_expiry_months">Standard-Ablaufzeit in Monaten *</label>
            <input id="default_batch_expiry_months" name="default_batch_expiry_months" type="number" min="1" max="240" step="1" class="premium-input" value="{{ old('default_batch_expiry_months', $defaultBatchExpiryMonths) }}" required>
            <div class="premium-muted" data-csp-style="s-b33fa592">Standard: 24 Monate. Beispiel: Wareneingang 12.08.2026 ergibt bei 24 Monaten automatisch 12.08.2028. Das Ablaufdatum kann in jeder Charge manuell kürzer oder länger gesetzt werden. Es dient nur als Information und sperrt weder Verkauf noch FIFO-Warenausgang.</div>
            @error('default_batch_expiry_months')<div class="premium-error">{{ $message }}</div>@enderror
        </div>
        <button class="premium-btn gold" type="submit" data-csp-style="s-e9f7b175"><i class="bi bi-save"></i> Ablaufzeit speichern</button>
    </form>
</section>