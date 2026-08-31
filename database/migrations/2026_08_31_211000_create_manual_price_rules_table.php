<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_price_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_quantity', 12, 3);
            $table->decimal('max_quantity', 12, 3)->nullable();
            $table->decimal('price', 12, 2);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'customer_group_id', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_price_rules');
    }
};
