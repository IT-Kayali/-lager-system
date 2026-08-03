<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_withdrawals', function (Blueprint $table): void {
            $table->string('status', 30)->default('issued')->after('branch_name');
            $table->foreignId('processed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('status');
        });

        Schema::create('branch_withdrawal_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_withdrawal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->decimal('stock_before', 15, 3)->default(0);
            $table->decimal('stock_after', 15, 3)->default(0);
            $table->json('batch_allocations')->nullable();
            $table->timestamps();

            $table->unique(['branch_withdrawal_id', 'product_id'], 'branch_withdrawal_product_unique');
        });

        DB::table('branch_withdrawals')
            ->orderBy('id')
            ->each(function (object $withdrawal): void {
                DB::table('branch_withdrawal_items')->insert([
                    'branch_withdrawal_id' => $withdrawal->id,
                    'product_id' => $withdrawal->product_id,
                    'quantity' => $withdrawal->quantity,
                    'stock_before' => $withdrawal->stock_before,
                    'stock_after' => $withdrawal->stock_after,
                    'batch_allocations' => $withdrawal->batch_allocations,
                    'created_at' => $withdrawal->created_at,
                    'updated_at' => $withdrawal->updated_at,
                ]);

                DB::table('branch_withdrawals')
                    ->where('id', $withdrawal->id)
                    ->update([
                        'status' => 'issued',
                        'processed_by' => $withdrawal->user_id,
                        'processed_at' => $withdrawal->created_at,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_withdrawal_items');

        Schema::table('branch_withdrawals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn(['status', 'processed_at']);
        });
    }
};
