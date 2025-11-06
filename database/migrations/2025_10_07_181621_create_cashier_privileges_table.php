<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cashier_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('managed_by')->constrained('users')->onDelete('cascade'); // Manager who set these privileges

            // Sales privileges
            $table->boolean('can_view_older_sales')->default(true);
            $table->boolean('can_process_refunds')->default(false);
            $table->boolean('can_apply_discounts')->default(false);
            $table->boolean('can_void_transactions')->default(false);
            $table->boolean('can_modify_prices')->default(false);

            // End of day privileges
            $table->boolean('can_press_end_of_day')->default(false);
            $table->boolean('can_view_daily_reports')->default(false);
            $table->boolean('can_view_cash_drawer')->default(true);

            // Product privileges
            $table->boolean('can_add_products')->default(false);
            $table->boolean('can_edit_products')->default(false);
            $table->boolean('can_manage_inventory')->default(false);

            // System privileges
            $table->boolean('can_access_settings')->default(false);
            $table->boolean('can_backup_data')->default(false);

            $table->timestamps();

            // Ensure one privilege record per cashier per company
            $table->unique(['company_id', 'cashier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_privileges');
    }
};
