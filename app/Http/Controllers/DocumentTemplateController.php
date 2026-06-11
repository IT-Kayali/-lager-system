<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentTemplateController extends Controller
{
    public function index(): View
    {
        return view('pages.settings.document-templates', [
            'templates' => DocumentTemplate::query()->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:3000'],
            'company_street' => ['nullable', 'string', 'max:255'],
            'company_house_number' => ['nullable', 'string', 'max:50'],
            'company_postal_code' => ['nullable', 'string', 'max:50'],
            'company_city' => ['nullable', 'string', 'max:255'],
            'company_country' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_vat_id' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'string', 'max:255'],

            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],

            'delivery_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delivery_background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],

            'remove_logo' => ['nullable', 'boolean'],
            'remove_background_image' => ['nullable', 'boolean'],
            'remove_delivery_logo' => ['nullable', 'boolean'],
            'remove_delivery_background_image' => ['nullable', 'boolean'],

            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'product_column_label' => ['nullable', 'string', 'max:255'],
            'payment_info' => ['nullable', 'string', 'max:3000'],
            'footer_note' => ['nullable', 'string', 'max:3000'],

            'delivery_title' => ['nullable', 'string', 'max:255'],
            'delivery_date_label' => ['nullable', 'string', 'max:255'],
            'delivery_customer_number_label' => ['nullable', 'string', 'max:255'],
            'delivery_order_number_label' => ['nullable', 'string', 'max:255'],
            'delivery_shipping_method_label' => ['nullable', 'string', 'max:255'],
            'delivery_shipping_method_text' => ['nullable', 'string', 'max:255'],
            'delivery_intro_text' => ['nullable', 'string', 'max:3000'],
            'delivery_quantity_label' => ['nullable', 'string', 'max:255'],
            'delivery_product_label' => ['nullable', 'string', 'max:255'],
            'delivery_footer_text' => ['nullable', 'string', 'max:3000'],
            'delivery_footer_left_text' => ['nullable', 'string', 'max:3000'],
            'delivery_footer_middle_text' => ['nullable', 'string', 'max:3000'],
            'delivery_footer_right_text' => ['nullable', 'string', 'max:3000'],

            'show_company_details' => ['nullable', 'boolean'],
            'show_logo' => ['nullable', 'boolean'],
        ]);

        $data['show_company_details'] = $request->boolean('show_company_details');
        $data['show_logo'] = $request->boolean('show_logo');

        $data['tax_rate'] = $data['tax_rate'] ?? $documentTemplate->tax_rate ?? 19.00;
        $data['product_column_label'] = ($data['product_column_label'] ?? null)
            ?: ($documentTemplate->product_column_label ?: 'Bezeichnung');
        $data['payment_info'] = $data['payment_info'] ?? $documentTemplate->payment_info;

        $removeMap = [
            'remove_logo' => 'logo_path',
            'remove_background_image' => 'background_image_path',
            'remove_delivery_logo' => 'delivery_logo_path',
            'remove_delivery_background_image' => 'delivery_background_image_path',
        ];

        foreach ($removeMap as $requestKey => $columnName) {
            if (! $request->boolean($requestKey)) {
                continue;
            }

            if (! Schema::hasColumn('document_templates', $columnName)) {
                continue;
            }

            $this->deletePublicFileIfUnused($documentTemplate->{$columnName}, $documentTemplate, $columnName);
            $data[$columnName] = null;
        }

        $uploadMap = [
            'logo' => [
                'column' => 'logo_path',
                'folder' => 'document-templates',
                'also_null' => ['logo_url'],
            ],
            'background_image' => [
                'column' => 'background_image_path',
                'folder' => 'document-template-backgrounds',
                'also_null' => [],
            ],
            'delivery_logo' => [
                'column' => 'delivery_logo_path',
                'folder' => 'document-template-delivery-logos',
                'also_null' => [],
            ],
            'delivery_background_image' => [
                'column' => 'delivery_background_image_path',
                'folder' => 'document-template-delivery-backgrounds',
                'also_null' => [],
            ],
        ];

        foreach ($uploadMap as $requestKey => $config) {
            if (! $request->hasFile($requestKey)) {
                continue;
            }

            $columnName = $config['column'];

            if (! Schema::hasColumn('document_templates', $columnName)) {
                continue;
            }

            $this->deletePublicFileIfUnused($documentTemplate->{$columnName}, $documentTemplate, $columnName);

            $data[$columnName] = $request->file($requestKey)->store($config['folder'], 'public');

            foreach ($config['also_null'] as $alsoNullColumn) {
                $data[$alsoNullColumn] = null;
            }
        }

        foreach ([
            'logo',
            'background_image',
            'delivery_logo',
            'delivery_background_image',
            'remove_logo',
            'remove_background_image',
            'remove_delivery_logo',
            'remove_delivery_background_image',
        ] as $requestOnlyField) {
            unset($data[$requestOnlyField]);
        }

        $existingColumns = array_flip(Schema::getColumnListing('document_templates'));
        $data = array_intersect_key($data, $existingColumns);

        $documentTemplate->update($data);

        ActivityLog::record('document_template.updated', $documentTemplate, [
            'template' => $documentTemplate->name,
            'logo_path' => $documentTemplate->logo_path,
            'background_image_path' => $documentTemplate->background_image_path,
            'delivery_logo_path' => $documentTemplate->delivery_logo_path,
            'delivery_background_image_path' => $documentTemplate->delivery_background_image_path,
            'show_logo' => $documentTemplate->show_logo,
        ]);

        return redirect()
            ->route('document-templates.index')
            ->with('success', 'PDF-Vorlage wurde gespeichert.');
    }

    private function deletePublicFileIfUnused(?string $path, DocumentTemplate $documentTemplate, string $changedColumn): void
    {
        if (! $path) {
            return;
        }

        $assetColumns = array_values(array_filter([
            'logo_path',
            'background_image_path',
            'delivery_logo_path',
            'delivery_background_image_path',
        ], fn ($column) => Schema::hasColumn('document_templates', $column)));

        if (! in_array($changedColumn, $assetColumns, true)) {
            return;
        }

        $isStillUsed = DocumentTemplate::query()
            ->get(array_merge(['id'], $assetColumns))
            ->contains(function (DocumentTemplate $template) use ($assetColumns, $path, $documentTemplate, $changedColumn) {
                foreach ($assetColumns as $assetColumn) {
                    if ((int) $template->id === (int) $documentTemplate->id && $assetColumn === $changedColumn) {
                        continue;
                    }

                    if ($template->{$assetColumn} === $path) {
                        return true;
                    }
                }

                return false;
            });

        if ($isStillUsed) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
