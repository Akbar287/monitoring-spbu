<?php

namespace App\Support;

use App\Models\Coa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sumber tunggal agregasi buku besar untuk seluruh laporan keuangan mobile.
 *
 * Bentuk query-nya disalin persis dari controller web (NeracaSaldoController,
 * LabaRugiController, NeracaController, BukuBesarController) supaya angkanya
 * tidak mungkin berbeda:
 *
 *     SELECT coa_id, sum(debit - kredit)
 *     FROM jurnal_detail JOIN jurnal USING (jurnal_id)
 *     WHERE jurnal.is_posted = true [AND spbu_id = ?] [AND tanggal <op> ?]
 *     GROUP BY coa_id
 *
 * Perbedaan yang disengaja: nilai dipertahankan sebagai STRING desimal dari
 * Postgres (kolom `numeric`) dan dijumlahkan dengan bcmath lewat App\Support\Uang,
 * bukan lewat float PHP. Web memakai floatval() lalu number_format(…, 2);
 * hasil tampilannya sama, tetapi di sini tidak ada galat pembulatan biner
 * pada penjumlahan antar-akun.
 *
 * SEMUA baris laporan selalu membawa `kode_akun` — tidak ada angka yang tidak
 * bisa ditelusuri ke akun COA-nya.
 */
class LaporanKeuangan
{
    /** Query dasar: hanya jurnal yang benar-benar diposting. */
    private static function dasar(?string $spbuId)
    {
        return DB::table('jurnal_detail')
            ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
            ->where('jurnal.is_posted', true)
            ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId));
    }

    /**
     * Saldo (debit − kredit) per akun sampai / dalam rentang tanggal.
     * Nilai dikembalikan sebagai string desimal presisi penuh.
     *
     * @return Collection<string,string> coa_id => saldo
     */
    public static function saldo(?string $spbuId, ?string $dari, ?string $sampai, ?string $sampaiInklusif = null): Collection
    {
        $q = self::dasar($spbuId)
            ->when($dari && $sampai, fn ($x) => $x->whereBetween('jurnal.tanggal', [$dari, $sampai]))
            ->when($sampaiInklusif, fn ($x) => $x->where('jurnal.tanggal', '<=', $sampaiInklusif))
            ->when($dari && ! $sampai && ! $sampaiInklusif, fn ($x) => $x->where('jurnal.tanggal', '<', $dari))
            ->groupBy('jurnal_detail.coa_id')
            ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.debit - jurnal_detail.kredit) as saldo'));

        return collect($q->get())->mapWithKeys(fn ($r) => [$r->coa_id => Uang::of($r->saldo)]);
    }

    /** Saldo sebelum tanggal (untuk kolom "saldo awal"). */
    public static function saldoSebelum(?string $spbuId, string $dari): Collection
    {
        return self::saldo($spbuId, $dari, null, null);
    }

    /** Saldo kumulatif sampai dengan tanggal (untuk neraca). */
    public static function saldoSampai(?string $spbuId, string $per): Collection
    {
        return self::saldo($spbuId, null, null, $per);
    }

    /** Total debit & kredit per akun dalam rentang (untuk neraca saldo). */
    public static function mutasi(?string $spbuId, string $dari, string $sampai): Collection
    {
        $q = self::dasar($spbuId)
            ->whereBetween('jurnal.tanggal', [$dari, $sampai])
            ->groupBy('jurnal_detail.coa_id')
            ->select(
                'jurnal_detail.coa_id',
                DB::raw('sum(jurnal_detail.debit) as debit'),
                DB::raw('sum(jurnal_detail.kredit) as kredit')
            );

        return collect($q->get())->mapWithKeys(fn ($r) => [$r->coa_id => [
            'debit' => Uang::of($r->debit),
            'kredit' => Uang::of($r->kredit),
        ]]);
    }

    /** Rincian baris jurnal satu akun dalam rentang, urut seperti buku besar web. */
    public static function rincianAkun(?string $spbuId, string $coaId, string $dari, string $sampai): Collection
    {
        return collect(
            self::dasar($spbuId)
                ->where('jurnal_detail.coa_id', $coaId)
                ->whereBetween('jurnal.tanggal', [$dari, $sampai])
                ->orderBy('jurnal.tanggal')
                ->orderBy('jurnal.nomor_jurnal')
                ->orderBy('jurnal_detail.urutan')
                ->select(
                    'jurnal.tanggal',
                    'jurnal.nomor_jurnal',
                    'jurnal.tipe_sumber',
                    'jurnal.keterangan as ket_jurnal',
                    'jurnal_detail.keterangan as ket_detail',
                    'jurnal_detail.debit',
                    'jurnal_detail.kredit'
                )->get()
        );
    }

    /** Akun non-header, urut kode — dasar seluruh laporan. */
    public static function akun(?array $tipe = null, bool $hanyaAktif = false): Collection
    {
        return Coa::where('is_header', false)
            ->when($tipe, fn ($q) => $q->whereIn('tipe_akun', $tipe))
            ->when($hanyaAktif, fn ($q) => $q->where('is_aktif', true))
            ->orderBy('kode_akun')
            ->get();
    }

    /**
     * Ambang "dianggap nol" mengikuti web (0,005) supaya akun tanpa aktivitas
     * disembunyikan pada baris yang sama dengan tampilan web.
     */
    public static function nol(mixed $nilai): bool
    {
        return bccomp(Uang::bersih($nilai) < 0 ? Uang::kali($nilai, '-1') : Uang::of($nilai), '0.005', 6) < 0;
    }

    /** Baris laporan baku — selalu memuat kode akun. */
    public static function baris(Coa $a, string $nilai): array
    {
        return [
            'kode_akun' => $a->kode_akun,
            'nama_akun' => $a->nama_akun,
            'tipe_akun' => $a->tipe_akun,
            'saldo_normal' => $a->saldo_normal,
            'coa_id' => $a->coa_id,
            'nilai' => $nilai,
        ];
    }
}
