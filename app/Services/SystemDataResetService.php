<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SystemDataResetService
{
    public const RESET_TABLES = [
        'offer_internal_notes',
        'warehouse_notifications',
        'customer_wallet_transactions',
        'offer_items',
        'branch_withdrawal_items',
        'branch_withdrawals',
        'stock_movements',
        'product_batches',
        'manual_price_rules',
        'product_price_tiers',
        'category_product',
        'offers',
        'customers',
        'products',
        'product_categories',
        'suppliers',
        'activity_logs',
    ];

    public const PRESERVED_TABLES = [
        'users',
        'application_settings',
        'customer_groups',
        'price_tier_definitions',
        'document_templates',
    ];

    /**
     * @return array<string, int>
     */
    public function reset(int $userId, ?string $ipAddress): array
    {
        $counts = [];

        DB::transaction(function () use (&$counts, $userId, $ipAddress): void {
            foreach (self::RESET_TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $counts[$table] = DB::table($table)->count();

                DB::table($table)->delete();
            }

            ActivityLog::query()->create([
                'user_id' => $userId,
                'action' => 'system.data_reset',
                'entity' => null,
                'entity_id' => null,
                'ip_address' => $ipAddress,
                'properties' => [
                    'deleted_counts' => $counts,
                    'preserved_tables' => self::PRESERVED_TABLES,
                ],
            ]);
        }, 3);

        return $counts;
    }
}
