<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catatan: kolom id memakai char(36) (bukan tipe uuid native) agar cocok
     * dengan tipe kolom id tabel-tabel lama di database (hasil restore dump
     * bertipe character(36)); FK dan join lintas tabel butuh tipe yang sama.
     */
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->char('coa_id', 36)->primary();
            $table->char('parent_coa_id', 36)->nullable();
            $table->string('kode_akun', 16)->unique();
            $table->string('nama_akun', 128);
            $table->enum('tipe_akun', ['aset', 'kewajiban', 'modal', 'pendapatan', 'hpp', 'beban', 'pendapatan_lain', 'beban_lain']);
            $table->enum('saldo_normal', ['debit', 'kredit']);
            $table->enum('kategori_arus_kas', ['operasi', 'investasi', 'pendanaan'])->nullable();
            $table->boolean('is_kas')->default(false);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // FK ke diri sendiri harus ditambahkan setelah primary key terbentuk;
        // fluent ->primary() baru dieksekusi setelah perintah foreign() di dalam closure create.
        Schema::table('coa', function (Blueprint $table) {
            $table->foreign('parent_coa_id')->references('coa_id')->on('coa')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
