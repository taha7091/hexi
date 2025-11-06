<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_purchase_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->decimal('quantity_buying', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('line_total', 15, 4)->default(0);
            $table->timestamps();

            $table->foreign('inventory_purchase_id')->references('id')->on('inventory_purchases')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_items');
    }
};

