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
        Schema::table('pos_devices', function (Blueprint $table) {
            // Add MAC address field
            $table->string('mac_address')->nullable()->after('device_fingerprint');
            
            // Add fields for admin reset tracking
            $table->timestamp('mac_address_reset_at')->nullable()->after('mac_address');
            $table->foreignId('mac_address_reset_by')->nullable()->after('mac_address_reset_at')
                  ->constrained('users')->nullOnDelete();
            
            // Add index for better performance
            $table->index(['mac_address']);
            $table->index(['is_activated', 'mac_address']);
        });

        // Add partial unique index for activated devices with MAC addresses
        Schema::table('pos_devices', function (Blueprint $table) {
            $table->unique(['mac_address', 'is_activated'], 'unique_mac_activated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['mac_address']);
            $table->dropIndex(['is_activated', 'mac_address']);
            $table->dropUnique('unique_mac_activated');
            
            // Drop columns
            $table->dropColumn([
                'mac_address',
                'mac_address_reset_at',
                'mac_address_reset_by'
            ]);
        });
    }
};
