<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // SQLite does not support MODIFY/ALTER COLUMN type changes; skip safely
        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            // Increase precision for inventory stock quantities
            if (Schema::hasTable('inventory_items')) {
                DB::statement("ALTER TABLE inventory_items MODIFY current_stock DECIMAL(15,6) NOT NULL DEFAULT 0");
                DB::statement("ALTER TABLE inventory_items MODIFY minimum_stock DECIMAL(15,6) NOT NULL DEFAULT 0");
            }

            // Increase precision for adjustments audit trail
            if (Schema::hasTable('inventory_adjustments')) {
                DB::statement("ALTER TABLE inventory_adjustments MODIFY quantity_before DECIMAL(15,6) NOT NULL DEFAULT 0");
                DB::statement("ALTER TABLE inventory_adjustments MODIFY quantity_after DECIMAL(15,6) NOT NULL DEFAULT 0");
                DB::statement("ALTER TABLE inventory_adjustments MODIFY adjustment_amount DECIMAL(15,6) NOT NULL DEFAULT 0");
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // SQLite does not support MODIFY/ALTER COLUMN type changes; skip safely
        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            // Revert precision changes
            if (Schema::hasTable('inventory_items')) {
                DB::statement("ALTER TABLE inventory_items MODIFY current_stock DECIMAL(15,4) NOT NULL DEFAULT 0");
                DB::statement("ALTER TABLE inventory_items MODIFY minimum_stock DECIMAL(15,4) NOT NULL DEFAULT 0");
            }

            if (Schema::hasTable('inventory_adjustments')) {
                DB::statement("ALTER TABLE inventory_adjustments MODIFY quantity_before DECIMAL(15,4) NOT NULL DEFAULT 0");
                DB::statement("ALTER TABLE inventory_adjustments MODIFY quantity_after DECIMAL(15,4) NOT NULL DEFAULT 0");
                DB::statement("ALTER TABLE inventory_adjustments MODIFY adjustment_amount DECIMAL(15,4) NOT NULL DEFAULT 0");
            }
        }
    }
};

