<?php

namespace App\Support\Import;

use App\Models\Dombak;
use App\Models\FileLo;
use App\Models\JamKerja;
use App\Models\JenisLosses;
use App\Models\Nozzle;
use App\Models\TypeDokumenStok;
use RuntimeException;

/**
 * Resolver data master untuk proses import transaksi.
 *
 * Master (dombak, jam kerja, nozzle, dll.) TIDAK pernah dibuat/diubah di sini —
 * resolver hanya mencari record yang sudah ada berdasarkan nama dan melempar
 * pesan yang jelas bila tidak ditemukan, supaya error muncul per baris Excel.
 * Hasil lookup di-cache agar import ribuan baris tetap cepat.
 */
class MasterResolver
{
    private array $cache = [];

    public function dombak(string $nama): Dombak
    {
        return $this->remember('dombak', $nama, function () use ($nama) {
            $dombak = Dombak::where('nama_dombak', trim($nama))->first();
            if (is_null($dombak)) {
                throw new RuntimeException("Dombak \"$nama\" tidak ditemukan.");
            }

            return $dombak;
        });
    }

    public function jamKerja(string $nama): JamKerja
    {
        return $this->remember('jam_kerja', $nama, function () use ($nama) {
            $jamKerja = JamKerja::where('nama_jam_kerja', trim($nama))->first();
            if (is_null($jamKerja)) {
                throw new RuntimeException("Jam kerja \"$nama\" tidak ditemukan.");
            }

            return $jamKerja;
        });
    }

    public function nozzle(string $nama): Nozzle
    {
        return $this->remember('nozzle', $nama, function () use ($nama) {
            $nozzle = Nozzle::where('nama_nozzle', trim($nama))->first();
            if (is_null($nozzle)) {
                throw new RuntimeException("Nozzle \"$nama\" tidak ditemukan.");
            }

            return $nozzle;
        });
    }

    public function fileLo(string $noLo): FileLo
    {
        return $this->remember('file_lo', $noLo, function () use ($noLo) {
            $fileLo = FileLo::where('no_lo', trim($noLo))->first();
            if (is_null($fileLo)) {
                throw new RuntimeException("No. LO \"$noLo\" tidak ditemukan. Penerimaan harus mengacu ke LO yang sudah ada di sistem.");
            }

            return $fileLo;
        });
    }

    public function typeDokumen(string $typeMovement): TypeDokumenStok
    {
        return $this->remember('type_dokumen', $typeMovement, function () use ($typeMovement) {
            $type = TypeDokumenStok::where('type_movement', $typeMovement)->first();
            if (is_null($type)) {
                throw new RuntimeException("Type dokumen stok \"$typeMovement\" tidak ditemukan di master.");
            }

            return $type;
        });
    }

    public function jenisLosses(string $jenis): JenisLosses
    {
        return $this->remember('jenis_losses', $jenis, function () use ($jenis) {
            $jenisLosses = JenisLosses::where('jenis_loss', $jenis)->first();
            if (is_null($jenisLosses)) {
                throw new RuntimeException("Jenis losses \"$jenis\" tidak ditemukan di master.");
            }

            return $jenisLosses;
        });
    }

    /** Stok inventory milik dombak (untuk dokumen_stok). */
    public function stokInventory(Dombak $dombak)
    {
        $sid = $dombak->stok_inventory_dombak()->first();
        if (is_null($sid) || is_null($sid->stok_inventory()->first())) {
            throw new RuntimeException("Dombak \"{$dombak->nama_dombak}\" belum punya stok inventory.");
        }

        return $sid->stok_inventory()->first();
    }

    private function remember(string $bucket, string $key, callable $resolver)
    {
        $key = trim($key);
        if (! isset($this->cache[$bucket][$key])) {
            $this->cache[$bucket][$key] = $resolver();
        }

        return $this->cache[$bucket][$key];
    }
}
