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
        Schema::create('dispenser', function (Blueprint $table) {
            $table->uuid('dispenser_id')->primary();
            $table->uuid('payung_id');
            $table->string('nama_dispenser', 16);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');

            $table->foreign('payung_id')->references('payung_id')->on('payung')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispenser');
    }
};
