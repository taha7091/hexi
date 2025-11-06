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
        Schema::create('role_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_role_id')->constrained()->onDelete('cascade');

            // POS Access Privileges
            $table->boolean('can_access_pos')->default(true);
            $table->boolean('can_logout_from_pos')->default(true);

            // Sales Privileges
            $table->boolean('can_view_sales')->default(true);
            $table->boolean('can_view_older_sales')->default(false);
            $table->boolean('can_create_sales')->default(true);
            $table->boolean('can_process_refunds')->default(false);
            $table->boolean('can_apply_discounts')->default(false);
            $table->boolean('can_void_transactions')->default(false);
            $table->boolean('can_modify_prices')->default(false);

            // End of Day Privileges
            $table->boolean('can_press_end_of_day')->default(false);
            $table->boolean('can_view_daily_reports')->default(false);
            $table->boolean('can_view_cash_drawer')->default(true);
            $table->boolean('can_open_cash_drawer')->default(false);

            // Product Privileges
            $table->boolean('can_view_products')->default(true);
            $table->boolean('can_add_products')->default(false);
            $table->boolean('can_edit_products')->default(false);
            $table->boolean('can_delete_products')->default(false);
            $table->boolean('can_manage_inventory')->default(false);

            // User Management Privileges
            $table->boolean('can_view_users')->default(false);
            $table->boolean('can_add_users')->default(false);
            $table->boolean('can_edit_users')->default(false);
            $table->boolean('can_delete_users')->default(false);

            // Reports Privileges
            $table->boolean('can_view_reports')->default(false);
            $table->boolean('can_export_reports')->default(false);
            $table->boolean('can_view_analytics')->default(false);

            // System Privileges
            $table->boolean('can_access_settings')->default(false);
            $table->boolean('can_backup_data')->default(false);
            $table->boolean('can_manage_branches')->default(false);
            $table->boolean('can_manage_categories')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_privileges');
    }
};
