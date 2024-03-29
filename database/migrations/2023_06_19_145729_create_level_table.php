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
        Schema::create('level', function (Blueprint $table) {
            $table->uuid('level_id')->primary();
            $table->uuid('divisi_id');
            $table->string('nama_level', 64);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('divisi_id')->references('divisi_id')->on('divisi')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level');
    }
};
