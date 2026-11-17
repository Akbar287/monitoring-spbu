<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom persetujuan pada kualitas_minyak sekarang khusus pemeriksaan Pagi.
        Schema::table('kualitas_minyak_penerimaan', function (Blueprint $table) {
            foreach (['penerimaan', 'akhir'] as $tahap) {
                $table->char($tahap.'_dibuat_oleh', 36)->nullable();
                $table->string($tahap.'_nama_operator')->nullable();
                $table->char($tahap.'_disetujui_oleh', 36)->nullable();
                $table->string($tahap.'_nama_penyetuju')->nullable();
                $table->timestamp($tahap.'_disetujui_pada')->nullable();
                $table->text('catatan_'.$tahap)->nullable();
                $table->foreign($tahap.'_dibuat_oleh')->references('user_login_id')->on('userlogin')->restrictOnDelete();
                $table->foreign($tahap.'_disetujui_oleh')->references('user_login_id')->on('userlogin')->restrictOnDelete();
                $table->index($tahap.'_disetujui_pada');
            }
        });

        // Pertahankan persetujuan lama pada semua pengukuran yang saat itu sudah ada.
        DB::table('kualitas_minyak_penerimaan')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $parent = DB::table('kualitas_minyak')->where('kualitas_minyak_id', $row->kualitas_minyak_id)->first();
                $data = [];
                foreach (['penerimaan', 'akhir'] as $tahap) {
                    if ($tahap === 'akhir' && ! $row->waktu_akhir) {
                        continue;
                    }
                    $data[$tahap.'_dibuat_oleh'] = $parent->dibuat_oleh;
                    $data[$tahap.'_nama_operator'] = $parent->nama_operator;
                    $data[$tahap.'_disetujui_oleh'] = $parent->disetujui_oleh;
                    $data[$tahap.'_nama_penyetuju'] = $parent->nama_penyetuju;
                    $data[$tahap.'_disetujui_pada'] = $parent->disetujui_pada;
                }
                DB::table('kualitas_minyak_penerimaan')->where('id', $row->id)->update($data);
            }
        });
    }

    public function down(): void
    {
        Schema::table('kualitas_minyak_penerimaan', function (Blueprint $table) {
            foreach (['penerimaan', 'akhir'] as $tahap) {
                $table->dropForeign([$tahap.'_dibuat_oleh']);
                $table->dropForeign([$tahap.'_disetujui_oleh']);
                $table->dropIndex([$tahap.'_disetujui_pada']);
                $table->dropColumn([$tahap.'_dibuat_oleh', $tahap.'_nama_operator', $tahap.'_disetujui_oleh', $tahap.'_nama_penyetuju', $tahap.'_disetujui_pada', 'catatan_'.$tahap]);
            }
        });
    }
};
