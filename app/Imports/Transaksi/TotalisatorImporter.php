<?php

namespace App\Imports\Transaksi;

/**
 * Import Totalisator (stand meter) historis.
 *
 * Tiap baris hanya menyisipkan 1 stand_meter berisi stand meter awal & akhir
 * sebagai data BELUM terverifikasi (is_confirmation = 0). Penjualan (dokumen
 * "Good Issue Sales") dan perubahan stok real dibentuk kemudian saat Penanggung
 * Jawab mengonfirmasi lewat alur normal (StandMeterController@confirmation) —
 * importer tidak menyentuh stok inventory.
 */
class TotalisatorImporter extends AbstractTransaksiImporter
{
    public static function headers(): array
    {
        return ['tanggal', 'jam_kerja', 'nozzle', 'dombak', 'stand_meter_awal', 'stand_meter_akhir'];
    }

    public static function example(): array
    {
        return ['2024-05-01', 'Jam Kerja 1', 'Nozzle 1', 'Dombak 1', '105230.5', '105480.5'];
    }

    protected function handleRow(array $row, string $memberId): void
    {
        $tanggal = $this->tanggal($row['tanggal']);
        $jamKerja = $this->master->jamKerja($this->text($row['jam_kerja'], 'jam_kerja'));
        $nozzle = $this->master->nozzle($this->text($row['nozzle'], 'nozzle'));
        $dombak = $this->master->dombak($this->text($row['dombak'], 'dombak'));
        $smAwal = $this->num($row['stand_meter_awal'], 'stand_meter_awal');
        $smAkhir = $this->num($row['stand_meter_akhir'], 'stand_meter_akhir');

        $sudahAda = $nozzle->stand_meter()
            ->where('dombak_id', $dombak->dombak_id)
            ->where('jam_kerja_id', $jamKerja->jam_kerja_id)
            ->where('tanggal', $tanggal)
            ->where('stand_meter_awal', $smAwal)
            ->where('stand_meter_akhir', $smAkhir)
            ->exists();

        if (! $sudahAda) {
            $nozzle->stand_meter()->create([
                'dombak_id' => $dombak->dombak_id,
                'jam_kerja_id' => $jamKerja->jam_kerja_id,
                'tanggal' => $tanggal,
                'stand_meter_awal' => $smAwal,
                'stand_meter_akhir' => $smAkhir,
                'urutan' => $jamKerja->urutan,
                'is_confirmation' => 0,
            ]);
        }
    }
}
