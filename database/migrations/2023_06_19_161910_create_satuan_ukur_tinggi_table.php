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
        Schema::create('satuan_ukur_tinggi', function (Blueprint $table) {
            $table->uuid('satuan_ukur_tinggi_id')->primary();
            $table->string('nama_satuan', 64);
            $table->string('singkatan', 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan_ukur_tinggi');
    }
};
