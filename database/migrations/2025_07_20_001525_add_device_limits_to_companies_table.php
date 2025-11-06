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
            $table->integer('max_devices')->default(1)->after('pos_limit');
            $table->integer('active_devices')->default(0)->after('max_devices');
            $table->boolean('allow_device_transfer')->default(false)->after('active_devices');
            $table->json('device_restrictions')->nullable()->after('allow_device_transfer');
            
            // Add index for better performance
            $table->index(['max_devices', 'active_devices']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['max_devices', 'active_devices']);
            $table->dropColumn([
                'max_devices',
                'active_devices', 
                'allow_device_transfer',
                'device_restrictions'
            ]);
        });
    }
};