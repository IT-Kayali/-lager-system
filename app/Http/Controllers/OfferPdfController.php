<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentTemplate;
use App\Models\Offer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use InvalidArgumentException;

class OfferPdfController extends Controller
{
    public function stream(Offer $offer, string $type): Response
    {
        $documentLabels = [
            'offer' => 'Angebot',
            'invoice' => 'Rechnung',
            'delivery-note' => 'Lieferschein',
        ];

        if (! array_key_exists($type, $documentLabels)) {
            throw new InvalidArgumentException('Ungültiger Dokumenttyp.');
        }

        $offer->load(['customer.group', 'items.product']);

        $template = DocumentTemplate::byKey($offer->template_type);
        $title = $documentLabels[$type];

        $templateName = strtolower((string) ($template->name ?? ''));
        $isNoLogoPdfTemplate = str_contains($templateName, 'ohne') || ! (bool) ($template->show_logo ?? false);

        if ($type === 'delivery-note') {
            $pdfView = $isNoLogoPdfTemplate ? 'pdf.delivery-note-ohne' : 'pdf.delivery-note';

            // Wichtig: Lieferschein nutzt NUR eigene Lieferschein-Dateien.
            // Kein Fallback mehr auf Angebot/Rechnung.
            $logoDataUri = $isNoLogoPdfTemplate
                ? null
                : $this->publicStorageDataUri($template->delivery_logo_path);

            $backgroundDataUri = $this->publicStorageDataUri($template->delivery_background_image_path);
        } else {
            $pdfView = $isNoLogoPdfTemplate ? 'pdf.offer-document-ohne' : 'pdf.offer-document';

            // Angebot/Rechnung nutzt NUR eigene Angebot/Rechnung-Dateien.
            $logoDataUri = $this->publicStorageDataUri($template->logo_path);
            $backgroundDataUri = $this->publicStorageDataUri($template->background_image_path);
        }

        $activityEvents = [
            'offer' => 'offer.pdf.generated',
            'invoice' => 'invoice.pdf.generated',
            'delivery-note' => 'delivery_note.pdf.generated',
        ];

        ActivityLog::record($activityEvents[$type], $offer, [
            'offer_number' => $offer->offer_number,
            'template' => $template->name,
        ]);

        $viewData = [
            'offer' => $offer,
            'template' => $template,
            'title' => $title,
            'documentType' => $type,
            'logoDataUri' => $logoDataUri,
            'backgroundDataUri' => $backgroundDataUri,
        ];

        if ($type === 'delivery-note') {
            $html = view($pdfView, $viewData)->render();
            $html = $this->injectCartonCountIntoDeliveryNote($html, $offer, $isNoLogoPdfTemplate);
            $pdf = Pdf::loadHTML($html)->setPaper('a4');
        } elseif ($type === 'invoice') {
            $html = view($pdfView, $viewData)->render();
            $html = $this->injectInvoiceProductUnitColumn($html, $offer, $isNoLogoPdfTemplate);
            $pdf = Pdf::loadHTML($html)->setPaper('a4');
        } else {
            $pdf = Pdf::loadView($pdfView, $viewData)->setPaper('a4');
        }

        $filenamePrefixes = [
            'offer' => 'angebot-',
            'invoice' => 'rechnung-',
            'delivery-note' => 'lieferschein-',
        ];

        return $pdf->stream($filenamePrefixes[$type] . $offer->offer_number . '.pdf');
    }

    private function injectCartonCountIntoDeliveryNote(string $html, Offer $offer, bool $isNoLogoPdfTemplate): string
    {
        if ($offer->shipping_method !== 'Lieferung' || ! $offer->carton_count || $offer->carton_count < 1) {
            return $html;
        }

        $shippingLabel = $isNoLogoPdfTemplate ? 'Shipping Method:' : 'Versandart:';
        $cartonLabel = $isNoLogoPdfTemplate ? 'Number of Cartons:' : 'Anzahl Kartons:';

        $pattern = '/(<tr>\s*<td>' . preg_quote($shippingLabel, '/') . '<\/td>\s*<td>.*?<\/td>\s*<\/tr>)/s';
        $cartonRow = "\n                    <tr>\n                        <td>{$cartonLabel}</td>\n                        <td>{$offer->carton_count}</td>\n                    </tr>";

        $rendered = preg_replace($pattern, '$1' . $cartonRow, $html, 1);

        return is_string($rendered) ? $rendered : $html;
    }

    /**
     * Rechnungen erhalten eine eigene Einheit-Spalte, ohne die gemeinsam
     * genutzten Angebot/Rechnung-Blade-Dateien oder das Angebotslayout zu ändern.
     */
    private function injectInvoiceProductUnitColumn(string $html, Offer $offer, bool $isNoLogoPdfTemplate): string
    {
        $units = $offer->items->values()->map(function ($item): string {
            $unit = trim((string) ($item->product?->unit ?? ''));

            return $unit !== '' ? $unit : '—';
        })->all();

        if (
            ($offer->shipping_method ?? null) === 'Lieferung'
            && (float) ($offer->shipping_price_gross ?? 0) > 0
        ) {
            $units[] = '—';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousLibxmlState = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlState);

        if (! $loaded) {
            return $html;
        }

        $xpath = new \DOMXPath($dom);
        $tables = $xpath->query(
            '//table['
            . 'contains(concat(" ", normalize-space(@class), " "), " items-table ")'
            . ' or contains(concat(" ", normalize-space(@class), " "), " items ")'
            . ']'
        );

        if ($tables === false || $tables->length === 0) {
            return $html;
        }

        $productLabel = $isNoLogoPdfTemplate ? 'PRODUCT' : 'Produkt';
        $quantityLabel = $isNoLogoPdfTemplate ? 'QUANTITY' : 'Menge';
        $unitLabel = $isNoLogoPdfTemplate ? 'UNIT' : 'Einheit';
        $priceLabel = $isNoLogoPdfTemplate ? 'PRICE' : 'Preis';
        $totalLabel = $isNoLogoPdfTemplate ? 'TOTAL' : 'Summe';
        $unitIndex = 0;

        foreach ($tables as $table) {
            if (! $table instanceof \DOMElement) {
                continue;
            }

            $table->setAttribute(
                'class',
                trim($table->getAttribute('class') . ' invoice-unit-table')
            );

            $headerNodes = [];
            $headers = $xpath->query('.//thead/tr[1]/th', $table);

            if ($headers !== false) {
                foreach ($headers as $header) {
                    $headerNodes[] = $header;
                }
            }

            if (count($headerNodes) >= 4) {
                $headerNodes[0]->nodeValue = $productLabel;
                $headerNodes[1]->nodeValue = $quantityLabel;
                $headerNodes[2]->nodeValue = $priceLabel;
                $headerNodes[3]->nodeValue = $totalLabel;

                $unitHeader = $dom->createElement('th');
                $unitHeader->appendChild($dom->createTextNode($unitLabel));
                $headerNodes[2]->parentNode?->insertBefore($unitHeader, $headerNodes[2]);
            }

            $rows = $xpath->query('.//tbody/tr', $table);

            if ($rows === false) {
                continue;
            }

            foreach ($rows as $row) {
                if (! $row instanceof \DOMElement) {
                    continue;
                }

                $cells = [];
                foreach ($row->childNodes as $child) {
                    if ($child instanceof \DOMElement && strtolower($child->tagName) === 'td') {
                        $cells[] = $child;
                    }
                }

                if (count($cells) < 4) {
                    continue;
                }

                $rowClasses = ' ' . preg_replace('/\s+/', ' ', trim($row->getAttribute('class'))) . ' ';
                $isEmptyRow = str_contains($rowClasses, ' empty-product-row ');
                $unit = $isEmptyRow ? "\u{00A0}" : ($units[$unitIndex] ?? '—');

                if (! $isEmptyRow) {
                    $unitIndex++;
                }

                $unitCell = $dom->createElement('td');
                $unitCell->appendChild($dom->createTextNode($unit));
                $cells[2]->parentNode?->insertBefore($unitCell, $cells[2]);
            }
        }

        $head = $dom->getElementsByTagName('head')->item(0);

        if ($head instanceof \DOMElement) {
            $style = $dom->createElement('style');
            $style->setAttribute('id', 'invoice-unit-column-styles');
            $style->appendChild($dom->createTextNode(
                $isNoLogoPdfTemplate
                    ? $this->noLogoInvoiceUnitColumnCss()
                    : $this->logoInvoiceUnitColumnCss()
            ));
            $head->appendChild($style);
        }

        $rendered = $dom->saveHTML();

        if (! is_string($rendered)) {
            return $html;
        }

        return (string) preg_replace('/^<\?xml encoding="UTF-8"\?>\s*/i', '', $rendered);
    }

    private function logoInvoiceUnitColumnCss(): string
    {
        return <<<'CSS'
.items-table.invoice-unit-table th:nth-child(1),
.items-table.invoice-unit-table td:nth-child(1) { width: 82mm !important; text-align: left !important; }
.items-table.invoice-unit-table th:nth-child(2),
.items-table.invoice-unit-table td:nth-child(2) { width: 20mm !important; text-align: center !important; }
.items-table.invoice-unit-table th:nth-child(3),
.items-table.invoice-unit-table td:nth-child(3) { width: 18mm !important; text-align: center !important; }
.items-table.invoice-unit-table th:nth-child(4),
.items-table.invoice-unit-table td:nth-child(4) { width: 20mm !important; text-align: right !important; }
.items-table.invoice-unit-table th:nth-child(5),
.items-table.invoice-unit-table td:nth-child(5) { width: 20mm !important; text-align: right !important; }
CSS;
    }

    private function noLogoInvoiceUnitColumnCss(): string
    {
        return <<<'CSS'
table.items.invoice-unit-table th:nth-child(1),
table.items.invoice-unit-table td:nth-child(1) { width: 70mm !important; text-align: left !important; }
table.items.invoice-unit-table th:nth-child(2),
table.items.invoice-unit-table td:nth-child(2) { width: 22mm !important; text-align: right !important; }
table.items.invoice-unit-table th:nth-child(3),
table.items.invoice-unit-table td:nth-child(3) { width: 18mm !important; text-align: center !important; }
table.items.invoice-unit-table th:nth-child(4),
table.items.invoice-unit-table td:nth-child(4) { width: 28mm !important; text-align: right !important; }
table.items.invoice-unit-table th:nth-child(5),
table.items.invoice-unit-table td:nth-child(5) { width: 32mm !important; text-align: right !important; }
CSS;
    }

    private function publicStorageDataUri(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $paths = [
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),
        ];

        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => mime_content_type($path) ?: 'image/jpeg',
            };

            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        }

        return null;
    }
}
