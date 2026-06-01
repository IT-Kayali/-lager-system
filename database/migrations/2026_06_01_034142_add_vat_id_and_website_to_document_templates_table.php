<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('document_templates', 'company_vat_id')) {
                $table->string('company_vat_id')->nullable()->after('company_email');
            }

            if (! Schema::hasColumn('document_templates', 'company_website')) {
                $table->string('company_website')->nullable()->after('company_vat_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (Schema::hasColumn('document_templates', 'company_website')) {
                $table->dropColumn('company_website');
            }

            if (Schema::hasColumn('document_templates', 'company_vat_id')) {
                $table->dropColumn('company_vat_id');
            }
        });
    }
};
