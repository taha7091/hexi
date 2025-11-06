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
            $table->string('activation_key')->unique()->nullable()->after('device_info');
            $table->timestamp('activated_at')->nullable()->after('activation_key');
            $table->string('device_fingerprint')->nullable()->after('activated_at');
            $table->string('ip_address')->nullable()->after('device_fingerprint');
            $table->json('hardware_info')->nullable()->after('ip_address');
            $table->boolean('is_activated')->default(false)->after('hardware_info');
            $table->timestamp('last_seen')->nullable()->after('is_activated');
            $table->string('app_version')->nullable()->after('last_seen');
            
            // Add index for better performance
            $table->index(['is_activated', 'status']);
            $table->index(['device_fingerprint']);
            $table->index(['activation_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            $table->dropIndex(['is_activated', 'status']);
            $table->dropIndex(['device_fingerprint']);
            $table->dropIndex(['activation_key']);
            
            $table->dropColumn([
                'activation_key',
                'activated_at',
                'device_fingerprint',
                'ip_address',
                'hardware_info',
                'is_activated',
                'last_seen',
                'app_version'
            ]);
        });
    }
};