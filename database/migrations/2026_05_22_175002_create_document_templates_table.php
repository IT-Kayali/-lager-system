<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->string('name');

            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('logo_url')->nullable();

            $table->text('payment_info')->nullable();
            $table->text('footer_note')->nullable();

            $table->boolean('show_company_details')->default(true);
            $table->boolean('show_logo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
