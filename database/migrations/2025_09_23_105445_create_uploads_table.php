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
        Schema::create('uploads', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('filename');
            $t->bigInteger('size');
            $t->string('status')->default('in_progress');
            $t->string('checksum')->nullable();
            $t->bigInteger('received_bytes')->default(0);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
