<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existing = Schema::getColumnListing('offers');

        Schema::table('offers', function (Blueprint $table) use ($existing) {
            if (! in_array('shipping_method', $existing, true)) {
                $table->string('shipping_method')->nullable()->after('template_type');
            }

            if (! in_array('shipping_price_gross', $existing, true)) {
                $table->decimal('shipping_price_gross', 10, 2)->nullable()->after('shipping_method');
            }
        });
    }

    public function down(): void
    {
        $existing = Schema::getColumnListing('offers');

        Schema::table('offers', function (Blueprint $table) use ($existing) {
            if (in_array('shipping_price_gross', $existing, true)) {
                $table->dropColumn('shipping_price_gross');
            }

            if (in_array('shipping_method', $existing, true)) {
                $table->dropColumn('shipping_method');
            }
        });
    }
};
