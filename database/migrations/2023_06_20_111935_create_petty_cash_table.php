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
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->uuid('petty_cash_id')->primary();
            $table->uuid('spbu_id');
            $table->string('no_kode', 4);
            $table->string('no_bukti', 8);
            $table->text('keterangan');
            $table->decimal('jumlah', 16, 2);
            $table->enum('jenis', ['debit', 'kredit']);
            $table->date('tanggal');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash');
    }
};
