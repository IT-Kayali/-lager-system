<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->string('color', 7)->default('#475569');
        });

        DB::table('customer_groups')->where('slug', 'gold')->update(['color' => '#D4AD16']);
        DB::table('customer_groups')->where('slug', 'silver')->update(['color' => '#9CA3AF']);
        DB::table('customer_groups')->where('slug', 'diamond')->update(['color' => '#2563EB']);
    }

    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
