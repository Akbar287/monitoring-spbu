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
        Schema::create('location', function (Blueprint $table) {
            $table->uuid('location_id')->primary();
            $table->uuid('penjadwalan_id');
            $table->decimal('lattitude', 8, 6);
            $table->decimal('longitude', 9, 6);
            $table->tinyInteger('gmt');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('penjadwalan_id')->references('penjadwalan_id')->on('penjadwalan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location');
    }
};
