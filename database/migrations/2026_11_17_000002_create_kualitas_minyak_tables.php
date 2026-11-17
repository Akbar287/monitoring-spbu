<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kualitas_minyak', function (Blueprint $table) {
            $table->char('kualitas_minyak_id', 36)->primary();
            $table->char('spbu_id', 36);
            $table->char('dombak_id', 36);
            $table->char('produk_id', 36);
            // Identitas pada saat pencatatan, tetap utuh bila master kemudian berubah.
            $table->string('nomor_spbu');
            $table->string('nama_dombak');
            $table->string('nama_produk');
            $table->date('tanggal');
            $table->time('jam_awal');
            $table->decimal('density_awal', 7, 4);
            $table->decimal('suhu_awal', 5, 2);
            $table->decimal('density_15_awal', 7, 4);
            $table->foreignId('astm_awal_id')->constrained('astm_density')->restrictOnDelete();
            $table->decimal('tinggi_air', 8, 2);
            $table->text('catatan')->nullable();
            $table->char('dibuat_oleh', 36);
            $table->string('nama_operator');
            $table->char('disetujui_oleh', 36)->nullable();
            $table->string('nama_penyetuju')->nullable();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();
            $table->unique(['dombak_id', 'tanggal']);
            $table->index(['disetujui_pada', 'tanggal']);
            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->restrictOnDelete();
            $table->foreign('dombak_id')->references('dombak_id')->on('dombak')->restrictOnDelete();
            $table->foreign('produk_id')->references('produk_id')->on('produk')->restrictOnDelete();
            $table->foreign('dibuat_oleh')->references('user_login_id')->on('userlogin')->restrictOnDelete();
            $table->foreign('disetujui_oleh')->references('user_login_id')->on('userlogin')->restrictOnDelete();
        });

        Schema::create('kualitas_minyak_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->char('kualitas_minyak_id', 36);
            $table->dateTime('waktu_penerimaan');
            $table->string('nomor_mobil_tangki', 32);
            $table->string('nomor_pnbp', 64);
            $table->boolean('truk_berwenang')->default(true);
            $table->decimal('density_15_pnbp', 7, 4);
            $table->decimal('density_penerimaan', 7, 4);
            $table->decimal('suhu_penerimaan', 5, 2);
            $table->decimal('density_15_penerimaan', 7, 4);
            $table->foreignId('astm_penerimaan_id')->constrained('astm_density')->restrictOnDelete();
            $table->decimal('selisih_density', 7, 4);
            $table->dateTime('waktu_akhir')->nullable();
            $table->decimal('density_akhir', 7, 4)->nullable();
            $table->decimal('suhu_akhir', 5, 2)->nullable();
            $table->decimal('density_15_akhir', 7, 4)->nullable();
            $table->foreignId('astm_akhir_id')->nullable()->constrained('astm_density')->restrictOnDelete();
            // Kolom pendukung aturan penolakan volume pada catatan kaki formulir.
            $table->decimal('volume_pnbp', 12, 3)->nullable();
            $table->decimal('volume_diterima', 12, 3)->nullable();
            $table->foreign('kualitas_minyak_id')->references('kualitas_minyak_id')->on('kualitas_minyak')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kualitas_minyak_penerimaan');
        Schema::dropIfExists('kualitas_minyak');
    }
};
