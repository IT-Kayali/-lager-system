<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_tier_definitions', function (Blueprint $table) {
            $table->unsignedInteger('max_grams')->nullable()->change();
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->unsignedInteger('max_grams')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('price_tier_definitions')
            ->whereNull('max_grams')
            ->update(['max_grams' => DB::raw('min_grams')]);

        DB::table('product_price_tiers')
            ->whereNull('max_grams')
            ->update(['max_grams' => DB::raw('min_grams')]);

        Schema::table('price_tier_definitions', function (Blueprint $table) {
            $table->unsignedInteger('max_grams')->nullable(false)->change();
        });

        Schema::table('product_price_tiers', function (Blueprint $table) {
            $table->unsignedInteger('max_grams')->nullable(false)->change();
        });
    }
};
