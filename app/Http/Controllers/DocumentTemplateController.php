<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'payment_info' => ['nullable', 'string', 'max:3000'],
            'footer_note' => ['nullable', 'string', 'max:3000'],
            'show_company_details' => ['nullable', 'boolean'],
            'show_logo' => ['nullable', 'boolean'],
        ]);

        $data['show_company_details'] = $request->boolean('show_company_details');
        $data['show_logo'] = $request->boolean('show_logo');

        if ($request->hasFile('logo')) {
            if ($documentTemplate->logo_path && Storage::disk('public')->exists($documentTemplate->logo_path)) {
                Storage::disk('public')->delete($documentTemplate->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('document-templates', 'public');
            $data['logo_url'] = null;
        }

        unset($data['logo']);

        $documentTemplate->update($data);

        ActivityLog::record('document_template.updated', $documentTemplate, [
            'template' => $documentTemplate->name,
            'logo_path' => $documentTemplate->logo_path,
            'show_logo' => $documentTemplate->show_logo,
        ]);

        return redirect()
            ->route('document-templates.index')
            ->with('success', 'PDF-Vorlage wurde gespeichert.');
    }
}
