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
        Schema::create('file_stand_meter', function (Blueprint $table) {
            $table->uuid('file_stand_meter_id')->primary();
            $table->uuid('stand_meter_id');
            $table->string('nama_file', 128);
            $table->string('nama_dokumen', 128);
            $table->date('tanggal_upload');
            $table->timestamps();

            $table->foreign('stand_meter_id')->references('stand_meter_id')->on('stand_meter')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_stand_meter');
    }
};
