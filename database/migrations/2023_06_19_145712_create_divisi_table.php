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
        Schema::create('divisi', function (Blueprint $table) {
            $table->uuid('divisi_id')->primary();
            $table->uuid('spbu_id');
            $table->string('nama_divisi', 64);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisi');
    }
};
