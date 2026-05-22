<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_group_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('tier_key');
            $table->string('tier_label');

            $table->unsignedInteger('min_grams');
            $table->unsignedInteger('max_grams');

            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'customer_group_id', 'tier_key'], 'unique_product_group_tier');
            $table->index(['product_id', 'customer_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
