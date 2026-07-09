<?php

namespace App\Imports\Transaksi;

/**
 * Import Stok Taking historis.
 *
 * Tiap baris membuat 1 dokumen_stok bertipe "Stok Taking" dengan stok_awal &
 * stok_akhir eksplisit (liter), plus catatan losses (lebih/kurang) dari selisih
 * keduanya, dan—bila ada—relasi ke baris konversi dombak yang volumenya cocok.
 * Logika ini meniru StokTakingController@store namun memakai nilai eksplisit
 * agar cocok untuk data lama (tidak bergantung pada stok berjalan).
 */
class StokTakingImporter extends AbstractTransaksiImporter
{
    public static function headers(): array
    {
        return ['tanggal', 'jam_kerja', 'dombak', 'stok_awal', 'stok_akhir'];
    }

    public static function example(): array
    {
        return ['2024-05-01', 'Jam Kerja 1', 'Dombak 1', '3137.5', '3152'];
    }

    protected function handleRow(array $row, string $memberId): void
    {
        $tanggal = $this->tanggal($row['tanggal']);
        $jamKerja = $this->master->jamKerja($this->text($row['jam_kerja'], 'jam_kerja'));
        $dombak = $this->master->dombak($this->text($row['dombak'], 'dombak'));
        $awal = $this->num($row['stok_awal'], 'stok_awal');
        $akhir = $this->num($row['stok_akhir'], 'stok_akhir');

        $type = $this->master->typeDokumen('Stok Taking');
        $stokInventory = $this->master->stokInventory($dombak);

        $dokumenStok = $type->dokumen_stok()
            ->where('stok_inventory_id', $stokInventory->stok_inventory_id)
            ->where('jam_kerja_id', $jamKerja->jam_kerja_id)
            ->where('member_id', $memberId)
            ->where('dombak_id', $dombak->dombak_id)
            ->where('tanggal', $tanggal)
            ->where('stok_awal', $awal)
            ->where('stok_akhir', $akhir)
            ->where('is_confirmation', 0)
            ->first();

        if (is_null($dokumenStok)) {
            $dokumenStok = $type->dokumen_stok()->create([
                'stok_inventory_id' => $stokInventory->stok_inventory_id,
                'jam_kerja_id' => $jamKerja->jam_kerja_id,
                'member_id' => $memberId,
                'dombak_id' => $dombak->dombak_id,
                'tanggal' => $tanggal,
                'stok_awal' => $awal,
                'stok_akhir' => $akhir,
                'is_confirmation' => 0,
                'waktu' => $jamKerja->jam_datang_in,
            ]);
        }

        $this->linkKonversi($dombak, $dokumenStok, $akhir);

        $jenisLosses = $this->master->jenisLosses('Stok Taking');
        $selisih = $akhir - $awal;
        $simbol = $selisih < 0 ? 'kurang' : 'lebih';
        $jumlah = abs($selisih);

        $adaLosses = $dokumenStok->losses()
            ->where('jenis_losses_id', $jenisLosses->jenis_losses_id)
            ->where('tanggal', $tanggal)
            ->where('simbol', $simbol)
            ->where('jumlah', $jumlah)
            ->exists();

        if (! $adaLosses) {
            $dokumenStok->losses()->create([
                'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                'tanggal' => $tanggal,
                'simbol' => $simbol,
                'jumlah' => $jumlah,
            ]);
        }
    }
}
