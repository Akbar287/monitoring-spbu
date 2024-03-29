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
        Schema::create('ktp', function (Blueprint $table) {
            $table->uuid('ktp_id')->primary();
            $table->string('nik', 16);
            $table->string('nama', 128);
            $table->enum('jenis_kelamin', ['pria', 'wanita']);
            $table->string('tempat_lahir', 128);
            $table->date('tanggal_lahir');
            $table->boolean('verified');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ktp');
    }
};
