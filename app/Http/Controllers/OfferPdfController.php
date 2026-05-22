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
        if (! in_array($type, ['offer', 'invoice'], true)) {
            throw new InvalidArgumentException('Ungültiger Dokumenttyp.');
        }

        $offer->load(['customer.group', 'items.product']);

        $template = DocumentTemplate::byKey($offer->template_type);

        $title = $type === 'invoice' ? 'Rechnung' : 'Angebot';

        ActivityLog::record($type === 'invoice' ? 'invoice.pdf.generated' : 'offer.pdf.generated', $offer, [
            'offer_number' => $offer->offer_number,
            'template' => $template->name,
        ]);

        $pdf = Pdf::loadView('pdf.offer-document', [
            'offer' => $offer,
            'template' => $template,
            'title' => $title,
            'documentType' => $type,
            'logoDataUri' => $this->logoDataUri($template),
        ])->setPaper('a4');

        $filename = ($type === 'invoice' ? 'rechnung-' : 'angebot-') . $offer->offer_number . '.pdf';

        return $pdf->stream($filename);
    }

    private function logoDataUri(DocumentTemplate $template): ?string
    {
        if (! $template->show_logo) {
            return null;
        }

        if ($template->logo_path) {
            $path = public_path('storage/' . $template->logo_path);

            if (is_file($path)) {
                $mime = mime_content_type($path) ?: 'image/png';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return null;
    }
}
