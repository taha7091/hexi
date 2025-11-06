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
        Schema::create('screen_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('screen_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('screen_group_id')->nullable();
            $table->string('type')->default('product'); // product, group, modifier, action
            $table->string('display_name');
            $table->string('background_color')->default('#007bff');
            $table->string('text_color')->default('#ffffff');
            $table->integer('grid_x'); // X position in grid
            $table->integer('grid_y'); // Y position in grid
            $table->integer('width')->default(1); // Width in grid units
            $table->integer('height')->default(1); // Height in grid units
            $table->json('custom_properties')->nullable(); // Store additional properties
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('screen_id')->references('id')->on('screens')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('screen_group_id')->references('id')->on('screen_groups')->onDelete('cascade');
            
            $table->index(['screen_id', 'is_active']);
            $table->index(['grid_x', 'grid_y']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screen_items');
    }
};
