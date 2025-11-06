<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inv_divisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inv_category_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('inv_category_id')->references('id')->on('inv_categories')->onDelete('cascade');
            $table->unique(['inv_category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_divisions');
    }
};

