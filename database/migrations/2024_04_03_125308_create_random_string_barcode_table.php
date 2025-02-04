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
        Schema::create('random_string_barcode', function (Blueprint $table) {
            $table->uuid('random_string_barcode_id')->primary();
            $table->uuid('validate_barcode_id');
            $table->uuid('penjadwalan_id');
            $table->string('barcode', 255);
            $table->timestamp('start');
            $table->timestamp('finish');
            $table->boolean('is_done')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('penjadwalan_id')->references('penjadwalan_id')->on('penjadwalan')->cascadeOnDelete();
            $table->foreign('validate_barcode_id')->references('validate_barcode_id')->on('validate_barcode')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('random_string_barcode');
    }
};
