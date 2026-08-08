<?php

namespace App\Http\Controllers;

use App\Models\BranchWithdrawal;
use App\Models\DocumentTemplate;
use App\Services\DocumentItemSorter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class BranchWithdrawalPdfController extends Controller
{
    public function __construct(private readonly DocumentItemSorter $documentItemSorter)
    {
    }

    public function stream(BranchWithdrawal $branchWithdrawal): Response
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()?->canAccessMenu(['manager', 'warehouse']), 403);

        $branchWithdrawal->load(['items.product.categories']);
        $branchWithdrawal->setRelation('items', $this->documentItemSorter->sort($branchWithdrawal->items));

        $template = DocumentTemplate::byKey(DocumentTemplate::WITH_COMPANY);

        // Wie beim normalen Lieferschein mit Logo: ausschließlich das eigene
        // Lieferschein-Logo verwenden. Der Filial-Lieferschein bekommt bewusst
        // kein Hintergrundbild, damit das Layout identisch weiß und schlicht bleibt.
        $logoDataUri = $this->publicStorageDataUri($template->delivery_logo_path);

        File::ensureDirectoryExists(storage_path('fonts'));

        $pdf = Pdf::loadView('pdf.branch-withdrawal-delivery-note', [
            'branchWithdrawal' => $branchWithdrawal,
            'template' => $template,
            'logoDataUri' => $logoDataUri,
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
