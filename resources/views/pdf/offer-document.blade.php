<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #212121;
            font-size: 12px;
            line-height: 1.45;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            border-bottom: 2px solid #e3ca6e;
            padding-bottom: 16px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .header-right {
            text-align: right;
        }

        .logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 28px;
            margin: 0 0 8px;
        }

        h2 {
            font-size: 15px;
            margin: 20px 0 8px;
        }

        .muted {
            color: #666;
        }

        .box {
            border: 1px solid #ddd6c8;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th {
            background: #212121;
            color: #fff;
            text-align: left;
            padding: 9px;
            font-size: 11px;
        }

        td {
            border-bottom: 1px solid #e5dfd2;
            padding: 9px;
            vertical-align: top;
        }

        .total {
            text-align: right;
            margin-top: 18px;
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e3ca6e;
            padding-top: 8px;
        }

        .badge {
            display: inline-block;
            background: #fbf6ed;
            border: 1px solid #e3ca6e;
            padding: 4px 7px;
            border-radius: 12px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    @php
        $companyAddressLines = [];

        if ($template->company_street || $template->company_house_number) {
            $companyAddressLines[] = trim(($template->company_street ?? '') . ' ' . ($template->company_house_number ?? ''));
        }

        if ($template->company_postal_code || $template->company_city) {
            $companyAddressLines[] = trim(($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? ''));
        }

        if ($template->company_country) {
            $companyAddressLines[] = $template->company_country;
        }

        if (empty($companyAddressLines) && $template->company_address) {
            $companyAddressLines = explode("\n", $template->company_address);
        }

        $customerAddressLines = [];

        if ($offer->customer?->billing_street || $offer->customer?->billing_house_number) {
            $customerAddressLines[] = trim(($offer->customer->billing_street ?? '') . ' ' . ($offer->customer->billing_house_number ?? ''));
        }

        if ($offer->customer?->billing_postal_code || $offer->customer?->billing_city) {
            $customerAddressLines[] = trim(($offer->customer->billing_postal_code ?? '') . ' ' . ($offer->customer->billing_city ?? ''));
        }

        if ($offer->customer?->billing_country) {
            $customerAddressLines[] = $offer->customer->billing_country;
        }

        if (empty($customerAddressLines)) {
            if ($offer->customer?->delivery_street || $offer->customer?->delivery_house_number) {
                $customerAddressLines[] = trim(($offer->customer->delivery_street ?? '') . ' ' . ($offer->customer->delivery_house_number ?? ''));
            }

            if ($offer->customer?->delivery_postal_code || $offer->customer?->delivery_city) {
                $customerAddressLines[] = trim(($offer->customer->delivery_postal_code ?? '') . ' ' . ($offer->customer->delivery_city ?? ''));
            }

            if ($offer->customer?->delivery_country) {
                $customerAddressLines[] = $offer->customer->delivery_country;
            }
        }

        if (empty($customerAddressLines) && $offer->customer?->billing_address) {
            $customerAddressLines = explode("\n", $offer->customer->billing_address);
        } elseif (empty($customerAddressLines) && $offer->customer?->delivery_address) {
            $customerAddressLines = explode("\n", $offer->customer->delivery_address);
        }
    @endphp

    <div class="header">
        <div class="header-left">
            @if ($template->show_logo && $logoDataUri)
                <img src="{{ $logoDataUri }}" class="logo">
            @endif

            @if ($template->show_company_details)
                <strong>{{ $template->company_name }}</strong><br>

                @foreach ($companyAddressLines as $line)
                    {{ $line }}<br>
                @endforeach

                @if ($template->company_phone)
                    Tel.: {{ $template->company_phone }}<br>
                @endif

                @if ($template->company_email)
                    E-Mail: {{ $template->company_email }}
                @endif
            @endif
        </div>

        <div class="header-right">
            <h1>{{ $title }}</h1>
            <div><strong>Nummer:</strong> {{ $offer->offer_number }}</div>
            <div><strong>Datum:</strong> {{ now()->format('d.m.Y') }}</div>
            <div><strong>Vorlage:</strong> {{ $template->name }}</div>
            @if ($documentType === 'offer' && $offer->reserved_until)
                <div><strong> bis:</strong> {{ $offer->reserved_until->format('d.m.Y H:i') }}</div>
            @endif
        </div>
    </div>

    <div class="box">
        <h2>Kunde</h2>
        <strong>{{ $offer->customer?->company_name }}</strong><br>
        Kundennummer: {{ $offer->customer?->customer_number }}<br>
        Kundengruppe: {{ $offer->customer?->group?->name }}<br>

        @if (! empty($customerAddressLines))
            <br>
            @foreach ($customerAddressLines as $line)
                {{ $line }}<br>
            @endforeach
        @endif

        @if ($offer->customer?->vat_number)
            USt-Nummer: {{ $offer->customer->vat_number }}
        @endif
    </div>

    <h2>Positionen</h2>

    <table>
        <thead>
            <tr>
                <th>Pos.</th>
                <th>Produkt</th>
                <th>Menge</th>
                <th>Preisstufe</th>
                <th>Einzelpreis</th>
                <th>Summe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($offer->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong><br>
                        <span class="muted">{{ $item->product_code }}</span>
                    </td>
                    <td>{{ number_format((float) $item->quantity, 3, ',', '.') }} {{ $item->unit }}</td>
                    <td>
                        <span class="badge">{{ $item->tier_label }}</span>
                    </td>
                    <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                    <td>{{ number_format((float) $item->line_total, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Gesamtbetrag: {{ number_format((float) $offer->total, 2, ',', '.') }} €
    </div>

    @if ($template->payment_info)
        <div class="box" style="margin-top: 24px;">
            <h2>Zahlungsinformationen</h2>
            {!! nl2br(e($template->payment_info)) !!}
        </div>
    @endif

    @if ($offer->notes)
        <div class="box">
            <h2>Hinweise</h2>
            {!! nl2br(e($offer->notes)) !!}
        </div>
    @endif

    <div class="footer">
        {{ $template->footer_note ?: 'Dokument wurde automatisch erstellt.' }}
    </div>
</body>
</html>
