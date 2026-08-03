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

        $branchWithdrawal->load(['items.product']);

        $template = DocumentTemplate::byKey(DocumentTemplate::WITH_COMPANY);

        $logoDataUri = $this->publicStorageDataUri(
            $template->delivery_logo_path ?: $template->logo_path
        );

        $backgroundDataUri = $this->publicStorageDataUri(
            $template->delivery_background_image_path ?: $template->background_image_path
        );

        $customer = (object) [
            'id' => 0,
            'company_name' => $branchWithdrawal->branch_name,
            'contact_person' => null,
            'delivery_street' => null,
            'delivery_house_number' => null,
            'delivery_postal_code' => null,
            'delivery_city' => null,
            'delivery_country' => null,
            'billing_street' => null,
            'billing_house_number' => null,
            'billing_postal_code' => null,
            'billing_city' => null,
            'billing_country' => null,
            'billing_address' => null,
            'customer_number' => 'INTERNE FILIALE',
            'number' => null,
        ];

        $offer = (object) [
            'customer' => $customer,
            'items' => $branchWithdrawal->items->map(fn ($item) => (object) [
                'quantity' => $item->quantity,
                'product_name' => $item->product?->name ?? 'Gelöschtes Produkt',
                'description' => $item->product?->name ?? 'Gelöschtes Produkt',
                'product' => $item->product,
            ]),
            'shipping_method' => 'Interne Warenübergabe',
            'offer_number' => $branchWithdrawal->withdrawal_number,
        ];

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'offer' => $offer,
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
