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
        Schema::table('role_privileges', function (Blueprint $table) {
            if (!Schema::hasColumn('role_privileges', 'can_manage_pos_layouts')) {
                $table->boolean('can_manage_pos_layouts')->default(false)->after('can_manage_categories');
            }

            if (!Schema::hasColumn('role_privileges', 'can_manage_screen_setup')) {
                $table->boolean('can_manage_screen_setup')->default(false)->after('can_manage_pos_layouts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_privileges', function (Blueprint $table) {
            if (Schema::hasColumn('role_privileges', 'can_manage_pos_layouts')) {
                $table->dropColumn('can_manage_pos_layouts');
            }

            if (Schema::hasColumn('role_privileges', 'can_manage_screen_setup')) {
                $table->dropColumn('can_manage_screen_setup');
            }
        });
    }
};
