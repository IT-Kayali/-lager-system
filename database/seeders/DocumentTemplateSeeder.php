<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DocumentTemplate::updateOrCreate(
            ['key' => DocumentTemplate::WITH_COMPANY],
            [
                'name' => 'Mit Firmendaten & Logo',
                'company_name' => 'Lagerverwaltung',
                'company_address' => "Musterstraße 1\n48431 Rheine\nDeutschland",
                'company_phone' => '+49 000 000000',
                'company_email' => 'info@example.com',
                'payment_info' => "Zahlbar innerhalb von 7 Tagen.\nBankverbindung bitte hier eintragen.",
                'footer_note' => 'Vielen Dank für Ihr Vertrauen.',
                'show_company_details' => true,
                'show_logo' => true,
            ]
        );

        DocumentTemplate::updateOrCreate(
            ['key' => DocumentTemplate::WITHOUT_COMPANY],
            [
                'name' => 'Ohne Firmendaten & Logo',
                'company_name' => null,
                'company_address' => null,
                'company_phone' => null,
                'company_email' => null,
                'payment_info' => null,
                'footer_note' => 'Dokument ohne Firmenkopf.',
                'show_company_details' => false,
                'show_logo' => false,
            ]
        );
    }
}
