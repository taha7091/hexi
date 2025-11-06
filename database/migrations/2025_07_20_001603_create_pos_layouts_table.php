<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosLayoutsTable extends Migration
{
    public function up()
    {
        Schema::create('pos_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->json('layout_data');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_layouts');
    }
}
