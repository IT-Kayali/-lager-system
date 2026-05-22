<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (! Schema::hasColumn('offers', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('reserved_until');
            }

            if (! Schema::hasColumn('offers', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('offers', 'reservation_released_at')) {
                $table->timestamp('reservation_released_at')->nullable()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'reservation_released_at')) {
                $table->dropColumn('reservation_released_at');
            }

            if (Schema::hasColumn('offers', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            if (Schema::hasColumn('offers', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
