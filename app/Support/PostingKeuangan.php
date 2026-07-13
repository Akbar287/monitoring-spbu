<?php

namespace App\Support;

use App\Models\Coa;
use App\Models\PajakPembelian;
use App\Models\Pembayaran;
use App\Models\Penarikan;
use App\Models\Penjualan;
use App\Models\PettyCash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aturan bisnis posting jurnal otomatis (peta transaksi -> jurnal).
 * Dipanggil dari model event (created/updated/deleted) sehingga semua jalur
 * penulisan (controller, import, seeder) tertangkap. Mekanika pencatatan dan
 * validasinya ada di JurnalPosting. Peta lengkap: docs/keuangan.md.
 *
 * Modul GL baru aktif setelah tabel coa terisi (CoaSeeder); sebelum itu semua
 * posting dilewati agar operasional lama tidak terganggu. Transaksi pada masa
 * belum aktif dicatat belakangan lewat command backfill.
 */
class PostingKeuangan
{
    private static ?bool $aktif = null;

    public static function aktif(): bool
    {
        if (is_null(self::$aktif)) {
            self::$aktif = Schema::hasTable('coa') && Coa::where('is_header', false)->exists();
        }

        return self::$aktif;
    }

    // ---------------------------------------------------------------- penjualan

    /**
     * Dipanggil dari event created/updated Penjualan: posting saat setoran
     * diterima admin DAN top manajemen; batal saat salah satunya dicabut.
     */
    public static function penjualanBerubah(Penjualan $penjualan): void
    {
        if (! self::aktif()) {
            return;
        }

        $sebelum = filter_var($penjualan->getOriginal('is_accepted_admin'), FILTER_VALIDATE_BOOLEAN)
            && filter_var($penjualan->getOriginal('is_accepted_top'), FILTER_VALIDATE_BOOLEAN);
        $sesudah = filter_var($penjualan->is_accepted_admin, FILTER_VALIDATE_BOOLEAN)
            && filter_var($penjualan->is_accepted_top, FILTER_VALIDATE_BOOLEAN);

        if ($penjualan->wasRecentlyCreated) {
            if ($sesudah) {
                self::postPenjualan($penjualan);
            }
            return;
        }

        if (! $sebelum && $sesudah) {
            self::postPenjualan($penjualan);
        } elseif ($sebelum && ! $sesudah) {
            self::batalPenjualan($penjualan);
        }
    }

    public static function batalPenjualan(Penjualan $penjualan): void
    {
        if (! self::aktif()) {
            return;
        }
        DB::transaction(function () use ($penjualan) {
            JurnalPosting::batalkan('penjualan', $penjualan->penjualan_id);
            JurnalPosting::batalkan('penjualan_hpp', $penjualan->penjualan_id);
        });
    }

    /**
     * Penjualan menghasilkan dua jurnal atomik: bagian kas (tipe `penjualan`)
     * dan bagian HPP/persediaan (tipe `penjualan_hpp`). Dipisah agar laporan
     * arus kas metode langsung bersih — jurnal non-kas tidak ikut terseret.
     */
    public static function postPenjualan(Penjualan $penjualan): void
    {
        $pendapatan = floatval($penjualan->total_penjualan);
        $kas = floatval($penjualan->total_uang_kotak);
        if ($pendapatan <= 0.0001 && $kas <= 0.0001) {
            return;
        }

        $lines = [
            ['kode_akun' => Akun::KAS_OPERATOR, 'debit' => $kas, 'kredit' => 0, 'keterangan' => 'Uang kotak setoran shift'],
            ['kode_akun' => Akun::PENJUALAN_BBM, 'debit' => 0, 'kredit' => $pendapatan, 'keterangan' => 'Penjualan BBM'],
        ];

        // selisih uang kotak vs penjualan tercatat (kurang/lebih setor)
        $selisih = $pendapatan - $kas;
        if ($selisih > 0.0001) {
            $lines[] = ['kode_akun' => Akun::BEBAN_LAIN, 'debit' => $selisih, 'kredit' => 0, 'keterangan' => 'Selisih kurang setor kas'];
        } elseif ($selisih < -0.0001) {
            $lines[] = ['kode_akun' => Akun::PENDAPATAN_LAIN, 'debit' => 0, 'kredit' => abs($selisih), 'keterangan' => 'Selisih lebih setor kas'];
        }

        // HPP perpetual: liter terjual x harga beli yang berlaku pada harga_id detail
        $hpp = $penjualan->detail_penjualan()->with('harga')->get()
            ->sum(fn ($d) => floatval($d->liter) * floatval($d->harga->harga_beli ?? 0));

        DB::transaction(function () use ($penjualan, $lines, $hpp) {
            $tanggalLabel = Carbon::parse($penjualan->tanggal)->format('d/m/Y');
            JurnalPosting::post(
                $penjualan->spbu_id,
                $penjualan->tanggal,
                'penjualan',
                $penjualan->penjualan_id,
                'Setoran penjualan ' . $tanggalLabel,
                $lines,
                self::memberAktif()
            );
            if ($hpp > 0.0001) {
                JurnalPosting::post(
                    $penjualan->spbu_id,
                    $penjualan->tanggal,
                    'penjualan_hpp',
                    $penjualan->penjualan_id,
                    'HPP penjualan ' . $tanggalLabel,
                    [
                        ['kode_akun' => Akun::HPP_BBM, 'debit' => $hpp, 'kredit' => 0, 'keterangan' => 'HPP BBM terjual (perpetual)'],
                        ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => 0, 'kredit' => $hpp, 'keterangan' => 'Pengurangan persediaan BBM terjual'],
                    ],
                    self::memberAktif()
                );
            }
        });
    }

    // ---------------------------------------------------------------- penarikan

    public static function penarikanBerubah(Penarikan $penarikan): void
    {
        if (! self::aktif()) {
            return;
        }

        $sebelum = filter_var($penarikan->getOriginal('is_accepted'), FILTER_VALIDATE_BOOLEAN);
        $sesudah = filter_var($penarikan->is_accepted, FILTER_VALIDATE_BOOLEAN);

        if ($penarikan->wasRecentlyCreated) {
            if ($sesudah) {
                self::postPenarikan($penarikan);
            }
            return;
        }

        if (! $sebelum && $sesudah) {
            self::postPenarikan($penarikan);
        } elseif ($sebelum && ! $sesudah) {
            JurnalPosting::batalkan('penarikan', $penarikan->penarikan_id);
        }
    }

    public static function batalPenarikan(Penarikan $penarikan): void
    {
        if (! self::aktif()) {
            return;
        }
        JurnalPosting::batalkan('penarikan', $penarikan->penarikan_id);
    }

    public static function postPenarikan(Penarikan $penarikan): void
    {
        $total = floatval($penarikan->total_uang);
        if ($total <= 0.0001) {
            return;
        }
        JurnalPosting::post(
            $penarikan->penjualan()->first()->spbu_id,
            $penarikan->tanggal,
            'penarikan',
            $penarikan->penarikan_id,
            'Setoran kas ke bank ' . $penarikan->nama_bank . ' ref ' . $penarikan->no_referensi,
            [
                ['kode_akun' => Akun::BANK, 'debit' => $total, 'kredit' => 0, 'keterangan' => 'Setoran ke ' . $penarikan->nama_bank],
                ['kode_akun' => Akun::KAS_OPERATOR, 'debit' => 0, 'kredit' => $total, 'keterangan' => 'Kas disetor ke bank'],
            ],
            self::memberAktif()
        );
    }

    // ------------------------------------------------------- pembelian (faktur)

    /**
     * Pengakuan persediaan + utang saat faktur pajak pembelian tercatat.
     * Komposisi debit mengikuti pilihan user per pembelian
     * (metode_posting_pajak hpp|terpisah) via JurnalPosting::barisPembelian.
     */
    public static function fakturPembelianBerubah(PajakPembelian $pajak): void
    {
        if (! self::aktif()) {
            return;
        }

        if (! $pajak->wasRecentlyCreated) {
            // nilai pajak/metode berubah -> jurnal lama dibatalkan lalu diposting ulang
            JurnalPosting::batalkan('pembelian', $pajak->pajak_pembelian_id);
        }
        self::postFakturPembelian($pajak);
    }

    public static function batalFakturPembelian(PajakPembelian $pajak): void
    {
        if (! self::aktif()) {
            return;
        }
        JurnalPosting::batalkan('pembelian', $pajak->pajak_pembelian_id);
    }

    public static function postFakturPembelian(PajakPembelian $pajak): void
    {
        $rencana = $pajak->rencana_pembelian()->first();
        if (is_null($rencana)) {
            return;
        }

        $lines = JurnalPosting::barisPembelian($pajak);
        $totalDebit = array_sum(array_column($lines, 'debit'));
        if ($totalDebit <= 0.0001) {
            return;
        }
        $lines[] = ['kode_akun' => Akun::UTANG_USAHA, 'debit' => 0, 'kredit' => $totalDebit, 'keterangan' => 'Utang pembelian ' . ($rencana->kode_pembelian ?? '')];

        JurnalPosting::post(
            $rencana->spbu_id,
            $rencana->tanggal_pembelian ?? Carbon::parse($pajak->created_at ?? now())->toDateString(),
            'pembelian',
            $pajak->pajak_pembelian_id,
            'Faktur pembelian BBM ' . ($rencana->kode_pembelian ?? $rencana->rencana_pembelian_id),
            $lines,
            self::memberAktif()
        );
    }

    // --------------------------------------------------------------- pembayaran

    public static function pembayaranBerubah(Pembayaran $pembayaran): void
    {
        if (! self::aktif()) {
            return;
        }

        $sebelum = filter_var($pembayaran->getOriginal('is_confirmation_admin'), FILTER_VALIDATE_BOOLEAN)
            && filter_var($pembayaran->getOriginal('is_confirmation_top'), FILTER_VALIDATE_BOOLEAN);
        $sesudah = filter_var($pembayaran->is_confirmation_admin, FILTER_VALIDATE_BOOLEAN)
            && filter_var($pembayaran->is_confirmation_top, FILTER_VALIDATE_BOOLEAN);

        if ($pembayaran->wasRecentlyCreated) {
            if ($sesudah) {
                self::postPembayaran($pembayaran);
            }
            return;
        }

        if (! $sebelum && $sesudah) {
            self::postPembayaran($pembayaran);
        } elseif ($sebelum && ! $sesudah) {
            JurnalPosting::batalkan('pembayaran', $pembayaran->pembayaran_id);
        }
    }

    public static function batalPembayaran(Pembayaran $pembayaran): void
    {
        if (! self::aktif()) {
            return;
        }
        JurnalPosting::batalkan('pembayaran', $pembayaran->pembayaran_id);
    }

    public static function postPembayaran(Pembayaran $pembayaran): void
    {
        $rencana = $pembayaran->rencana_pembelian()->first();
        $total = floatval($pembayaran->total_setoran);
        if (is_null($rencana) || $total <= 0.0001) {
            return;
        }
        JurnalPosting::post(
            $rencana->spbu_id,
            $pembayaran->tanggal,
            'pembayaran',
            $pembayaran->pembayaran_id,
            'Pembayaran pembelian ' . ($rencana->kode_pembelian ?? '') . ' cek/BG ' . $pembayaran->no_cek_bg,
            [
                ['kode_akun' => Akun::UTANG_USAHA, 'debit' => $total, 'kredit' => 0, 'keterangan' => 'Pelunasan utang pembelian'],
                ['kode_akun' => Akun::BANK, 'debit' => 0, 'kredit' => $total, 'keterangan' => 'Pembayaran via bank'],
            ],
            self::memberAktif()
        );
    }

    // --------------------------------------------------------------- petty cash

    public static function pettyCashBerubah(PettyCash $pettyCash): void
    {
        if (! self::aktif()) {
            return;
        }

        if (! $pettyCash->wasRecentlyCreated) {
            JurnalPosting::batalkan('petty_cash', $pettyCash->petty_cash_id);
        }
        self::postPettyCash($pettyCash);
    }

    public static function batalPettyCash(PettyCash $pettyCash): void
    {
        if (! self::aktif()) {
            return;
        }
        JurnalPosting::batalkan('petty_cash', $pettyCash->petty_cash_id);
    }

    public static function postPettyCash(PettyCash $pettyCash): void
    {
        $jumlah = floatval($pettyCash->jumlah);
        if ($jumlah <= 0.0001) {
            return;
        }
        $ket = trim('Petty cash ' . $pettyCash->no_kode . '/' . $pettyCash->no_bukti . ' - ' . $pettyCash->keterangan);

        if ($pettyCash->jenis === 'debit') {
            // pengisian kembali kas kecil dari bank
            $lines = [
                ['kode_akun' => Akun::KAS_KECIL, 'debit' => $jumlah, 'kredit' => 0, 'keterangan' => $ket],
                ['kode_akun' => Akun::BANK, 'debit' => 0, 'kredit' => $jumlah, 'keterangan' => $ket],
            ];
        } else {
            // pengeluaran kas kecil; pemetaan no_kode -> akun beban spesifik menyusul,
            // sementara semua ke Beban Operasional Lainnya
            $lines = [
                ['kode_akun' => Akun::BEBAN_OPERASIONAL_LAIN, 'debit' => $jumlah, 'kredit' => 0, 'keterangan' => $ket],
                ['kode_akun' => Akun::KAS_KECIL, 'debit' => 0, 'kredit' => $jumlah, 'keterangan' => $ket],
            ];
        }

        JurnalPosting::post(
            $pettyCash->spbu_id,
            $pettyCash->tanggal,
            'petty_cash',
            $pettyCash->petty_cash_id,
            $ket,
            $lines,
            self::memberAktif()
        );
    }

    // ------------------------------------------------------------------- losses

    /**
     * Susut/lebih stok dari stok taking, transfer, atau penerimaan.
     * Nilai = liter x harga beli default produk. Jika produk belum punya harga
     * default, posting dilewati (nilai 0) agar alur stok tidak terblokir.
     */
    public static function lossesBerubah(\App\Models\Losses $losses): void
    {
        if (! self::aktif()) {
            return;
        }
        if (! $losses->wasRecentlyCreated) {
            JurnalPosting::batalkan('losses', $losses->losses_id);
        }
        self::postLosses($losses);
    }

    public static function batalLosses(\App\Models\Losses $losses): void
    {
        if (! self::aktif()) {
            return;
        }
        JurnalPosting::batalkan('losses', $losses->losses_id);
    }

    public static function postLosses(\App\Models\Losses $losses): void
    {
        $dokumen = $losses->dokumen_stok()->with(['dombak', 'stok_inventory'])->first();
        $spbuId = $dokumen?->dombak?->spbu_id;
        $produkId = $dokumen?->stok_inventory?->produk_id;
        if (is_null($spbuId) || is_null($produkId)) {
            return;
        }

        $hargaBeli = floatval(\App\Models\Harga::where('produk_id', $produkId)->where('is_default', 1)->value('harga_beli') ?? 0);
        $nilai = floatval($losses->jumlah) * $hargaBeli;
        if ($nilai <= 0.0001) {
            return;
        }

        $ket = 'Susut stok (' . $losses->simbol . ') ' . floatval($losses->jumlah) . ' liter';
        $lines = $losses->simbol === 'kurang'
            ? [
                ['kode_akun' => Akun::BEBAN_SUSUT_BBM, 'debit' => $nilai, 'kredit' => 0, 'keterangan' => $ket],
                ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => 0, 'kredit' => $nilai, 'keterangan' => $ket],
            ]
            : [
                ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => $nilai, 'kredit' => 0, 'keterangan' => $ket],
                ['kode_akun' => Akun::BEBAN_SUSUT_BBM, 'debit' => 0, 'kredit' => $nilai, 'keterangan' => $ket],
            ];

        JurnalPosting::post($spbuId, $losses->tanggal, 'losses', $losses->losses_id, $ket, $lines, self::memberAktif());
    }

    // --------------------------------------------------------------------- gaji

    /**
     * Pembayaran gaji per member. Beban gaji = gaji bersih + potongan pajak;
     * potongan diakui sebagai Utang PPh 21 sampai disetor ke kas negara.
     * SPBU diambil dari penempatan member (spbu_member pertama).
     */
    public static function gajiMemberBerubah(\App\Models\GajiMember $gajiMember): void
    {
        if (! self::aktif()) {
            return;
        }
        if (! $gajiMember->wasRecentlyCreated) {
            JurnalPosting::batalkan('gaji', $gajiMember->gaji_member_id);
        }
        self::postGajiMember($gajiMember);
    }

    public static function batalGajiMember(\App\Models\GajiMember $gajiMember): void
    {
        if (! self::aktif()) {
            return;
        }
        JurnalPosting::batalkan('gaji', $gajiMember->gaji_member_id);
    }

    public static function postGajiMember(\App\Models\GajiMember $gajiMember): void
    {
        $bersih = floatval($gajiMember->total_gaji_bersih);
        if ($bersih <= 0.0001) {
            return;
        }
        $potongan = floatval($gajiMember->gaji()->first()?->potongan_pajak ?? 0);
        $member = $gajiMember->member()->first();
        $spbuId = $member?->spbu()->first()?->spbu_id ?? \App\Models\Spbu::first()->spbu_id;

        $ket = 'Gaji ' . (optional($member?->ktp()->first())->nama ?? $gajiMember->member_id) . ' per ' . Carbon::parse($gajiMember->tanggal)->format('d/m/Y');
        $lines = [
            ['kode_akun' => Akun::BEBAN_GAJI, 'debit' => $bersih + $potongan, 'kredit' => 0, 'keterangan' => $ket],
            ['kode_akun' => Akun::BANK, 'debit' => 0, 'kredit' => $bersih, 'keterangan' => 'Pembayaran gaji bersih'],
        ];
        if ($potongan > 0.0001) {
            $lines[] = ['kode_akun' => Akun::UTANG_PPH21, 'debit' => 0, 'kredit' => $potongan, 'keterangan' => 'Potongan PPh 21 gaji'];
        }

        JurnalPosting::post($spbuId, $gajiMember->tanggal, 'gaji', $gajiMember->gaji_member_id, $ket, $lines, self::memberAktif());
    }

    // ------------------------------------------------------------------ helpers

    private static function memberAktif(): ?string
    {
        try {
            return Auth::user()?->member()->first()?->member_id;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
