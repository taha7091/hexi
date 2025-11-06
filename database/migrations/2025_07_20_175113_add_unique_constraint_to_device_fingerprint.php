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
        // First, clean up any duplicate fingerprints that might exist
        DB::statement("
            UPDATE pos_devices 
            SET device_fingerprint = NULL 
            WHERE device_fingerprint IN (
                SELECT device_fingerprint 
                FROM (
                    SELECT device_fingerprint 
                    FROM pos_devices 
                    WHERE device_fingerprint IS NOT NULL 
                    GROUP BY device_fingerprint 
                    HAVING COUNT(*) > 1
                ) as duplicates
            ) 
            AND is_activated = 0
        ");

        Schema::table('pos_devices', function (Blueprint $table) {
            // Add unique constraint to device_fingerprint for activated devices
            // We'll use a partial unique index that only applies to activated devices
            $table->unique(['device_fingerprint', 'is_activated'], 'unique_active_fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            $table->dropUnique('unique_active_fingerprint');
        });
    }
};