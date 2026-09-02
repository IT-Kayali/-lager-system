<x-layouts.premium title="Angebotsvorschau" subtitle="Angebot anzeigen, Status ändern und PDFs öffnen.">
    @if (session('success')) <div class="premium-alert">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">{{ session('error') }}</div> @endif

    <div class="offer-preview-layout">
        <section class="premium-card offer-preview-main">
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

        <aside class="premium-card offer-notes-sidebar">
            <div class="offer-notes-header">
                <div class="offer-notes-title-row">
                    <span class="offer-notes-icon"><i class="bi bi-chat-left-text"></i></span>
                    <div>
                        <h3>Interne Notizen</h3>
                        <div class="premium-muted">Nur intern · nicht auf PDFs</div>
                    </div>
                </div>
                <span class="premium-badge">{{ $offer->internalNotes->count() }}</span>
            </div>

            <form method="POST" action="{{ route('offers.internal-notes.store', $offer) }}" class="offer-note-form">@csrf
                <label for="internal-note">Notiz hinzufügen</label>
                <textarea id="internal-note" name="note" rows="5" maxlength="5000" required placeholder="Interne Information eingeben …">{{ old('note') }}</textarea>
                @error('note') <div style="color:#991b1b;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
                <div class="offer-note-author"><i class="bi bi-person"></i> <strong>{{ auth()->user()?->name }}</strong> · Datum & Uhrzeit automatisch</div>
                <button class="premium-btn gold offer-note-submit" type="submit"><i class="bi bi-plus-lg"></i> Notiz hinzufügen</button>
            </form>

            <div class="offer-notes-feed">
                @forelse ($offer->internalNotes as $note)
                    <article class="offer-note-item">
                        <div class="offer-note-avatar">{{ mb_strtoupper(mb_substr($note->user?->name ?? '?', 0, 1)) }}</div>
                        <div class="offer-note-content">
                            <strong>{{ $note->user?->name ?? 'Gelöschter Benutzer' }}</strong>
                            <div class="offer-note-time"><i class="bi bi-clock"></i> {{ $note->created_at->format('d.m.Y') }} · {{ $note->created_at->format('H:i') }} Uhr</div>
                            <div class="offer-note-text">{{ $note->note }}</div>
                        </div>
                    </article>
                @empty
                    <div class="offer-notes-empty"><i class="bi bi-chat-square-text"></i><span>Noch keine internen Notizen.</span></div>
                @endforelse
            </div>
        </aside>
    </div>

    <style>
        .offer-preview-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 22px;
            align-items: start;
        }
        .offer-preview-main { min-width: 0; }
        .offer-notes-sidebar {
            position: sticky;
            top: 20px;
            padding: 20px !important;
            min-width: 0;
        }
        .offer-notes-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(33,33,33,.10);
        }
        .offer-notes-title-row { display:flex; align-items:center; gap:10px; min-width:0; }
        .offer-notes-icon {
            width: 38px; height: 38px; flex: 0 0 38px;
            border-radius: 12px; background: rgba(212,175,55,.16);
            display:grid; place-items:center; color:#8b6b16;
        }
        .offer-notes-title-row h3 { margin:0; font-size:18px; font-weight:900; }
        .offer-note-form { padding:18px 0; border-bottom:1px solid rgba(33,33,33,.10); }
        .offer-note-form label { display:block; font-weight:800; margin-bottom:8px; }
        .offer-note-form textarea {
            width:100%; min-height:135px; resize:vertical; box-sizing:border-box;
            border:1px solid rgba(33,33,33,.18); border-radius:14px;
            padding:13px 14px; background:#fff; font:inherit; outline:none;
        }
        .offer-note-form textarea:focus { border-color:#d4af37; box-shadow:0 0 0 3px rgba(212,175,55,.12); }
        .offer-note-author { margin-top:10px; font-size:12px; line-height:1.45; color:#777; }
        .offer-note-submit { width:100%; justify-content:center; margin-top:12px; }
        .offer-notes-feed { padding-top:4px; max-height:520px; overflow-y:auto; }
        .offer-note-item { display:grid; grid-template-columns:38px minmax(0,1fr); gap:10px; padding:16px 0; border-bottom:1px solid rgba(33,33,33,.08); }
        .offer-note-item:last-child { border-bottom:0; }
        .offer-note-avatar { width:38px; height:38px; border-radius:50%; background:#212121; color:#d4af37; display:grid; place-items:center; font-weight:900; }
        .offer-note-content { min-width:0; }
        .offer-note-content > strong { display:block; font-size:14px; }
        .offer-note-time { margin-top:3px; color:#888; font-size:11px; }
        .offer-note-text { margin-top:8px; line-height:1.5; white-space:pre-wrap; word-break:break-word; font-size:14px; }
        .offer-notes-empty { display:grid; place-items:center; gap:7px; text-align:center; color:#777; padding:24px 8px; }
        .offer-notes-empty i { font-size:24px; }
        @media (max-width: 1250px) {
            .offer-preview-layout { grid-template-columns: minmax(0, 1fr) 320px; gap:16px; }
        }
        @media (max-width: 980px) {
            .offer-preview-layout { grid-template-columns: 1fr; }
            .offer-notes-sidebar { position:static; }
            .offer-notes-feed { max-height:none; }
        }
    </style>
</x-layouts.premium>
