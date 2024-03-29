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
        Schema::create('hari_jam_kerja', function (Blueprint $table) {
            $table->uuid('hari_id');
            $table->uuid('jam_kerja_id');
            $table->primary(['hari_id', 'jam_kerja_id']);

            $table->foreign('hari_id')->references('hari_id')->on('hari')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_jam_kerja');
    }
};
