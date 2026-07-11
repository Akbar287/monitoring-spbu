<?php

namespace Database\Seeders;

use App\Models\Coa;
use App\Support\Akun;
use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    /**
     * Bagan akun standar SPBU. Aman dijalankan berulang (idempotent):
     * akun yang kode-nya sudah ada tidak akan ditimpa.
     */
    public function run(): void
    {
        $headers = [
            ['kode' => '1-0000', 'nama' => 'ASET', 'tipe' => 'aset', 'normal' => 'debit'],
            ['kode' => '2-0000', 'nama' => 'KEWAJIBAN', 'tipe' => 'kewajiban', 'normal' => 'kredit'],
            ['kode' => '3-0000', 'nama' => 'MODAL', 'tipe' => 'modal', 'normal' => 'kredit'],
            ['kode' => '4-0000', 'nama' => 'PENDAPATAN', 'tipe' => 'pendapatan', 'normal' => 'kredit'],
            ['kode' => '5-0000', 'nama' => 'HARGA POKOK PENJUALAN', 'tipe' => 'hpp', 'normal' => 'debit'],
            ['kode' => '6-0000', 'nama' => 'BEBAN OPERASIONAL', 'tipe' => 'beban', 'normal' => 'debit'],
            ['kode' => '7-0000', 'nama' => 'PENDAPATAN LAIN-LAIN', 'tipe' => 'pendapatan_lain', 'normal' => 'kredit'],
            ['kode' => '8-0000', 'nama' => 'BEBAN LAIN-LAIN', 'tipe' => 'beban_lain', 'normal' => 'debit'],
        ];

        $headerIds = [];
        foreach ($headers as $h) {
            $coa = Coa::firstOrCreate(['kode_akun' => $h['kode']], [
                'nama_akun' => $h['nama'],
                'tipe_akun' => $h['tipe'],
                'saldo_normal' => $h['normal'],
                'is_header' => true,
                'is_aktif' => true,
            ]);
            $headerIds[substr($h['kode'], 0, 1)] = $coa->coa_id;
        }

        // [kode, nama, tipe, saldo_normal, kategori_arus_kas, is_kas]
        $akun = [
            [Akun::KAS_OPERATOR, 'Kas Operator', 'aset', 'debit', 'operasi', true],
            [Akun::KAS_KECIL, 'Kas Kecil (Petty Cash)', 'aset', 'debit', 'operasi', true],
            [Akun::BANK, 'Bank', 'aset', 'debit', 'operasi', true],
            [Akun::PIUTANG_USAHA, 'Piutang Usaha', 'aset', 'debit', 'operasi', false],
            [Akun::PPN_MASUKAN, 'PPN Masukan', 'aset', 'debit', 'operasi', false],
            [Akun::PERSEDIAAN_BBM, 'Persediaan BBM', 'aset', 'debit', 'operasi', false],
            [Akun::PERSEDIAAN_LAINNYA, 'Persediaan Lainnya', 'aset', 'debit', 'operasi', false],
            [Akun::ASET_TETAP, 'Aset Tetap', 'aset', 'debit', 'investasi', false],
            [Akun::AKUMULASI_PENYUSUTAN, 'Akumulasi Penyusutan', 'aset', 'kredit', 'investasi', false],

            [Akun::UTANG_USAHA, 'Utang Usaha', 'kewajiban', 'kredit', 'operasi', false],
            [Akun::UTANG_PPH21, 'Utang PPh 21', 'kewajiban', 'kredit', 'operasi', false],
            [Akun::UTANG_PAJAK_LAINNYA, 'Utang Pajak Lainnya', 'kewajiban', 'kredit', 'operasi', false],

            [Akun::MODAL_DISETOR, 'Modal Disetor', 'modal', 'kredit', 'pendanaan', false],
            [Akun::PRIVE, 'Prive', 'modal', 'debit', 'pendanaan', false],
            [Akun::LABA_DITAHAN, 'Laba Ditahan', 'modal', 'kredit', 'pendanaan', false],
            [Akun::IKHTISAR_LABA_RUGI, 'Ikhtisar Laba Rugi', 'modal', 'kredit', null, false],

            [Akun::PENJUALAN_BBM, 'Penjualan BBM', 'pendapatan', 'kredit', 'operasi', false],
            [Akun::PENJUALAN_NON_BBM, 'Penjualan Non-BBM', 'pendapatan', 'kredit', 'operasi', false],

            [Akun::HPP_BBM, 'Harga Pokok Penjualan BBM', 'hpp', 'debit', 'operasi', false],
            [Akun::HPP_NON_BBM, 'Harga Pokok Penjualan Non-BBM', 'hpp', 'debit', 'operasi', false],
            [Akun::BEBAN_SUSUT_BBM, 'Beban Susut BBM', 'hpp', 'debit', 'operasi', false],

            [Akun::BEBAN_GAJI, 'Beban Gaji', 'beban', 'debit', 'operasi', false],
            [Akun::BEBAN_LISTRIK_AIR, 'Beban Listrik & Air', 'beban', 'debit', 'operasi', false],
            [Akun::BEBAN_PEMELIHARAAN, 'Beban Pemeliharaan', 'beban', 'debit', 'operasi', false],
            [Akun::BEBAN_PENYUSUTAN, 'Beban Penyusutan', 'beban', 'debit', null, false],
            [Akun::BEBAN_OPERASIONAL_LAIN, 'Beban Operasional Lainnya', 'beban', 'debit', 'operasi', false],
            [Akun::BEBAN_PPBKB, 'Beban PPBKB', 'beban', 'debit', 'operasi', false],
            [Akun::BEBAN_PPH_FINAL, 'Beban PPh Final', 'beban', 'debit', 'operasi', false],

            [Akun::PENDAPATAN_LAIN, 'Pendapatan Lain-lain', 'pendapatan_lain', 'kredit', 'operasi', false],
            [Akun::BEBAN_LAIN, 'Beban Lain-lain', 'beban_lain', 'debit', 'operasi', false],
        ];

        foreach ($akun as [$kode, $nama, $tipe, $normal, $arusKas, $isKas]) {
            Coa::firstOrCreate(['kode_akun' => $kode], [
                'parent_coa_id' => $headerIds[substr($kode, 0, 1)] ?? null,
                'nama_akun' => $nama,
                'tipe_akun' => $tipe,
                'saldo_normal' => $normal,
                'kategori_arus_kas' => $arusKas,
                'is_kas' => $isKas,
                'is_header' => false,
                'is_aktif' => true,
            ]);
        }
    }
}
