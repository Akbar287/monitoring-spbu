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
        Schema::create('jurnal_detail', function (Blueprint $table) {
            $table->char('jurnal_detail_id', 36)->primary();
            $table->char('jurnal_id', 36);
            $table->char('coa_id', 36);
            $table->smallInteger('urutan')->default(0);
            $table->decimal('debit', 18, 6)->default(0);
            $table->decimal('kredit', 18, 6)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('coa_id');
            $table->foreign('jurnal_id')->references('jurnal_id')->on('jurnal')->cascadeOnDelete();
            // akun yang sudah dipakai jurnal tidak boleh dihapus
            $table->foreign('coa_id')->references('coa_id')->on('coa')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_detail');
    }
};
