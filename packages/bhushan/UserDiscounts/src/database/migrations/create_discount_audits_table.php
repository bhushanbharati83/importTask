<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('discount_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('discount_audits');
    }
};
