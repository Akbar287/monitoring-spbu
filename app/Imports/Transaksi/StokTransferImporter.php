<?php

namespace App\Imports\Transaksi;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Import Stok Transfer historis (perpindahan stok antar dombak satu produk).
 *
 * Tiap baris membuat 1 dombak_transfer, dokumen_stok "Good Transfer Send" untuk
 * dombak asal (stok turun) dan "Good Transfer Receive" untuk dombak tujuan
 * (stok naik), keduanya dihubungkan via pivot dombak_transfer_dokumen_stok,
 * serta losses "Good Transfer" pada dokumen receive.
 *
 * Catatan: dokumen rekonsiliasi "Stok Taking Good Transfer/Penyesuaian" yang
 * dibuat alur live (untuk menyamakan stok berjalan) sengaja tidak dibuat di sini
 * karena impor data lama memakai nilai eksplisit, bukan stok berjalan.
 */
class StokTransferImporter extends AbstractTransaksiImporter
{
    public static function headers(): array
    {
        return [
            'tanggal', 'jam_kerja', 'waktu',
            'dombak_asal', 'asal_awal', 'asal_akhir',
            'dombak_tujuan', 'tujuan_awal', 'tujuan_akhir',
        ];
    }

    public static function example(): array
    {
        return ['2024-05-01', 'Jam Kerja 1', '08:30:00', 'Dombak 1', '3152', '1152', 'Dombak 2', '2000', '4000'];
    }

    protected function handleRow(array $row, string $memberId): void
    {
        $tanggal = $this->tanggal($row['tanggal']);
        $jamKerja = $this->master->jamKerja($this->text($row['jam_kerja'], 'jam_kerja'));
        $waktu = $this->text($row['waktu'], 'waktu');
        $dombakAsal = $this->master->dombak($this->text($row['dombak_asal'], 'dombak_asal'));
        $asalAwal = $this->num($row['asal_awal'], 'asal_awal');
        $asalAkhir = $this->num($row['asal_akhir'], 'asal_akhir');
        $dombakTujuan = $this->master->dombak($this->text($row['dombak_tujuan'], 'dombak_tujuan'));
        $tujuanAwal = $this->num($row['tujuan_awal'], 'tujuan_awal');
        $tujuanAkhir = $this->num($row['tujuan_akhir'], 'tujuan_akhir');

        $invAsal = $this->master->stokInventory($dombakAsal);
        $invTujuan = $this->master->stokInventory($dombakTujuan);
        if ($invAsal->stok_inventory_id !== $invTujuan->stok_inventory_id) {
            throw new RuntimeException('Dombak asal dan tujuan harus produk yang sama.');
        }

        $produk = $invAsal->produk()->first();
        if (is_null($produk)) {
            throw new RuntimeException('Produk untuk transfer tidak ditemukan.');
        }

        $typeSend = $this->master->typeDokumen('Good Transfer Send');
        $typeReceive = $this->master->typeDokumen('Good Transfer Receive');

        $dt = $produk->dombak_transfer()
            ->where('jam_kerja_id', $jamKerja->jam_kerja_id)
            ->where('tanggal', $tanggal)
            ->where('is_confirmation', 0)
            ->first();
        if (is_null($dt)) {
            $dt = $produk->dombak_transfer()->create([
                'jam_kerja_id' => $jamKerja->jam_kerja_id,
                'tanggal' => $tanggal,
                'is_confirmation' => 0,
                'waktu' => $waktu,
            ]);
        }

        $send = $this->createDokumen($typeSend, $invAsal->stok_inventory_id, $memberId, $jamKerja->jam_kerja_id, $dombakAsal->dombak_id, $tanggal, $asalAwal, $asalAkhir, $waktu);
        $this->linkTransfer($dt->dombak_transfer_id, $send->dokumen_stok_id);
        $this->linkKonversi($dombakAsal, $send, $asalAkhir);

        $receive = $this->createDokumen($typeReceive, $invTujuan->stok_inventory_id, $memberId, $jamKerja->jam_kerja_id, $dombakTujuan->dombak_id, $tanggal, $tujuanAwal, $tujuanAkhir, $waktu);
        $this->linkTransfer($dt->dombak_transfer_id, $receive->dokumen_stok_id);
        $this->linkKonversi($dombakTujuan, $receive, $tujuanAkhir);

        // Loss = (tujuan naik) - (asal turun)
        $loss = ($tujuanAkhir - $tujuanAwal) - ($asalAwal - $asalAkhir);
        $jenisLoss = $this->master->jenisLosses('Good Transfer');
        $simbol = $loss < 0 ? 'kurang' : 'lebih';
        $jumlah = abs($loss);

        $adaLosses = $receive->losses()
            ->where('jenis_losses_id', $jenisLoss->jenis_losses_id)
            ->where('tanggal', $tanggal)
            ->where('simbol', $simbol)
            ->where('jumlah', $jumlah)
            ->exists();
        if (! $adaLosses) {
            $receive->losses()->create([
                'jenis_losses_id' => $jenisLoss->jenis_losses_id,
                'tanggal' => $tanggal,
                'simbol' => $simbol,
                'jumlah' => $jumlah,
            ]);
        }
    }

    private function createDokumen($type, $stokInventoryId, $memberId, $jamKerjaId, $dombakId, $tanggal, $awal, $akhir, $waktu)
    {
        $dokumen = $type->dokumen_stok()
            ->where('stok_inventory_id', $stokInventoryId)
            ->where('member_id', $memberId)
            ->where('jam_kerja_id', $jamKerjaId)
            ->where('dombak_id', $dombakId)
            ->where('tanggal', $tanggal)
            ->where('stok_awal', $awal)
            ->where('stok_akhir', $akhir)
            ->where('is_confirmation', 0)
            ->first();

        if (is_null($dokumen)) {
            $dokumen = $type->dokumen_stok()->create([
                'stok_inventory_id' => $stokInventoryId,
                'member_id' => $memberId,
                'jam_kerja_id' => $jamKerjaId,
                'dombak_id' => $dombakId,
                'tanggal' => $tanggal,
                'stok_awal' => $awal,
                'stok_akhir' => $akhir,
                'is_confirmation' => 0,
                'waktu' => $waktu,
            ]);
        }

        return $dokumen;
    }

    private function linkTransfer(string $dombakTransferId, string $dokumenStokId): void
    {
        $exists = DB::table('dombak_transfer_dokumen_stok')
            ->where('dombak_transfer_id', $dombakTransferId)
            ->where('dokumen_stok_id', $dokumenStokId)
            ->exists();
        if (! $exists) {
            DB::table('dombak_transfer_dokumen_stok')->insert([
                'dombak_transfer_id' => $dombakTransferId,
                'dokumen_stok_id' => $dokumenStokId,
            ]);
        }
    }
}
