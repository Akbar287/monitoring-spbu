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
        Schema::create('penerimaan_petty_cash', function (Blueprint $table) {
            $table->uuid('penerimaan_id');
            $table->uuid('petty_cash_id');
            $table->primary(['penerimaan_id', 'petty_cash_id']);

            $table->foreign('penerimaan_id')->references('penerimaan_id')->on('penerimaan')->cascadeOnDelete();
            $table->foreign('petty_cash_id')->references('petty_cash_id')->on('petty_cash')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_petty_cash');
    }
};
