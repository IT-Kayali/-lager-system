<x-layouts.premium title="Angebotsvorschau" subtitle="Angebot anzeigen, Status ändern und PDFs öffnen.">
    @if (session('success')) <div class="premium-alert">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">{{ session('error') }}</div> @endif

    <section class="premium-card">
        <div class="premium-toolbar">
            <div><h2 style="font-size:24px;font-weight:900;margin:0;">{{ $offer->offer_number }}</h2><div class="premium-muted" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px;"><span>{{ $offer->customer?->company_name }}</span><span>·</span><x-customer-group-badge :group="$offer->customer?->group" /><span>·</span><span>{{ $offer->templateLabel() }}</span></div></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('offers.pdf', [$offer, 'offer']) }}" target="_blank" class="premium-btn gold"><i class="bi bi-file-earmark-pdf"></i> Angebot PDF</a>
                <a href="{{ route('offers.pdf', [$offer, 'invoice']) }}" target="_blank" class="premium-btn"><i class="bi bi-receipt"></i> Rechnung PDF</a>
                <a href="{{ route('offers.pdf', [$offer, 'delivery-note']) }}" target="_blank" class="premium-btn"><i class="bi bi-truck"></i> Lieferschein PDF</a>
                @if (! $offer->isFinal()) <a href="{{ route('offers.edit', $offer) }}" class="premium-btn"><i class="bi bi-pencil"></i> Bearbeiten</a> @endif
                @if (auth()->user()?->isManager()) <form method="POST" action="{{ route('offers.destroy', $offer) }}" onsubmit="return confirm('Angebot wirklich löschen?');">@csrf @method('DELETE')<button class="premium-btn" type="submit" style="background:#991b1b;"><i class="bi bi-trash"></i> Löschen</button></form> @endif
            </div>
        </div>

        @if (! $offer->isFinal())
            <form method="POST" action="{{ route('offers.status', $offer) }}" style="margin-bottom:18px;">@csrf @method('PUT')<div class="premium-form-grid"><div class="premium-form-field"><label>Status ändern</label><select name="status" class="premium-select">@foreach (['offer'=>'Angebot','in_progress'=>'In Bearbeitung','ready'=>'Abholbereit','completed'=>'Erledigt','cancelled'=>'Storniert','reservation_expired'=>'Reservierung abgelaufen'] as $value=>$label)<option value="{{ $value }}" @selected($offer->status === $value)>{{ $label }}</option>@endforeach</select></div><div class="premium-form-field" style="display:flex;align-items:end;"><button class="premium-btn gold" type="submit"><i class="bi bi-arrow-repeat"></i> Status speichern</button></div></div></form>
        @else <div style="margin-bottom:18px;"><span class="premium-badge ok">Status: {{ $offer->statusLabel() }}</span></div> @endif

        <div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Menge</th><th>Preisstufe</th><th>Einzelpreis</th><th>Summe</th></tr></thead><tbody>@foreach ($offer->items as $item)<tr><td><strong>{{ $item->product_name }}</strong><div class="premium-muted">{{ $item->product_code }}</div></td><td>{{ \App\Support\GermanNumber::format($item->quantity) }} {{ $item->product?->unitLabel('de') ?? '—' }}</td><td><span class="premium-badge ok">{{ $item->tier_label }}</span></td><td>{{ \App\Support\GermanNumber::format($item->unit_price) }} €</td><td>{{ \App\Support\GermanNumber::format($item->line_total) }} €</td></tr>@endforeach</tbody></table></div>
        <div style="text-align:right;font-size:22px;font-weight:900;margin-top:18px;">Gesamt: {{ \App\Support\GermanNumber::format($offer->total) }} €</div>
    </section>

    <section class="premium-card" style="margin-top:22px;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding-bottom:18px;border-bottom:1px solid rgba(33,33,33,.10);">
            <div><div style="display:flex;align-items:center;gap:10px;"><span style="width:38px;height:38px;border-radius:12px;background:rgba(212,175,55,.16);display:grid;place-items:center;color:#8b6b16;"><i class="bi bi-chat-left-text"></i></span><div><h3 style="margin:0;font-size:19px;font-weight:900;">Interne Notizen</h3><div class="premium-muted" style="margin-top:2px;">Nur für Mitarbeiter im ERP sichtbar · nicht auf PDFs</div></div></div></div>
            <span class="premium-badge">{{ $offer->internalNotes->count() }} {{ $offer->internalNotes->count() === 1 ? 'Notiz' : 'Notizen' }}</span>
        </div>

        <form method="POST" action="{{ route('offers.internal-notes.store', $offer) }}" style="padding:20px 0 22px;border-bottom:1px solid rgba(33,33,33,.10);">@csrf
            <label for="internal-note" style="display:block;font-weight:800;margin-bottom:8px;">Notiz hinzufügen</label>
            <textarea id="internal-note" name="note" rows="4" maxlength="5000" required placeholder="Interne Information zu diesem Angebot eingeben …" style="width:100%;resize:vertical;min-height:105px;border:1px solid rgba(33,33,33,.18);border-radius:14px;padding:14px 16px;background:#fff;font:inherit;box-sizing:border-box;outline:none;">{{ old('note') }}</textarea>
            @error('note') <div style="color:#991b1b;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:12px;"><div class="premium-muted" style="font-size:13px;"><i class="bi bi-person"></i> Wird gespeichert als <strong>{{ auth()->user()?->name }}</strong> mit Datum und Uhrzeit.</div><button class="premium-btn gold" type="submit"><i class="bi bi-plus-lg"></i> Notiz hinzufügen</button></div>
        </form>

        <div style="padding-top:20px;">
            @forelse ($offer->internalNotes as $note)
                <article style="display:grid;grid-template-columns:42px 1fr;gap:13px;position:relative;{{ ! $loop->last ? 'padding-bottom:20px;margin-bottom:20px;border-bottom:1px solid rgba(33,33,33,.08);' : '' }}">
                    <div style="width:42px;height:42px;border-radius:50%;background:#212121;color:#d4af37;display:grid;place-items:center;font-weight:900;">{{ mb_strtoupper(mb_substr($note->user?->name ?? '?', 0, 1)) }}</div>
                    <div><div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;"><strong style="font-size:15px;">{{ $note->user?->name ?? 'Gelöschter Benutzer' }}</strong><span class="premium-muted" style="font-size:12px;"><i class="bi bi-clock"></i> {{ $note->created_at->format('d.m.Y') }} · {{ $note->created_at->format('H:i') }} Uhr</span></div><div style="margin-top:8px;line-height:1.55;white-space:pre-wrap;word-break:break-word;">{{ $note->note }}</div></div>
                </article>
            @empty
                <div style="text-align:center;padding:20px 10px;color:#777;"><i class="bi bi-chat-square-text" style="font-size:25px;display:block;margin-bottom:8px;"></i>Noch keine internen Notizen vorhanden.</div>
            @endforelse
        </div>
    </section>
</x-layouts.premium>
