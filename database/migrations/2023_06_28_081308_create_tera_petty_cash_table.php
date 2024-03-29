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
        Schema::create('tera_petty_cash', function (Blueprint $table) {
            $table->uuid('tera_id');
            $table->uuid('petty_cash_id');

            $table->primary(['tera_id', 'petty_cash_id']);

            $table->foreign('tera_id')->references('tera_id')->on('tera')->cascadeOnDelete();
            $table->foreign('petty_cash_id')->references('petty_cash_id')->on('petty_cash')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tera_petty_cash');
    }
};
