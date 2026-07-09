<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Strip Iraq Doctors marketplace pharmacy / laboratory leftovers.
 * Clinic CMS will later own in-clinic Laboratory & Radiology as modules —
 * not as external marketplace businesses.
 */
return new class extends Migration
{
    private array $marketplaceTables = [
        'pharmacy_order_items',
        'pharmacy_orders',
        'pharmacy_medicines',
        'pharmacy_branches',
        'pharmacy_subscriptions',
        'pharmacies',
        'laboratory_order_results',
        'laboratory_order_items',
        'laboratory_orders',
        'laboratory_test_items',
        'laboratory_branches',
        'laboratory_subscriptions',
        'laboratories',
        'lab_tests',
        'lab_test_categories',
        'medicines',
        'medicine_categories',
    ];

    private array $marketplaceColumns = [
        'pharmacy_id',
        'pharmacy_order_id',
        'laboratory_id',
        'laboratory_order_id',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('medical_records') && Schema::hasColumn('medical_records', 'record_type')) {
            DB::table('medical_records')->whereIn('record_type', ['pharmacy_order', 'lab_result'])->delete();
        }

        if (Schema::hasTable('reviews')) {
            $q = DB::table('reviews');
            $q->where(function ($query) {
                foreach ($this->marketplaceColumns as $column) {
                    if (Schema::hasColumn('reviews', $column)) {
                        $query->orWhereNotNull($column);
                    }
                }
            })->delete();
        }

        $this->dropMarketplaceColumns('medical_records');
        $this->dropMarketplaceColumns('reviews');

        if (Schema::hasTable('medical_records') && Schema::hasColumn('medical_records', 'record_type')) {
            DB::statement("ALTER TABLE medical_records MODIFY COLUMN record_type ENUM('prescription','report','diagnosis') NOT NULL DEFAULT 'diagnosis'");
        }

        foreach ($this->marketplaceTables as $table) {
            Schema::dropIfExists($table);
        }

        $marketplaceUserIds = DB::table('users')
            ->whereIn('role', ['pharmacy', 'laboratory'])
            ->pluck('id');

        if ($marketplaceUserIds->isNotEmpty()) {
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->whereIn('tokenable_id', $marketplaceUserIds)->delete();
            }
            if (Schema::hasTable('device_tokens')) {
                DB::table('device_tokens')->whereIn('user_id', $marketplaceUserIds)->delete();
            }
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->whereIn('notifiable_id', $marketplaceUserIds)->delete();
            }
            DB::table('users')->whereIn('id', $marketplaceUserIds)->delete();
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient','doctor','admin') NOT NULL DEFAULT 'patient'");

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally empty — marketplace pharmacy/lab schema is not restored.
    }

    private function dropMarketplaceColumns(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter(
            $this->marketplaceColumns,
            fn (string $column) => Schema::hasColumn($table, $column)
        ));

        if ($existing === []) {
            return;
        }

        // Drop FK constraints by known Laravel naming convention, then columns.
        foreach ($existing as $column) {
            $constraint = "{$table}_{$column}_foreign";
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
            } catch (\Throwable) {
                // Constraint may already be gone
            }
        }

        Schema::table($table, function (Blueprint $blueprint) use ($existing) {
            $blueprint->dropColumn($existing);
        });
    }
};
