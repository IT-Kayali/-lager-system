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

        $pdf = Pdf::loadView($pdfView, [
            'offer' => $offer,
            'template' => $template,
            'title' => $title,
            'documentType' => $type,
            'logoDataUri' => $logoDataUri,
            'backgroundDataUri' => $backgroundDataUri,
        ])->setPaper('a4');

        $filenamePrefixes = [
            'offer' => 'angebot-',
            'invoice' => 'rechnung-',
            'delivery-note' => 'lieferschein-',
        ];

        return $pdf->stream($filenamePrefixes[$type] . $offer->offer_number . '.pdf');
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
