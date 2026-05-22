<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('offer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('product_price_tier_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('product_code');
            $table->string('product_name');

            $table->decimal('quantity', 12, 3);
            $table->string('unit')->default('gram');

            $table->string('tier_key')->nullable();
            $table->string('tier_label')->nullable();

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['offer_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_items');
    }
};
