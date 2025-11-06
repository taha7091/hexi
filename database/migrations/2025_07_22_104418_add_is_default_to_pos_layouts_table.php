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
        Schema::table('pos_layouts', function (Blueprint $table) {
            // Add missing columns that the controller expects
            $table->string('name')->after('brand_id');
            $table->text('description')->nullable()->after('name');
            $table->boolean('is_default')->default(false)->after('description');

            // Add index for better performance when querying default layouts
            $table->index(['brand_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_layouts', function (Blueprint $table) {
            $table->dropIndex(['brand_id', 'is_default']);
            $table->dropColumn(['name', 'description', 'is_default']);
        });
    }
};
