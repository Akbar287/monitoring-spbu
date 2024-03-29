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
        Schema::create('dombak_payung', function (Blueprint $table) {
            $table->uuid('dombak_id');
            $table->uuid('payung_id');
            $table->primary(['payung_id', 'dombak_id']);

            $table->foreign('dombak_id')->references('dombak_id')->on('dombak')->cascadeOnDelete();
            $table->foreign('payung_id')->references('payung_id')->on('payung')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dombak_payung');
    }
};
