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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('sale_number')->unique()->after('id');
            $table->decimal('subtotal', 12, 2)->default(0)->after('user_id');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('total_amount', 12, 2)->default(0)->after('discount_amount');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending')->after('total_amount');
            $table->timestamp('sale_date')->useCurrent()->after('payment_status');
            $table->text('notes')->nullable()->after('sale_date');

            // Drop old columns if they exist
            $table->dropColumn(['total', 'sold_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'sale_number',
                'subtotal',
                'tax_amount',
                'discount_amount',
                'total_amount',
                'payment_status',
                'sale_date',
                'notes'
            ]);

            // Restore old columns
            $table->decimal('total', 12, 2);
            $table->timestamp('sold_at')->useCurrent();
        });
    }
};
