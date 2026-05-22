<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('product_code')->unique();
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('unit', ['gram', 'liter', 'piece'])->default('gram');

            $table->string('supplier')->nullable();
            $table->string('storage_location')->nullable();
            $table->decimal('minimum_stock', 12, 3)->default(0);

            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->timestamps();

            $table->index(['name', 'manufacturer']);
            $table->index('storage_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
