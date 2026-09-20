<x-layouts.premium title="Kundenprofil" subtitle="Kundendaten, Guthaben und Bestellhistorie anzeigen.">
    @php
        $billingAddress = collect([
            trim(($customer->billing_street ?? '') . ' ' . ($customer->billing_house_number ?? '')),
            trim(($customer->billing_postal_code ?? '') . ' ' . ($customer->billing_city ?? '')),
            $customer->billing_country,
        ])->filter();

        $deliveryAddress = collect([
            trim(($customer->delivery_street ?? '') . ' ' . ($customer->delivery_house_number ?? '')),
            trim(($customer->delivery_postal_code ?? '') . ' ' . ($customer->delivery_city ?? '')),
            $customer->delivery_country,
        ])->filter();

        $walletClass = $walletBalance > 0 ? 'wallet-positive' : ($walletBalance < 0 ? 'wallet-negative' : 'wallet-neutral');

        $transactionLabels = [
            'offer_debit' => 'Angebot abgezogen',
            'offer_payment_credit' => 'Zahlung gutgeschrieben',
            'offer_cancel_adjustment' => 'Storno-Korrektur',
            'manual_credit' => 'Manuelle Gutschrift',
            'manual_debit' => 'Manueller Abzug',
        ];
    @endphp

    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    <div data-csp-style="s-d93a4dfe">
        <a href="{{ route('customers.index') }}" class="premium-btn">
            <i class="bi bi-arrow-left"></i>
            Zurück
        </a>

        <a href="{{ route('customers.edit', $customer) }}" class="premium-btn gold">
            <i class="bi bi-pencil"></i>
            Kunde bearbeiten
        </a>

        <a href="{{ route('offers.create', ['customer_id' => $customer->id]) }}" class="premium-btn">
            <i class="bi bi-plus-lg"></i>
            Neues Angebot
        </a>
    </div>

    <div class="premium-grid customer-profile-grid">
        <section class="premium-card">
            <div class="premium-muted" data-csp-style="s-fc61b41d">Kunde</div>
            <h2 data-csp-style="s-3bea0fae">{{ $customer->company_name }}</h2>

            <div data-csp-style="s-5311b8aa">
                <div>
                    <strong>Kundennummer:</strong><br>
                    <span class="premium-code">{{ $customer->customer_number }}</span>
                </div>

                <div>
                    <strong>Kundengruppe:</strong><br>
                    <x-customer-group-badge :group="$customer->group" />
                </div>

                @if ($customer->vat_number)
                    <div>
                        <strong>USt-Nummer:</strong><br>
                        {{ $customer->vat_number }}
                    </div>
                @endif
            </div>
        </section>

        <section class="premium-card">
            <div class="premium-muted" data-csp-style="s-fc61b41d">Kontakt</div>

            <div data-csp-style="s-ff14234e">
                <div>
                    <strong>E-Mail:</strong><br>
                    @if ($customer->email)
                        <a href="mailto:{{ $customer->email }}" data-csp-style="s-6a6caba7">{{ $customer->email }}</a>
                    @else
                        —
                    @endif
                </div>

                <div>
                    <strong>Telefon / WhatsApp:</strong><br>
                    @if ($customer->phone)
                        <x-whatsapp-link :number="$customer->phone" :label="$customer->phone" :country-code="$customer->phone_country_code" />
                    @else
                        —
                    @endif
                </div>
            </div>
        </section>

        <section class="premium-card wallet-card {{ $walletClass }}">
            <div class="premium-muted" data-csp-style="s-fc61b41d">Kundenkonto</div>

            <div class="wallet-balance">
                {{ $walletBalance >= 0 ? '+' : '' }}{{ \App\Support\GermanNumber::format($walletBalance) }} €
            </div>

            <div class="wallet-hint">
                @if ($walletBalance > 0)
                    Guthaben im Plus
                @elseif ($walletBalance < 0)
                    Kunde ist im Minus
                @else
                    Konto ausgeglichen
                @endif
            </div>
        </section>
    </div>

    @include('pages.customers._revenue')

    <div class="premium-grid" data-csp-style="s-a555e52a">
        <section class="premium-card">
            <h2 data-csp-style="s-f0db0e9c">Rechnungsadresse</h2>

            @if ($billingAddress->isNotEmpty())
                <div data-csp-style="s-d0feeb4b">
                    @foreach ($billingAddress as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>
            @elseif ($customer->billing_address)
                <div data-csp-style="s-4f5d23fc">{{ $customer->billing_address }}</div>
            @else
                <div class="premium-muted">Keine Rechnungsadresse hinterlegt.</div>
            @endif
        </section>

        <section class="premium-card">
            <h2 data-csp-style="s-f0db0e9c">Lieferadresse</h2>

            @if ($deliveryAddress->isNotEmpty())
                <div data-csp-style="s-d0feeb4b">
                    @foreach ($deliveryAddress as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>
            @elseif ($customer->delivery_address)
                <div data-csp-style="s-4f5d23fc">{{ $customer->delivery_address }}</div>
            @else
                <div class="premium-muted">Keine Lieferadresse hinterlegt.</div>
            @endif
        </section>
    </div>

    <section class="premium-card" data-csp-style="s-e9f7b175">
        <h2 data-csp-style="s-35e6a173">Guthaben manuell buchen</h2>
        <div class="premium-muted" data-csp-style="s-3914ce83">Notiz ist Pflicht. Der eingeloggte Mitarbeiter wird automatisch gespeichert.</div>

        <form method="POST" action="{{ route('customers.wallet-transactions.store', $customer) }}">
            @csrf

            <div class="premium-form-grid">
                <div class="premium-form-field">
                    <label>Art *</label>
                    <select name="direction" class="premium-select" data-search="false" required>
                        <option value="credit">Guthaben hinzufügen</option>
                        <option value="debit">Guthaben abziehen</option>
                    </select>
                    @error('direction') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label>Betrag *</label>
                    <input name="amount" type="text" inputmode="decimal" class="premium-input" value="{{ old('amount') }}" placeholder="0,00" required>
                    @error('amount') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label>Notiz *</label>
                    <textarea name="note" rows="3" class="premium-textarea" required placeholder="z. B. Barzahlung, Korrektur, Rückzahlung..."></textarea>
                    @error('note') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <button class="premium-btn gold" type="submit" data-csp-style="s-b15ae76f">
                <i class="bi bi-wallet2"></i>
                Buchung speichern
            </button>
        </form>
    </section>

    <section class="premium-card" data-csp-style="s-e9f7b175">
        <h2 data-csp-style="s-bf72d880">Guthaben-Historie</h2>

        <div class="premium-table-wrap">
            <table class="premium-table premium-wide-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Art</th>
                        <th>Betrag</th>
                        <th>Saldo danach</th>
                        <th>Notiz</th>
                        <th>Mitarbeiter</th>
                        <th>Angebot</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($walletTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at?->format('d.m.Y H:i') }}</td>
                            <td>{{ $transactionLabels[$transaction->type] ?? $transaction->type }}</td>
                            <td>
                                <strong class="{{ $transaction->amount >= 0 ? 'wallet-text-positive' : 'wallet-text-negative' }}">
                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ \App\Support\GermanNumber::format($transaction->amount) }} €
                                </strong>
                            </td>
                            <td>
                                <strong class="{{ $transaction->balance_after >= 0 ? 'wallet-text-positive' : 'wallet-text-negative' }}">
                                    {{ $transaction->balance_after >= 0 ? '+' : '' }}{{ \App\Support\GermanNumber::format($transaction->balance_after) }} €
                                </strong>
                            </td>
                            <td data-csp-style="s-c634306d">{{ $transaction->note }}</td>
                            <td>{{ $transaction->user?->name ?: 'System' }}</td>
                            <td>
                                @if ($transaction->offer)
                                    <a href="{{ route('offers.show', $transaction->offer) }}" class="premium-code">
                                        {{ $transaction->offer->offer_number }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="premium-muted">Noch keine Guthaben-Buchungen vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-csp-style="s-e9f7b175">
            {{ $walletTransactions->links() }}
        </div>
    </section>

    <section class="premium-card" data-csp-style="s-e9f7b175">
        <h2 data-csp-style="s-bf72d880">Bestellhistorie</h2>

        <div class="premium-table-wrap">
            <table class="premium-table premium-wide-table">
                <thead>
                    <tr>
                        <th>Nummer</th>
                        <th>Datum</th>
                        <th>Status</th>
                        <th>Positionen</th>
                        <th>Gesamt</th>
                        <th>Reserviert bis</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td><span class="premium-code">{{ $offer->offer_number }}</span></td>
                            <td>{{ $offer->created_at?->format('d.m.Y H:i') }}</td>
                            <td>
                                <span class="premium-badge {{ $offer->status }}">
                                    {{ $statusLabels[$offer->status] ?? $offer->status }}
                                </span>
                            </td>
                            <td>{{ $offer->items_count }}</td>
                            <td>{{ \App\Support\GermanNumber::format($offer->total) }} €</td>
                            <td>
                                @if ($offer->reserved_until && $offer->status === \App\Models\Offer::STATUS_OFFER)
                                    {{ $offer->reserved_until->format('d.m.Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('offers.show', $offer) }}" title="Anzeigen">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('offers.edit', $offer) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if (Route::has('offers.pdf'))
                                        <a class="premium-icon-btn" href="{{ route('offers.pdf', ['offer' => $offer, 'type' => 'offer']) }}" title="PDF öffnen">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="premium-muted">Für diesen Kunden gibt es noch keine Angebote oder Rechnungen.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-csp-style="s-e9f7b175">
            {{ $offers->links() }}
        </div>
    </section>
</x-layouts.premium>