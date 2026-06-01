<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('document_templates', 'background_image_path')) {
                $table->string('background_image_path')->nullable()->after('logo_path');
            }

            if (! Schema::hasColumn('document_templates', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(19.00)->after('background_image_path');
            }

            if (! Schema::hasColumn('document_templates', 'product_column_label')) {
                $table->string('product_column_label')->default('Bezeichnung')->after('tax_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (Schema::hasColumn('document_templates', 'background_image_path')) {
                $table->dropColumn('background_image_path');
            }

            if (Schema::hasColumn('document_templates', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }

            if (Schema::hasColumn('document_templates', 'product_column_label')) {
                $table->dropColumn('product_column_label');
            }
        });
    }
};
