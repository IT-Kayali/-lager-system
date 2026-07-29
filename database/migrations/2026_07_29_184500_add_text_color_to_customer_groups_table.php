<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customer_groups', 'text_color')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                $table->string('text_color', 7)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customer_groups', 'text_color')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                $table->dropColumn('text_color');
            });
        }
    }
};
