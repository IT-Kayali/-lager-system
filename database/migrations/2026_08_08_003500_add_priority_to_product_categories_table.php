<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->unsignedInteger('priority')->nullable()->after('name');
        });

        $categoryIds = DB::table('product_categories')
            ->orderBy('name')
            ->orderBy('id')
            ->pluck('id');

        foreach ($categoryIds as $index => $categoryId) {
            DB::table('product_categories')
                ->where('id', $categoryId)
                ->update(['priority' => $index + 1]);
        }

        Schema::table('product_categories', function (Blueprint $table) {
            $table->unique('priority', 'product_categories_priority_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique('product_categories_priority_unique');
            $table->dropColumn('priority');
        });
    }
};
