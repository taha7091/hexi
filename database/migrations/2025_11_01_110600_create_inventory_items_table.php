<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('inv_category_id');
            $table->unsignedBigInteger('inv_division_id');
            $table->unsignedBigInteger('inv_group_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('buying_unit_id');
            $table->unsignedBigInteger('stock_unit_id');
            $table->unsignedBigInteger('usage_unit_id');
            $table->decimal('buy_to_stock_factor', 12, 6)->default(1);
            $table->decimal('stock_to_usage_factor', 12, 6)->default(1);
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('current_stock', 15, 4)->default(0); // in stock unit
            $table->decimal('minimum_stock', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('inv_category_id')->references('id')->on('inv_categories')->onDelete('cascade');
            $table->foreign('inv_division_id')->references('id')->on('inv_divisions')->onDelete('cascade');
            $table->foreign('inv_group_id')->references('id')->on('inv_groups')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('inventory_locations')->onDelete('set null');
            $table->foreign('buying_unit_id')->references('id')->on('inventory_units')->onDelete('restrict');
            $table->foreign('stock_unit_id')->references('id')->on('inventory_units')->onDelete('restrict');
            $table->foreign('usage_unit_id')->references('id')->on('inventory_units')->onDelete('restrict');

            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'inv_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};

