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
        Schema::table('companies', function (Blueprint $table) {
            // Transaction limits
            $table->decimal('daily_sales_limit', 15, 2)->nullable()->after('pos_limit')->comment('Maximum daily sales amount');
            $table->integer('daily_transaction_limit')->nullable()->after('daily_sales_limit')->comment('Maximum daily transaction count');
            $table->decimal('per_transaction_limit', 15, 2)->nullable()->after('daily_transaction_limit')->comment('Maximum amount per single transaction');
            $table->boolean('enforce_limits')->default(true)->after('per_transaction_limit')->comment('Whether to enforce POS limits');
            
            // Add indexes for better performance
            $table->index(['daily_sales_limit', 'daily_transaction_limit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['daily_sales_limit', 'daily_transaction_limit']);
            $table->dropColumn([
                'daily_sales_limit',
                'daily_transaction_limit', 
                'per_transaction_limit',
                'enforce_limits'
            ]);
        });
    }
};
