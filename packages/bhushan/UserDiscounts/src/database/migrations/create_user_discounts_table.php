<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('discount_id');
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'discount_id']);
        });
    }
    public function down()
    {
        Schema::dropIfExists('user_discounts');
    }
};
