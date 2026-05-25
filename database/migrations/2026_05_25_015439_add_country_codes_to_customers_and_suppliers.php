<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'phone_country_code')) {
                $table->string('phone_country_code', 10)->default('+49')->after('email');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'phone_country_code')) {
                $table->string('phone_country_code', 10)->default('+49')->after('email');
            }

            if (! Schema::hasColumn('suppliers', 'whatsapp_country_code')) {
                $table->string('whatsapp_country_code', 10)->default('+49')->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'phone_country_code')) {
                $table->dropColumn('phone_country_code');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'phone_country_code')) {
                $table->dropColumn('phone_country_code');
            }

            if (Schema::hasColumn('suppliers', 'whatsapp_country_code')) {
                $table->dropColumn('whatsapp_country_code');
            }
        });
    }
};
