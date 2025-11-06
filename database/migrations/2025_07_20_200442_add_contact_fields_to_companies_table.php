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
            $table->string('status')->default('active')->after('pos_limit');
            $table->string('contact_email')->nullable()->after('status');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->text('address')->nullable()->after('contact_phone');
            $table->text('notes')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['status', 'contact_email', 'contact_phone', 'address', 'notes']);
        });
    }
};
