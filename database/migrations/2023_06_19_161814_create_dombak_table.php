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
        Schema::create('dombak', function (Blueprint $table) {
            $table->uuid('dombak_id')->primary();
            $table->uuid('spbu_id');
            $table->string('nama_dombak', 16);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dombak');
    }
};
