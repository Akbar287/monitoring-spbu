<?php

namespace App\Imports\Transaksi;

/**
 * Import Penerimaan BBM historis (penambahan stok dombak).
 *
 * Tiap baris membuat dokumen_stok "Good Receive Penerimaan" (stok_awal =
 * stok sebelum, stok_akhir = stok setelah), losses "Good Issue Penerimaan" dari
 * selisih antara penambahan aktual stok dan jumlah penerimaan yang tercatat,
 * lalu record penerimaan yang mengacu ke LO (no_lo) yang sudah ada di sistem.
 *
 * Catatan: dokumen rekonsiliasi "Stok Taking Good Receive Penerimaan/Penyesuaian"
 * dari alur live tidak dibuat di sini karena impor data lama memakai nilai
 * eksplisit, bukan stok berjalan. No. LO wajib sudah ada (master tidak dibuat).
 */
class PenerimaanImporter extends AbstractTransaksiImporter
{
    public static function headers(): array
    {
        return ['tanggal', 'jam_kerja', 'dombak', 'no_lo', 'stok_sebelumnya', 'jumlah_penerimaan', 'stok_setelah'];
    }

    public static function example(): array
    {
        return ['2024-05-01', 'Jam Kerja 1', 'Dombak 1', 'LO-001', '1303.1', '8000', '9237'];
    }

    protected function handleRow(array $row, string $memberId): void
    {
        $tanggal = $this->tanggal($row['tanggal']);
        $jamKerja = $this->master->jamKerja($this->text($row['jam_kerja'], 'jam_kerja'));
        $dombak = $this->master->dombak($this->text($row['dombak'], 'dombak'));
        $fileLo = $this->master->fileLo($this->text($row['no_lo'], 'no_lo'));
        $sebelum = $this->num($row['stok_sebelumnya'], 'stok_sebelumnya');
        $jumlah = $this->num($row['jumlah_penerimaan'], 'jumlah_penerimaan');
        $setelah = $this->num($row['stok_setelah'], 'stok_setelah');

        $type = $this->master->typeDokumen('Good Receive Penerimaan');
        $stokInventory = $this->master->stokInventory($dombak);

        $dokumenStok = $type->dokumen_stok()
            ->where('stok_inventory_id', $stokInventory->stok_inventory_id)
            ->where('member_id', $memberId)
            ->where('jam_kerja_id', $jamKerja->jam_kerja_id)
            ->where('dombak_id', $dombak->dombak_id)
            ->where('tanggal', $tanggal)
            ->where('stok_awal', $sebelum)
            ->where('stok_akhir', $setelah)
            ->where('is_confirmation', 0)
            ->first();

        if (is_null($dokumenStok)) {
            $dokumenStok = $type->dokumen_stok()->create([
                'stok_inventory_id' => $stokInventory->stok_inventory_id,
                'member_id' => $memberId,
                'jam_kerja_id' => $jamKerja->jam_kerja_id,
                'dombak_id' => $dombak->dombak_id,
                'tanggal' => $tanggal,
                'stok_awal' => $sebelum,
                'stok_akhir' => $setelah,
                'is_confirmation' => 0,
                'waktu' => $jamKerja->jam_datang_in,
            ]);
        }

        $this->linkKonversi($dombak, $dokumenStok, $setelah);

        // Selisih penerimaan aktual vs tercatat (PnL Good Receive).
        $selisih = ($setelah - $sebelum) - $jumlah;
        $jenisLosses = $this->master->jenisLosses('Good Issue Penerimaan');
        $simbol = $selisih > 0 ? 'lebih' : 'kurang';

        $adaLosses = $dokumenStok->losses()
            ->where('jenis_losses_id', $jenisLosses->jenis_losses_id)
            ->where('tanggal', $tanggal)
            ->where('simbol', $simbol)
            ->where('jumlah', abs($selisih))
            ->exists();
        if (! $adaLosses) {
            $dokumenStok->losses()->create([
                'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                'tanggal' => $tanggal,
                'simbol' => $simbol,
                'jumlah' => abs($selisih),
            ]);
        }

        $penerimaan = $fileLo->penerimaan()
            ->where('dokumen_stok_id', $dokumenStok->dokumen_stok_id)
            ->where('tanggal', $tanggal)
            ->first();
        if (is_null($penerimaan)) {
            $fileLo->penerimaan()->create([
                'dokumen_stok_id' => $dokumenStok->dokumen_stok_id,
                'tanggal' => $tanggal,
            ]);
        }
    }
}
