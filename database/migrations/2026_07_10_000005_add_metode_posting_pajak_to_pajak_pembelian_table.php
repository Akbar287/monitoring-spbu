<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Diberi guard hasColumn karena migrasi ini bisa sudah ter-apply di database
     * yang pencatatan migrasinya sempat gagal (lihat 2026_07_10_000000).
     */
    public function up(): void
    {
        if (Schema::hasColumn('pajak_pembelian', 'metode_posting_pajak')) {
            return;
        }

        Schema::table('pajak_pembelian', function (Blueprint $table) {
            // hpp      : PPN & PPBKB dikapitalisasi ke Persediaan (masuk HPP saat terjual)
            // terpisah : PPN ke PPN Masukan (aset), PPBKB ke Beban PPBKB
            // PPh 22 selalu diposting terpisah (Beban PPh Final) sesuai standar akuntansi —
            // pajak penghasilan tidak boleh menjadi komponen nilai persediaan.
            $table->string('metode_posting_pajak', 16)->default('hpp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pajak_pembelian', function (Blueprint $table) {
            $table->dropColumn('metode_posting_pajak');
        });
    }
};
