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
        Schema::create('detail_gaji', function (Blueprint $table) {
            $table->uuid('detail_gaji_id')->primary();
            $table->uuid('gaji_member_id');
            $table->uuid('nama_gaji');
            $table->enum('type', ['lebih', 'kurang']);
            $table->decimal('total', 16, 6);

            $table->foreign('gaji_member_id')->references('gaji_member_id')->on('gaji_member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_gaji');
    }
};
