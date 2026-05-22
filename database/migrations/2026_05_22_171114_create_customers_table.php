<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('customer_number')->unique();

            $table->foreignId('customer_group_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('company_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('city')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('billing_address')->nullable();

            $table->string('vat_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_name', 'city']);
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
