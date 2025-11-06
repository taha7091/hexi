<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('pos_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('limit')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'brand_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_limits');
    }
}
