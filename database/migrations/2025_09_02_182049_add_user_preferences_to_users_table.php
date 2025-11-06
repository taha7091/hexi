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
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_preference')->nullable()->after('remember_token');
            $table->boolean('notifications_enabled')->default(true)->after('theme_preference');
            $table->boolean('email_notifications')->default(true)->after('notifications_enabled');
            $table->string('timezone')->default('UTC')->after('email_notifications');
            $table->string('date_format')->default('Y-m-d')->after('timezone');
            $table->string('time_format')->default('H:i:s')->after('date_format');
            $table->string('language_preference')->nullable()->after('time_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'theme_preference',
                'notifications_enabled',
                'email_notifications',
                'timezone',
                'date_format',
                'time_format',
                'language_preference'
            ]);
        });
    }
};
