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
        Schema::create('mesin_presensi', function (Blueprint $table) {
            $table->uuid('mesin_presensi_id')->primary();
            $table->string('nama_mesin');
            $table->string('tipe_koneksi')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('com_port')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesin_presensi');
    }
};
