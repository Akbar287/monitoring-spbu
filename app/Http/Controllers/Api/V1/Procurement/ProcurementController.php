<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Http\Controllers\Controller;
use App\Models\DetailRencanaPembelian;
use App\Models\PajakPembelianLib;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\RencanaPembelian;
use App\Models\StatusPurchase;
use App\Support\ApiResponse;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dasar bersama modul Procurement + endpoint ringkasan tahapan.
 *
 * Seluruh nilai uang & kuantitas diserialisasi sebagai STRING dengan 6 angka
 * di belakang koma lewat App\Support\Uang, dan setiap perhitungan memakai
 * bcmath — bukan float — agar presisi kolom decimal(16,6) di database
 * dipertahankan utuh dari server sampai layar.
 */
class ProcurementController extends Controller
{
    /** Urutan tahapan pada tabel status_purchase (StatusPurchaseSeeder). */
    public const TAHAP = ['Rencana', 'Pembelian', 'Pembayaran', 'MS2', 'Pengiriman', 'Penerimaan'];

    public function ringkasan(Request $request)
    {
        $spbu = \App\Support\MobileProfile::spbuUtama($request->user()->member()->first());

        $dasar = fn () => RencanaPembelian::query()
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id));

        $perTahap = [];
        foreach (self::TAHAP as $nama) {
            $status = self::status($nama);
            $perTahap[] = [
                'tahap' => $nama,
                'jumlah' => $status ? (clone $dasar())->where('status_purchase_id', $status->status_purchase_id)->count() : 0,
            ];
        }

        $lib = self::pajakLib();

        return ApiResponse::ok([
            'spbu' => $spbu ? $spbu->nama_spbu : 'Semua SPBU',
            'per_tahap' => $perTahap,
            'perencanaan_ditolak' => (clone $dasar())->where('is_denied', 1)->count(),
            'pembayaran_menunggu' => Pembayaran::where(fn ($q) => $q->where('is_confirmation_admin', 0)->orWhere('is_confirmation_top', 0))->count(),
            'delivery_berjalan' => Pengiriman::where('is_done', 0)->count(),
            'tarif_pajak' => $lib ? [
                'ppn' => Uang::of($lib->ppn),
                'ppbkb' => Uang::of($lib->ppbkb),
                'pph' => Uang::of($lib->pph),
            ] : null,
        ]);
    }

    // ------------------------------------------------------------- pembantu

    public static function status(string $nama): ?StatusPurchase
    {
        return StatusPurchase::where('nama_status', $nama)->first();
    }

    public static function pajakLib(): ?PajakPembelianLib
    {
        return PajakPembelianLib::where('is_aktif', 1)->first();
    }

    protected static function bool(mixed $nilai): bool
    {
        // kolom boolean di DB ini bertipe varchar(5) — isinya '0'/'1'
        return filter_var($nilai, FILTER_VALIDATE_BOOLEAN);
    }

    protected static function memberAktif(): ?string
    {
        return Auth::user()?->member()->first()?->member_id;
    }

    /**
     * Rit = pembulatan ke atas dari jumlah / kapasitas satu rit (8.000 liter),
     * mengikuti DetailRencanaPembelianController::checkRecommendedRit().
     */
    public static function ritDisarankan(string $jumlah): string
    {
        return Uang::of(ceil((float) Uang::bagi($jumlah, '8000')));
    }

    /** Harga beli default produk; 0 bila produk belum punya harga default. */
    public static function hargaBeliDefault(string $produkId): string
    {
        $produk = \App\Models\Produk::find($produkId);
        $harga = $produk?->harga()->where('is_default', 1)->first();

        return Uang::of($harga->harga_beli ?? 0);
    }

    /**
     * Hitung ulang grand_total dari seluruh baris detail, lalu segarkan baris
     * pajak pembelian dari tarif aktif. Dipanggil setiap kali detail berubah.
     *
     * Rumus (sama dengan web, tetapi dengan bcmath):
     *   total_harga = jumlah × harga
     *   grand_total = Σ total_harga
     *   net_price   = grand_total
     *   ppn         = grand_total × ppn%   ·  ppbkb = grand_total × ppbkb%
     *   pph         = grand_total × pph%
     *   total_amount= grand_total + ppn + ppbkb + pph
     */
    public static function hitungUlang(RencanaPembelian $rencana, bool $perbaruiPajak = true): string
    {
        $grandTotal = Uang::jumlah($rencana->detail_rencana_pembelian()->get(), 'total_harga');
        $rencana->update(['grand_total' => $grandTotal]);

        if ($perbaruiPajak) {
            self::segarkanPajak($rencana->fresh(), $grandTotal);
        }

        return $grandTotal;
    }

    public static function segarkanPajak(RencanaPembelian $rencana, ?string $grandTotal = null): void
    {
        $lib = self::pajakLib();
        if (is_null($lib)) {
            return; // tarif belum dikonfigurasi — jangan tulis pajak asal-asalan
        }

        $net = Uang::of($grandTotal ?? $rencana->grand_total);
        $ppn = Uang::persen($net, $lib->ppn);
        $ppbkb = Uang::persen($net, $lib->ppbkb);
        $pph = Uang::persen($net, $lib->pph);

        $atribut = [
            'pajak_pembelian_lib_id' => $lib->pajak_pembelian_lib_id,
            'net_price' => $net,
            'ppn' => $ppn,
            'ppbkb' => $ppbkb,
            'pph' => $pph,
            'total_amount' => Uang::tambah($net, $ppn, $ppbkb, $pph),
        ];

        $pajak = $rencana->pajak_pembelian()->first();
        if ($pajak) {
            // metode_posting_pajak sengaja tidak disentuh — itu pilihan user
            $pajak->update($atribut);
        } else {
            $rencana->pajak_pembelian()->create($atribut);
        }

        // sisa duplikat dari alur lama dibersihkan
        $rencana->pajak_pembelian()->orderBy('created_at', 'desc')->get()->skip(1)
            ->each(fn ($p) => $p->delete());
    }

    // --------------------------------------------------------- serialisasi

    public static function pajakArray(RencanaPembelian $rencana): array
    {
        $pajak = $rencana->pajak_pembelian()->first();
        $lib = self::pajakLib();

        if (is_null($pajak)) {
            // belum tersimpan: tampilkan proyeksi dari tarif aktif
            $net = Uang::of($rencana->grand_total);
            $ppn = $lib ? Uang::persen($net, $lib->ppn) : '0.000000';
            $ppbkb = $lib ? Uang::persen($net, $lib->ppbkb) : '0.000000';
            $pph = $lib ? Uang::persen($net, $lib->pph) : '0.000000';

            return [
                'tersimpan' => false,
                'net_price' => $net,
                'ppn' => $ppn,
                'ppbkb' => $ppbkb,
                'pph' => $pph,
                'total_amount' => Uang::tambah($net, $ppn, $ppbkb, $pph),
                'metode_posting_pajak' => 'hpp',
                'tarif' => self::tarifArray($lib),
            ];
        }

        return [
            'tersimpan' => true,
            'pajak_pembelian_id' => $pajak->pajak_pembelian_id,
            'net_price' => Uang::of($pajak->net_price),
            'ppn' => Uang::of($pajak->ppn),
            'ppbkb' => Uang::of($pajak->ppbkb),
            'pph' => Uang::of($pajak->pph),
            'total_amount' => Uang::of($pajak->total_amount),
            'metode_posting_pajak' => $pajak->metode_posting_pajak ?? 'hpp',
            'tarif' => self::tarifArray($lib),
        ];
    }

    private static function tarifArray(?PajakPembelianLib $lib): ?array
    {
        return $lib ? [
            'ppn' => Uang::of($lib->ppn),
            'ppbkb' => Uang::of($lib->ppbkb),
            'pph' => Uang::of($lib->pph),
        ] : null;
    }

    public static function detailArray(DetailRencanaPembelian $d): array
    {
        return [
            'id' => $d->detail_rencana_pembelian_id,
            'detail_rencana_pembelian_id' => $d->detail_rencana_pembelian_id,
            'produk_id' => $d->produk_id,
            'produk' => optional($d->produk()->first())->nama_produk,
            'jumlah' => Uang::of($d->jumlah),
            'satuan_jumlah' => $d->satuan_jumlah,
            'rit' => Uang::of($d->rit),
            'harga' => Uang::of($d->harga),
            'total_harga' => Uang::of($d->total_harga),
            'is_confirmation' => self::bool($d->is_confirmation),
            'ms2' => self::bool($d->ms2),
            'delivery' => self::bool($d->delivery),
        ];
    }

    public static function rencanaArray(RencanaPembelian $r, bool $lengkap = false): array
    {
        $status = optional($r->status_purchase()->first())->nama_status;
        $ditolak = self::bool($r->is_denied);

        $data = [
            'id' => $r->rencana_pembelian_id,
            'rencana_pembelian_id' => $r->rencana_pembelian_id,
            'judul' => $r->kode_pembelian ?? '(tanpa kode)',
            'subjudul' => trim(($status ?? '-') . ' · Rp ' . Uang::of($r->grand_total)),
            'kode_pembelian' => $r->kode_pembelian,
            'status' => $status,
            'spbu_id' => $r->spbu_id,
            'tanggal_pembelian' => $r->tanggal_pembelian,
            'grand_total' => Uang::of($r->grand_total),
            'is_confirmation' => self::bool($r->is_confirmation),
            'is_denied' => $ditolak,
            'created_at' => $r->created_at,
        ];

        if (! $lengkap) {
            return $data;
        }

        $detail = $r->detail_rencana_pembelian()->get();

        return array_merge($data, [
            'spbu' => optional($r->spbu()->first())->nama_spbu,
            'pemohon' => optional(optional($r->member()->first())->ktp()->first())->nama,
            'keterangan' => $r->keterangan,
            'keterangan_denied' => $r->keterangan_denied,
            'detail' => $detail->map(fn ($d) => self::detailArray($d))->values()->all(),
            'kalkulasi' => [
                'jumlah_baris' => $detail->count(),
                'total_kuantitas' => Uang::jumlah($detail, 'jumlah'),
                'total_rit' => Uang::jumlah($detail, 'rit'),
                'grand_total' => Uang::of($r->grand_total),
            ],
            'pajak' => self::pajakArray($r),
        ]);
    }
}
