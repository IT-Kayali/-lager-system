<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tier_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label', 100);
            $table->unsignedInteger('min_grams');
            $table->unsignedInteger('max_grams');
            $table->timestamps();

            $table->index(['min_grams', 'max_grams']);
        });

        $now = now();

        DB::table('price_tier_definitions')->insert([
            [
                'key' => '50g',
                'label' => '50g',
                'min_grams' => 50,
                'max_grams' => 90,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => '100g',
                'label' => '100g',
                'min_grams' => 91,
                'max_grams' => 239,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => '250g',
                'label' => '250g',
                'min_grams' => 240,
                'max_grams' => 460,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => '500g',
                'label' => '500g',
                'min_grams' => 461,
                'max_grams' => 750,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => '1000g',
                'label' => '1000g',
                'min_grams' => 751,
                'max_grams' => 5000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('price_tier_definitions');
    }
};
