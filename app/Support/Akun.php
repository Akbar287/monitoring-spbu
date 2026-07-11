<?php

namespace App\Support;

/**
 * Kode akun baku yang dirujuk oleh posting otomatis (JurnalPosting).
 * Kode di sini harus selalu ada di tabel coa (di-seed lewat CoaSeeder)
 * dan tidak boleh diubah kodenya dari menu master COA.
 */
class Akun
{
    // Aset
    public const KAS_OPERATOR = '1-1000';
    public const KAS_KECIL = '1-1100';
    public const BANK = '1-1200';
    public const PIUTANG_USAHA = '1-1300';
    public const PPN_MASUKAN = '1-1400';
    public const PERSEDIAAN_BBM = '1-1500';
    public const PERSEDIAAN_LAINNYA = '1-1600';
    public const ASET_TETAP = '1-2000';
    public const AKUMULASI_PENYUSUTAN = '1-2100';

    // Kewajiban
    public const UTANG_USAHA = '2-1000';
    public const UTANG_PPH21 = '2-1100';
    public const UTANG_PAJAK_LAINNYA = '2-1200';

    // Modal
    public const MODAL_DISETOR = '3-1000';
    public const PRIVE = '3-1100';
    public const LABA_DITAHAN = '3-2000';
    public const IKHTISAR_LABA_RUGI = '3-2100';

    // Pendapatan
    public const PENJUALAN_BBM = '4-1000';
    public const PENJUALAN_NON_BBM = '4-2000';

    // HPP
    public const HPP_BBM = '5-1000';
    public const HPP_NON_BBM = '5-2000';
    public const BEBAN_SUSUT_BBM = '5-3000';

    // Beban operasional
    public const BEBAN_GAJI = '6-1000';
    public const BEBAN_LISTRIK_AIR = '6-1100';
    public const BEBAN_PEMELIHARAAN = '6-1200';
    public const BEBAN_PENYUSUTAN = '6-1300';
    public const BEBAN_OPERASIONAL_LAIN = '6-1400';
    public const BEBAN_PPBKB = '6-2000';
    public const BEBAN_PPH_FINAL = '6-3000';

    // Lain-lain
    public const PENDAPATAN_LAIN = '7-1000';
    public const BEBAN_LAIN = '8-1000';
}
