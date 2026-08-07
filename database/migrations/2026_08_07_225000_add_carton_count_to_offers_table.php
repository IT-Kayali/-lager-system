<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('offers', 'carton_count')) {
            Schema::table('offers', function (Blueprint $table): void {
                $table->unsignedInteger('carton_count')->nullable()->after('shipping_price_gross');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('offers', 'carton_count')) {
            Schema::table('offers', function (Blueprint $table): void {
                $table->dropColumn('carton_count');
            });
        }
    }
};
