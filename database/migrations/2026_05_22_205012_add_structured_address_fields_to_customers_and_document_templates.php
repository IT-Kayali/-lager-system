<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'delivery_street')) {
                $table->string('delivery_street')->nullable()->after('city');
                $table->string('delivery_house_number')->nullable()->after('delivery_street');
                $table->string('delivery_postal_code')->nullable()->after('delivery_house_number');
                $table->string('delivery_city')->nullable()->after('delivery_postal_code');
                $table->string('delivery_country')->nullable()->after('delivery_city');

                $table->string('billing_street')->nullable()->after('delivery_address');
                $table->string('billing_house_number')->nullable()->after('billing_street');
                $table->string('billing_postal_code')->nullable()->after('billing_house_number');
                $table->string('billing_city')->nullable()->after('billing_postal_code');
                $table->string('billing_country')->nullable()->after('billing_city');
            }
        });

        Schema::table('document_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('document_templates', 'company_street')) {
                $table->string('company_street')->nullable()->after('company_address');
                $table->string('company_house_number')->nullable()->after('company_street');
                $table->string('company_postal_code')->nullable()->after('company_house_number');
                $table->string('company_city')->nullable()->after('company_postal_code');
                $table->string('company_country')->nullable()->after('company_city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'delivery_street',
                'delivery_house_number',
                'delivery_postal_code',
                'delivery_city',
                'delivery_country',
                'billing_street',
                'billing_house_number',
                'billing_postal_code',
                'billing_city',
                'billing_country',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('document_templates', function (Blueprint $table) {
            foreach ([
                'company_street',
                'company_house_number',
                'company_postal_code',
                'company_city',
                'company_country',
            ] as $column) {
                if (Schema::hasColumn('document_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
