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
        Schema::create('konversi', function (Blueprint $table) {
            $table->uuid('konversi_id')->primary();
            $table->uuid('satuan_ukur_tinggi_id');
            $table->uuid('satuan_ukur_volume_id');
            $table->uuid('dombak_id');
            $table->decimal('tinggi', 16, 2);
            $table->decimal('volume', 16, 2);

            $table->foreign('satuan_ukur_tinggi_id')->references('satuan_ukur_tinggi_id')->on('satuan_ukur_tinggi')->cascadeOnDelete();
            $table->foreign('satuan_ukur_volume_id')->references('satuan_ukur_volume_id')->on('satuan_ukur_volume')->cascadeOnDelete();
            $table->foreign('dombak_id')->references('dombak_id')->on('dombak')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konversi');
    }
};
