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
        Schema::create('stand_meter', function (Blueprint $table) {
            $table->uuid('stand_meter_id')->primary();
            $table->uuid('nozzle_id');
            $table->uuid('jam_kerja_id');
            $table->uuid('dombak_id');
            $table->date('tanggal');
            $table->decimal('stand_meter_awal', 16, 6);
            $table->decimal('stand_meter_akhir', 16, 6)->nullable();
            $table->tinyInteger('urutan');
            $table->boolean('is_confirmation');
            $table->timestamps();

            $table->foreign('dombak_id')->references('dombak_id')->on('dombak')->cascadeOnDelete();
            $table->foreign('nozzle_id')->references('nozzle_id')->on('nozzle')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stand_meter');
    }
};
