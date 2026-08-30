<x-layouts.premium title="Kunden" subtitle="Kundenverwaltung mit individuell verwaltbaren Kundengruppen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @php
        $crmCustomerCare = auth()->user()?->isCrm() ?? false;
    @endphp

    <section class="premium-card">
        <div class="premium-toolbar">
            <form method="GET" action="{{ route('customers.index') }}" class="premium-search customers-search-inline">
                <input name="search" value="{{ $search }}" class="premium-input" style="min-width:280px;" placeholder="Kunde suchen...">
                <button class="premium-btn" type="submit"><i class="bi bi-search"></i> Suchen</button>
                @if ($search)
                    <a href="{{ route('customers.index') }}" class="premium-btn"><i class="bi bi-x-lg"></i> Zurücksetzen</a>
                @endif
            </form>
            <a href="{{ route('customers.create') }}" class="premium-btn gold"><i class="bi bi-plus-lg"></i> Kunde hinzufügen</a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead><tr><th>Kundennummer</th><th>Kunde</th><th>Gruppe</th><th>Kontakt</th><th>Stadt</th><th>USt-Nummer</th><th>Aktionen</th></tr></thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td><span class="premium-code">{{ $customer->customer_number }}</span></td>
                            <td><strong>{{ $customer->company_name }}</strong>@if ($customer->notes)<div class="premium-muted">{{ \Illuminate\Support\Str::limit($customer->notes, 60) }}</div>@endif</td>
                            <td><x-customer-group-badge :group="$customer->group" /></td>
                            <td>
                                <div class="customer-contact-stack">
                                    @if ($customer->email)<a href="mailto:{{ $customer->email }}"><i class="bi bi-envelope"></i> {{ $customer->email }}</a>@else<span class="premium-muted">Keine E-Mail</span>@endif
                                    @if ($customer->phone)<x-whatsapp-link :number="$customer->phone" :label="$customer->phone" :country-code="$customer->phone_country_code" />@endif
                                </div>
                            </td>
                            <td>{{ $customer->billing_city ?: $customer->delivery_city ?: $customer->city ?: '—' }}</td>
                            <td>{{ $customer->vat_number ?: '—' }}</td>
                            <td>
                                <div class="premium-actions">
                                    @unless ($crmCustomerCare)
                                        <a class="premium-icon-btn" href="{{ route('customers.show', $customer) }}" title="Vorschau"><i class="bi bi-eye"></i></a>
                                    @endunless
                                    <a class="premium-icon-btn" href="{{ route('customers.edit', $customer) }}" title="Bearbeiten"><i class="bi bi-pencil"></i></a>
                                    @unless ($crmCustomerCare)
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Kunde wirklich löschen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="premium-icon-btn premium-danger" type="submit" title="Löschen"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="premium-muted">Noch keine Kunden vorhanden.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:18px;">{{ $customers->links() }}</div>
    </section>
<style>
        .customers-search-inline {display:flex !important;flex-direction:row !important;align-items:center !important;gap:10px !important;flex-wrap:nowrap !important;}
        .customers-search-inline .premium-input {width:360px !important;max-width:360px !important;}
        .customers-search-inline .premium-btn {height:46px !important;white-space:nowrap !important;}
        .customer-contact-stack {display:grid;gap:5px;}
        .customer-contact-stack > a {color:#211d17;font-weight:800;text-decoration:none;}
        .customer-contact-stack > a:hover {color:#9a7300;}
        .customer-contact-stack > a i {margin-right:6px;color:#7b5c00;}
        @media (max-width:700px) {.customers-search-inline {flex-wrap:wrap !important;}.customers-search-inline .premium-input {width:100% !important;max-width:100% !important;}}
</style>
</x-layouts.premium>
