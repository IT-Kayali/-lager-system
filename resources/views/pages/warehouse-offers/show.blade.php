<x-layouts.premium title="Bestellung vorbereiten" subtitle="Lageransicht mit Positionen, Versand, Status und Lieferschein.">
    @if (session('success')) <div class="premium-alert">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="premium-alert" style="border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#991b1b;">{{ session('error') }}</div> @endif

    @php
        $customer = $offer->customer;
        $street = $customer?->delivery_street ?: $customer?->billing_street;
        $houseNumber = $customer?->delivery_house_number ?: $customer?->billing_house_number;
        $postalCode = $customer?->delivery_postal_code ?: $customer?->billing_postal_code;
        $city = $customer?->delivery_city ?: $customer?->billing_city ?: $customer?->city;
        $country = $customer?->delivery_country ?: $customer?->billing_country;
        $statusClass = match ($offer->status) {
            \App\Models\Offer::STATUS_IN_PROGRESS => 'progress',
            \App\Models\Offer::STATUS_READY => 'ready',
            \App\Models\Offer::STATUS_COMPLETED => 'completed',
            default => 'neutral',
        };
    @endphp

    <div class="warehouse-offer-layout">
        <div>
            <section class="premium-card" style="margin-bottom:22px;">
                <div class="premium-toolbar">
                    <div>
                        <h2 style="font-size:24px;font-weight:950;margin:0;">{{ $offer->offer_number }}</h2>
                        <div class="premium-muted" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:6px;"><span>{{ $customer?->company_name ?: '—' }}</span><span>·</span><span class="warehouse-status-badge {{ $statusClass }}">{{ $offer->statusLabel() }}</span></div>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;"><a href="{{ route('warehouse.offers.delivery-note', $offer) }}" target="_blank" class="premium-btn gold"><i class="bi bi-truck"></i> Lieferschein PDF</a><a href="{{ route('warehouse.offers.index') }}" class="premium-btn"><i class="bi bi-arrow-left"></i> Zurück</a></div>
                </div>

                @if ($nextStatuses)
                    <form method="POST" action="{{ route('warehouse.offers.status', $offer) }}" style="margin-top:18px;">@csrf @method('PUT')
                        <div class="premium-form-grid"><div class="premium-form-field"><label for="status">Lagerstatus</label><select id="status" name="status" class="premium-select" required>@foreach ($nextStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div><div class="premium-form-field" style="display:flex;align-items:end;"><button class="premium-btn gold" type="submit"><i class="bi bi-arrow-repeat"></i> Status speichern</button></div></div>
                    </form>
                @else
                    <div class="premium-alert" style="margin-top:18px;">Diese Bestellung ist erledigt. Der Lagerbestand wurde bereits per FIFO gebucht; deshalb ist ein Rücksprung aus „Erledigt“ gesperrt.</div>
                @endif
            </section>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-bottom:22px;">
                <section class="premium-card"><h3 style="margin:0 0 12px;font-size:18px;">Empfänger</h3><div style="display:grid;gap:6px;"><strong>{{ $customer?->company_name ?: '—' }}</strong><span>{{ $customer?->customer_number ?: '—' }}</span>@if ($street || $houseNumber)<span>{{ trim(($street ?? '') . ' ' . ($houseNumber ?? '')) }}</span>@endif @if ($postalCode || $city)<span>{{ trim(($postalCode ?? '') . ' ' . ($city ?? '')) }}</span>@endif @if ($country)<span>{{ $country }}</span>@endif</div></section>
                <section class="premium-card"><h3 style="margin:0 0 12px;font-size:18px;">Versand</h3><div style="display:grid;gap:6px;"><div><strong>Versandart:</strong> {{ $offer->shipping_method ?: '—' }}</div>@if ($offer->shipping_method === 'Lieferung')<div><strong>Anzahl Kartons:</strong> {{ $offer->carton_count ?: '—' }}</div>@endif</div></section>
                @if ($offer->notes)<section class="premium-card"><h3 style="margin:0 0 12px;font-size:18px;">Hinweis</h3><div style="white-space:pre-wrap;">{{ $offer->notes }}</div></section>@endif
            </div>

            <section class="premium-card">
                <div class="premium-toolbar"><div><h2 style="font-size:20px;font-weight:900;margin:0;">Positionen vorbereiten</h2><p class="premium-muted" style="margin:4px 0 0;">Nur Produkt, Menge und Einheit – keine Preis- oder Rechnungsdaten.</p></div></div>
                <div class="premium-table-wrap"><table class="premium-table"><thead><tr><th>Produkt</th><th>Menge</th><th>Einheit</th></tr></thead><tbody>@foreach ($offer->items as $item)<tr><td><strong>{{ $item->product_name }}</strong><div class="premium-muted">{{ $item->product_code }}</div></td><td>{{ \App\Support\GermanNumber::format($item->quantity) }}</td><td>{{ $item->product?->unitLabel('de') ?? ($item->unit ?: '—') }}</td></tr>@endforeach</tbody></table></div>
            </section>
        </div>

        <aside class="premium-card warehouse-notes-sidebar">
            <div class="warehouse-notes-head"><div><h3>Interne Notizen</h3><div class="premium-muted">Für alle ERP-Mitarbeiter sichtbar</div></div><span class="premium-badge">{{ $offer->internalNotes->count() }}</span></div>
            <form method="POST" action="{{ route('offer-notes.store', $offer) }}" class="warehouse-note-form">@csrf
                <label for="warehouse-internal-note">Notiz hinzufügen</label>
                <textarea id="warehouse-internal-note" name="note" rows="5" maxlength="5000" required placeholder="Interne Information eingeben …">{{ old('note') }}</textarea>
                @error('note') <div style="color:#991b1b;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
                <div class="premium-muted" style="font-size:12px;margin-top:8px;"><i class="bi bi-person"></i> {{ auth()->user()?->name }} · Datum & Uhrzeit automatisch</div>
                <button class="premium-btn gold" style="width:100%;justify-content:center;margin-top:12px;" type="submit"><i class="bi bi-plus-lg"></i> Notiz hinzufügen</button>
            </form>
            <div class="warehouse-notes-feed">
                @forelse ($offer->internalNotes as $note)
                    <article class="warehouse-note-item"><div class="warehouse-note-avatar">{{ mb_strtoupper(mb_substr($note->user?->name ?? '?', 0, 1)) }}</div><div><strong>{{ $note->user?->name ?? 'Gelöschter Benutzer' }}</strong><div class="warehouse-note-time">{{ $note->created_at->format('d.m.Y') }} · {{ $note->created_at->format('H:i') }} Uhr</div><div class="warehouse-note-text">{{ $note->note }}</div></div></article>
                @empty
                    <div class="premium-muted" style="padding:20px 0;text-align:center;">Noch keine internen Notizen.</div>
                @endforelse
            </div>
        </aside>
    </div>

    <style>
        .warehouse-offer-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:22px;align-items:start}.warehouse-notes-sidebar{position:sticky;top:20px;padding:20px!important}.warehouse-notes-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding-bottom:16px;border-bottom:1px solid rgba(33,33,33,.1)}.warehouse-notes-head h3{margin:0;font-size:18px}.warehouse-note-form{padding:18px 0;border-bottom:1px solid rgba(33,33,33,.1)}.warehouse-note-form label{display:block;font-weight:800;margin-bottom:8px}.warehouse-note-form textarea{width:100%;min-height:130px;box-sizing:border-box;resize:vertical;border:1px solid rgba(33,33,33,.18);border-radius:14px;padding:13px 14px;background:#fff;font:inherit}.warehouse-notes-feed{max-height:520px;overflow-y:auto}.warehouse-note-item{display:grid;grid-template-columns:38px 1fr;gap:10px;padding:16px 0;border-bottom:1px solid rgba(33,33,33,.08)}.warehouse-note-avatar{width:38px;height:38px;border-radius:50%;background:#212121;color:#d4af37;display:grid;place-items:center;font-weight:900}.warehouse-note-time{font-size:11px;color:#888;margin-top:3px}.warehouse-note-text{font-size:14px;line-height:1.5;margin-top:8px;white-space:pre-wrap;word-break:break-word}.warehouse-status-badge{display:inline-flex;align-items:center;min-height:32px;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:950;white-space:nowrap}.warehouse-status-badge.progress{background:#dbeafe;color:#1d4ed8}.warehouse-status-badge.ready{background:#fef3c7;color:#b45309}.warehouse-status-badge.completed{background:#dcfce7;color:#166534}.warehouse-status-badge.neutral{background:#f3f4f6;color:#374151}@media(max-width:980px){.warehouse-offer-layout{grid-template-columns:1fr}.warehouse-notes-sidebar{position:static}.warehouse-notes-feed{max-height:none}}
    </style>
</x-layouts.premium>
