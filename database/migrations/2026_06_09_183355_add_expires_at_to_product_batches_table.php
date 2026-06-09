<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_batches', 'expires_at')) {
            Schema::table('product_batches', function (Blueprint $table) {
                $table->date('expires_at')->nullable()->after('quantity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_batches', 'expires_at')) {
            Schema::table('product_batches', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
