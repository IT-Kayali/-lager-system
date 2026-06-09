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

        $activityEvents = [
            'offer' => 'offer.pdf.generated',
            'invoice' => 'invoice.pdf.generated',
            'delivery-note' => 'delivery_note.pdf.generated',
        ];

        ActivityLog::record($activityEvents[$type], $offer, [
            'offer_number' => $offer->offer_number,
            'template' => $template->name,
        ]);

        $templateName = strtolower((string) ($template->name ?? ''));
        $isNoLogoPdfTemplate = str_contains($templateName, 'ohne') || ! (bool) ($template->show_logo ?? false);

        if ($type === 'delivery-note') {
            $pdfView = $isNoLogoPdfTemplate ? 'pdf.delivery-note-ohne' : 'pdf.delivery-note';
        } else {
            $pdfView = $isNoLogoPdfTemplate ? 'pdf.offer-document-ohne' : 'pdf.offer-document';
        }

        $pdf = Pdf::loadView($pdfView, [
            'offer' => $offer,
            'template' => $template,
            'title' => $title,
            'documentType' => $type,
            'logoDataUri' => $this->logoDataUri($template),
            'backgroundDataUri' => $this->backgroundDataUri($template),
        ])->setPaper('a4');

        $filenamePrefixes = [
            'offer' => 'angebot-',
            'invoice' => 'rechnung-',
            'delivery-note' => 'lieferschein-',
        ];

        $filename = $filenamePrefixes[$type] . $offer->offer_number . '.pdf';

        return $pdf->stream($filename);
    }


    private function backgroundDataUri(DocumentTemplate $template): ?string
    {
        if (! $template->background_image_path) {
            return null;
        }

        $paths = [
            storage_path('app/public/' . $template->background_image_path),
            public_path('storage/' . $template->background_image_path),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';

                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return null;
    }

    private function logoDataUri(DocumentTemplate $template): ?string
    {
        if (! $template->show_logo || ! $template->logo_path) {
            return null;
        }

        $paths = [
            storage_path('app/public/' . $template->logo_path),
            public_path('storage/' . $template->logo_path),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $mime = mime_content_type($path) ?: 'image/png';

                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return null;
    }
}
