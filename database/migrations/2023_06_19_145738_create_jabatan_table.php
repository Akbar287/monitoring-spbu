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
        Schema::create('jabatan', function (Blueprint $table) {
            $table->uuid('jabatan_id')->primary();
            $table->uuid('level_id');
            $table->string('nama_jabatan', 64);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('level_id')->references('level_id')->on('level')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan');
    }
};
