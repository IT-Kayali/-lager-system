<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->string('supplier_number')->unique();
            $table->string('company_name');
            $table->string('contact_person')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();

            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_name', 'city']);
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('supplier')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
        });

        Schema::dropIfExists('suppliers');
    }
};
