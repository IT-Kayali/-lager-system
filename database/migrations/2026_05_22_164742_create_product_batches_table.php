<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('batch_number')->unique();

            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('storage_location')->nullable();

            $table->decimal('purchase_price', 12, 2)->nullable();

            $table->date('received_at');
            $table->date('expires_at')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'received_at']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
