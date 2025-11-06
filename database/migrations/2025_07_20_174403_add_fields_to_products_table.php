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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->string('sku')->nullable()->unique()->after('cost_price');
            $table->string('barcode')->nullable()->unique()->after('sku');
            $table->integer('stock_quantity')->nullable()->default(0)->after('barcode');
            $table->integer('min_stock_level')->nullable()->after('stock_quantity');
            $table->boolean('is_active')->default(true)->after('min_stock_level');
            $table->string('image_url')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'sku',
                'barcode',
                'stock_quantity',
                'min_stock_level',
                'is_active',
                'image_url'
            ]);
        });
    }
};
