<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existing = Schema::getColumnListing('document_templates');

        Schema::table('document_templates', function (Blueprint $table) use ($existing) {
            if (! in_array('delivery_logo_path', $existing, true)) {
                $table->string('delivery_logo_path')->nullable()->after('background_image_path');
            }

            if (! in_array('delivery_background_image_path', $existing, true)) {
                $table->string('delivery_background_image_path')->nullable()->after('delivery_logo_path');
            }
        });

        DB::table('document_templates')->update([
            'delivery_logo_path' => DB::raw('logo_path'),
            'delivery_background_image_path' => DB::raw('background_image_path'),
        ]);
    }

    public function down(): void
    {
        $existing = Schema::getColumnListing('document_templates');

        Schema::table('document_templates', function (Blueprint $table) use ($existing) {
            if (in_array('delivery_background_image_path', $existing, true)) {
                $table->dropColumn('delivery_background_image_path');
            }

            if (in_array('delivery_logo_path', $existing, true)) {
                $table->dropColumn('delivery_logo_path');
            }
        });
    }
};
