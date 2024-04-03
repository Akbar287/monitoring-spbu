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
        Schema::create('penjadwalan_nozzle', function (Blueprint $table) {
            $table->uuid('penjadwalan_id');
            $table->uuid('nozzle_id');
            $table->primary(['penjadwalan_id', 'nozzle_id']);

            $table->foreign('penjadwalan_id')->references('penjadwalan_id')->on('penjadwalan')->cascadeOnDelete();
            $table->foreign('nozzle_id')->references('nozzle_id')->on('nozzle')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjadwalan_nozzle');
    }
};
