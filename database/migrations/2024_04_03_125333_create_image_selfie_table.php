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
        Schema::create('image_selfie', function (Blueprint $table) {
            $table->uuid('image_selfie_id')->primary();
            $table->uuid('penjadwalan_id');
            $table->string('nama_file', 128);

            $table->foreign('penjadwalan_id')->references('penjadwalan_id')->on('penjadwalan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_selfie');
    }
};
