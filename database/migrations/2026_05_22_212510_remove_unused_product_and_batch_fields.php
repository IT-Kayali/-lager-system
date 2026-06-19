<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'storage_location')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex('products_storage_location_index');
                });
            } catch (\Throwable $e) {
                // Index may already be missing on some database states.
            }
        }

        $productColumns = [
            'storage_location',
            'image_path',
            'image_url',
        ];

        foreach ($productColumns as $column) {
            if (Schema::hasColumn('products', $column)) {
                Schema::table('products', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        if (Schema::hasColumn('product_batches', 'expires_at')) {
            try {
                Schema::table('product_batches', function (Blueprint $table) {
                    $table->dropIndex('product_batches_expires_at_index');
                });
            } catch (\Throwable $e) {
                // Index may already be missing on some database states.
            }
        }

        $batchColumns = [
            'storage_location',
            'purchase_price',
            'expires_at',
            'expiry_date',
        ];

        foreach ($batchColumns as $column) {
            if (Schema::hasColumn('product_batches', $column)) {
                Schema::table('product_batches', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'storage_location')) {
                $table->string('storage_location')->nullable()->after('supplier_id');
            }

            if (! Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }
        });

        Schema::table('product_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('product_batches', 'storage_location')) {
                $table->string('storage_location')->nullable()->after('quantity');
            }

            if (! Schema::hasColumn('product_batches', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->nullable()->after('storage_location');
            }

            if (! Schema::hasColumn('product_batches', 'expires_at')) {
                $table->date('expires_at')->nullable()->after('received_at');
            }
        });
    }
};
