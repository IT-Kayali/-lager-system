<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('pdf_templates', 'background_image_path')) {
                $table->string('background_image_path')->nullable();
            }

            if (! Schema::hasColumn('pdf_templates', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(19.00);
            }

            if (! Schema::hasColumn('pdf_templates', 'product_column_label')) {
                $table->string('product_column_label')->default('Bezeichnung');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pdf_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pdf_templates', 'background_image_path')) {
                $table->dropColumn('background_image_path');
            }

            if (Schema::hasColumn('pdf_templates', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }

            if (Schema::hasColumn('pdf_templates', 'product_column_label')) {
                $table->dropColumn('product_column_label');
            }
        });
    }
};
