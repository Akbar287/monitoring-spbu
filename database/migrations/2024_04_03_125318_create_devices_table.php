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
        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('devices_id')->primary();
            $table->uuid('penjadwalan_id');
            $table->string('ipv4', 16);
            $table->string('type_devices', 8);
            $table->string('model_devices', 20);
            $table->string('os', 64);
            $table->string('versi_os', 8);
            $table->string('browser', 32);
            $table->string('versi_browser', 8);
            $table->timestamps();

            $table->foreign('penjadwalan_id')->references('penjadwalan_id')->on('penjadwalan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
