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
        Schema::create('pajak_pembelian_lib', function (Blueprint $table) {
            $table->uuid('pajak_pembelian_lib_id')->primary();
            $table->double('ppn', 13, 2);
            $table->double('ppbkb', 13, 2);
            $table->double('pph', 13, 2);
            $table->boolean('is_aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_pembelian_lib');
    }
};
