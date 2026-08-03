<?php

namespace App\Http\Controllers;

use App\Models\BranchWithdrawal;
use App\Models\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BranchWithdrawalPdfController extends Controller
{
    public function stream(BranchWithdrawal $branchWithdrawal): Response
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()?->canAccessMenu(['manager', 'warehouse']), 403);

        $branchWithdrawal->load(['items.product', 'user', 'processor']);

        $template = DocumentTemplate::byKey(DocumentTemplate::WITH_COMPANY);

        $logoDataUri = $this->publicStorageDataUri(
            $template->delivery_logo_path ?: $template->logo_path
        );

        $backgroundDataUri = $this->publicStorageDataUri(
            $template->delivery_background_image_path ?: $template->background_image_path
        );

        $pdf = Pdf::loadView('pdf.branch-withdrawal-delivery-note', [
            'withdrawal' => $branchWithdrawal,
            'template' => $template,
            'logoDataUri' => $logoDataUri,
            'backgroundDataUri' => $backgroundDataUri,
        ])->setPaper('a4');

        return $pdf->stream(
            'filial-lieferschein-' . $branchWithdrawal->withdrawal_number . '.pdf'
        );
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
