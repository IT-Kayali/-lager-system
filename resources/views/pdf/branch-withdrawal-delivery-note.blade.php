<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: A4 portrait; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            background: #fff;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            overflow: hidden;
            background: #fff;
        }

        .page:last-child { page-break-after: auto; }

        .background {
            position: absolute;
            inset: 0;
            width: 210mm;
            height: 297mm;
            object-fit: cover;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            width: 210mm;
            height: 297mm;
        }

        .logo {
            position: absolute;
            top: 13mm;
            right: 22mm;
            width: 40mm;
            max-height: 24mm;
            object-fit: contain;
        }

        .sender-line {
            position: absolute;
            top: 28mm;
            left: 12mm;
            width: 104mm;
            font-size: 7.2pt;
            white-space: nowrap;
            overflow: hidden;
        }

        .recipient {
            position: absolute;
            top: 42mm;
            left: 12mm;
            width: 90mm;
            font-size: 10.3pt;
            line-height: 1.36;
        }

        .recipient strong {
            font-size: 11pt;
        }

        .meta {
            position: absolute;
            top: 43mm;
            right: 14mm;
            width: 78mm;
            font-size: 10pt;
            line-height: 1.48;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td:first-child {
            width: 32mm;
            font-weight: 700;
        }

        .title {
            position: absolute;
            top: 92mm;
            left: 12mm;
            right: 12mm;
            text-align: center;
            font-size: 18pt;
            font-weight: 700;
        }

        .intro {
            position: absolute;
            top: 108mm;
            left: 12mm;
            width: 184mm;
            font-size: 10.3pt;
            line-height: 1.55;
        }

        .items-wrap {
            position: absolute;
            left: 12mm;
            top: 132mm;
            width: 184mm;
        }

        .items-wrap.continuation {
            top: 27mm;
        }

        table.items {
            width: 184mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items thead { display: table-header-group; }
        table.items tbody { display: table-row-group; }
        table.items tr { page-break-inside: avoid; }

        table.items th {
            background: #f0f0f0;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1.25;
            padding: 2.2mm 2mm;
            border-bottom: 1px solid #777;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        table.items td {
            font-size: 10pt;
            line-height: 1.25;
            padding: 2mm 2mm;
            border-bottom: 1px solid #cfcfcf;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 58mm;
            text-align: center;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 126mm;
            text-align: left;
        }

        .product-code {
            display: block;
            margin-top: 1mm;
            color: #666;
            font-size: 8.5pt;
        }

        .document-note {
            position: absolute;
            left: 12mm;
            right: 12mm;
            bottom: 67mm;
            font-size: 9.5pt;
            line-height: 1.35;
        }

        .signatures {
            width: 100%;
            margin-top: 13mm;
            border-collapse: collapse;
        }

        .signatures td {
            width: 48%;
            vertical-align: bottom;
        }

        .signature-line {
            margin-top: 13mm;
            border-top: 1px solid #555;
            padding-top: 2mm;
            color: #555;
            font-size: 8pt;
        }

        .footer {
            position: absolute;
            left: 12mm;
            right: 12mm;
            bottom: 12mm;
            height: 34mm;
            font-size: 8pt;
            line-height: 1.35;
        }

        .footer-col {
            position: absolute;
            top: 0;
            width: 58mm;
        }

        .footer-left { left: 0; }
        .footer-middle { left: 70mm; }
        .footer-right { right: 0; }

        .page-number {
            position: absolute;
            right: 12mm;
            bottom: 5mm;
            font-size: 7.4pt;
            color: #777;
        }
    </style>
</head>
<body>
@php
    $companyName = trim((string) ($template->company_name ?: 'Alowidat Parfümerie'));
    $companyStreet = trim((string) (($template->company_street ?? '') . ' ' . ($template->company_house_number ?? '')));
    $companyCity = trim((string) (($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? '')));
    $companyCountry = trim((string) ($template->company_country ?? 'Deutschland'));

    $companyAddressLines = array_values(array_filter([
        $companyStreet,
        $companyCity,
        $companyCountry,
    ], fn ($line) => trim((string) $line) !== ''));

    $senderLine = trim($companyName . ' – ' . $companyStreet . ' – ' . $companyCity);
    $statusLabel = \App\Models\BranchWithdrawal::statusLabels()[$withdrawal->status] ?? $withdrawal->status;

    $unitLabels = [
        'gram' => 'g',
        'liter' => 'L',
        'piece' => 'Stk.',
    ];

    $formatQuantity = function ($value): string {
        $formatted = number_format((float) $value, 3, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    };

    $items = $withdrawal->items->values()->map(function ($item) use ($formatQuantity, $unitLabels) {
        $unit = $unitLabels[$item->product?->unit] ?? ($item->product?->unit ?: '');
        $quantity = trim($formatQuantity($item->quantity) . ' ' . $unit);
        $description = $item->product?->name ?? 'Gelöschtes Produkt';
        $productCode = trim((string) ($item->product?->product_code ?? ''));

        return (object) [
            'quantity' => $quantity,
            'description' => $description,
            'product_code' => $productCode,
        ];
    });

    $firstPageLimit = 9;
    $normalPageLimit = 18;
    $lastPageLimit = 18;

    $pages = collect();
    $remaining = $items->values();

    $pages->push([
        'first' => true,
        'items' => $remaining->take($firstPageLimit)->values(),
    ]);

    $remaining = $remaining->slice($firstPageLimit)->values();

    while ($remaining->count() > $lastPageLimit) {
        $pages->push([
            'first' => false,
            'items' => $remaining->take($normalPageLimit)->values(),
        ]);

        $remaining = $remaining->slice($normalPageLimit)->values();
    }

    if ($remaining->count() > 0) {
        $pages->push([
            'first' => false,
            'items' => $remaining->values(),
        ]);
    }

    $pageCount = $pages->count();
@endphp

@foreach ($pages as $pageIndex => $page)
    <div class="page">
        @if (! empty($backgroundDataUri))
            <img class="background" src="{{ $backgroundDataUri }}" alt="">
        @endif

        <div class="content">
            @if ($page['first'])
                @if (! empty($logoDataUri))
                    <img class="logo" src="{{ $logoDataUri }}" alt="">
                @endif

                <div class="sender-line">{{ $senderLine }}</div>

                <div class="recipient">
                    <strong>{{ $withdrawal->branch_name }}</strong><br>
                    Interne Filiale
                </div>

                <div class="meta">
                    <table>
                        <tr>
                            <td>Datum:</td>
                            <td>{{ $withdrawal->created_at?->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td>Filiale:</td>
                            <td>{{ $withdrawal->branch_name }}</td>
                        </tr>
                        <tr>
                            <td>Vorgangs-Nr.:</td>
                            <td>{{ $withdrawal->withdrawal_number }}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td>{{ $statusLabel }}</td>
                        </tr>
                    </table>
                </div>

                <div class="title">Lieferschein</div>

                <div class="intro">
                    Sehr geehrte Damen und Herren,<br>
                    wir übergeben der Filiale {{ $withdrawal->branch_name }} folgende Waren:
                </div>
            @endif

            <div class="items-wrap {{ $page['first'] ? '' : 'continuation' }}">
                <table class="items">
                    <thead>
                        <tr>
                            <th>Menge</th>
                            <th>Bezeichnung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($page['items'] as $item)
                            <tr>
                                <td>{{ $item->quantity }}</td>
                                <td>
                                    {{ $item->description }}
                                    @if ($item->product_code !== '')
                                        <span class="product-code">Artikelnummer: {{ $item->product_code }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($loop->last)
                <div class="document-note">
                    @if (filled($withdrawal->note))
                        <strong>Notiz:</strong> {{ $withdrawal->note }}<br>
                    @endif

                    <table class="signatures">
                        <tr>
                            <td><div class="signature-line">Datum, Unterschrift Lager / Ausgabe</div></td>
                            <td style="width:4%;"></td>
                            <td><div class="signature-line">Datum, Unterschrift Filiale / Übernahme</div></td>
                        </tr>
                    </table>
                </div>
            @endif

            <div class="footer">
                <div class="footer-col footer-left">
                    <strong>{{ $companyName }}</strong><br>
                    @foreach ($companyAddressLines as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>

                <div class="footer-col footer-middle">
                    <strong>Kontakt:</strong><br>
                    @if ($template->company_phone)
                        Tel: {{ $template->company_phone }}<br>
                    @endif
                    @if ($template->company_email)
                        E-Mail: {{ $template->company_email }}<br>
                    @endif
                    @if ($template->company_website)
                        Web: {{ $template->company_website }}<br>
                    @endif
                </div>

                <div class="footer-col footer-right">
                    @if ($template->company_vat_id)
                        USt-IdNr.: {{ $template->company_vat_id }}<br>
                    @endif
                    Interner Filial-Lieferschein
                </div>
            </div>

            <div class="page-number">
                Seite {{ $pageIndex + 1 }} / {{ $pageCount }}
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
