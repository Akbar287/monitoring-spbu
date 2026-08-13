<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Models\RencanaPembelian;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Tahap 2 — Pembelian (Purchase Order).
 * Di sini harga tebus per produk dikunci, pajak dihitung, lalu dokumen
 * dilanjutkan ke tahap Pembayaran.
 */
class PembelianController extends ProcurementController
{
    public function index(Request $request)
    {
        $status = self::status('Pembelian');
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());

        $paginator = RencanaPembelian::query()
            ->when($status, fn ($q) => $q->where('status_purchase_id', $status->status_purchase_id))
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->when($request->filled('cari'), fn ($q) => $q->whereRaw('LOWER(kode_pembelian) LIKE ?', ['%' . mb_strtolower($request->cari) . '%']))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (RencanaPembelian $r) => self::rencanaArray($r));
    }

    public function show(string $id)
    {
        $rencana = RencanaPembelian::find($id);

        return $rencana
            ? ApiResponse::ok(self::rencanaArray($rencana, lengkap: true))
            : ApiResponse::fail('Pembelian tidak ditemukan.', 404);
    }

    /**
     * Ubah harga tebus & rit satu baris. total_harga, grand_total, dan seluruh
     * komponen pajak dihitung ulang pada presisi 6 desimal.
     */
    public function ubahHarga(Request $request, string $id, string $detailId)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Pembelian tidak ditemukan.', 404);
        }

        $detail = $rencana->detail_rencana_pembelian()->where('detail_rencana_pembelian_id', $detailId)->first();
        if (! $detail) {
            return ApiResponse::fail('Baris produk tidak ditemukan pada pembelian ini.', 404);
        }

        $request->validate([
            'harga' => ['required', 'numeric', 'min:0'],
            'rit' => ['nullable', 'numeric', 'min:0'],
            'jumlah' => ['nullable', 'numeric', 'gt:0'],
        ]);

        DB::transaction(function () use ($request, $rencana, $detail) {
            $jumlah = $request->filled('jumlah') ? Uang::of($request->input('jumlah')) : Uang::of($detail->jumlah);
            $harga = Uang::of($request->input('harga'));

            $detail->update([
                'jumlah' => $jumlah,
                'harga' => $harga,
                'rit' => $request->filled('rit') ? Uang::of($request->input('rit')) : self::ritDisarankan($jumlah),
                'total_harga' => Uang::kali($jumlah, $harga),
            ]);

            self::hitungUlang($rencana);
        });

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Harga produk diperbarui.');
    }

    /** Kembalikan komponen pajak ke hasil hitungan tarif aktif. */
    public function hitungUlangPajak(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Pembelian tidak ditemukan.', 404);
        }
        if (is_null(self::pajakLib())) {
            return ApiResponse::fail('Belum ada tarif pajak aktif. Atur di Konfigurasi → Tarif Pajak.', 422);
        }

        self::hitungUlang($rencana);

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Pajak dihitung ulang dari tarif aktif.');
    }

    /**
     * Penyesuaian pajak manual — dipakai bila faktur Pertamina berbeda dari
     * hasil hitungan tarif. Nilai diterima apa adanya (6 desimal) tanpa
     * dipaksa cocok dengan tarif.
     */
    public function ubahPajak(Request $request, string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Pembelian tidak ditemukan.', 404);
        }

        $request->validate([
            'net_price' => ['required', 'numeric', 'min:0'],
            'ppn' => ['required', 'numeric', 'min:0'],
            'ppbkb' => ['required', 'numeric', 'min:0'],
            'pph' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'metode_posting_pajak' => ['required', Rule::in(['hpp', 'terpisah'])],
        ]);

        $lib = self::pajakLib();
        if (is_null($lib)) {
            return ApiResponse::fail('Belum ada tarif pajak aktif. Atur di Konfigurasi → Tarif Pajak.', 422);
        }

        $net = Uang::of($request->input('net_price'));
        $ppn = Uang::of($request->input('ppn'));
        $ppbkb = Uang::of($request->input('ppbkb'));
        $pph = Uang::of($request->input('pph'));

        // total dihitung server bila klien tidak mengirimnya
        $total = $request->filled('total_amount')
            ? Uang::of($request->input('total_amount'))
            : Uang::tambah($net, $ppn, $ppbkb, $pph);

        $atribut = [
            'pajak_pembelian_lib_id' => $lib->pajak_pembelian_lib_id,
            'net_price' => $net,
            'ppn' => $ppn,
            'ppbkb' => $ppbkb,
            'pph' => $pph,
            'total_amount' => $total,
            'metode_posting_pajak' => $request->input('metode_posting_pajak'),
        ];

        $pajak = $rencana->pajak_pembelian()->first();
        $pajak ? $pajak->update($atribut) : $rencana->pajak_pembelian()->create($atribut);

        $selisih = Uang::kurang($total, Uang::tambah($net, $ppn, $ppbkb, $pph));

        return ApiResponse::ok(
            array_merge(self::rencanaArray($rencana->fresh(), lengkap: true), ['selisih_total' => $selisih]),
            Uang::nol($selisih)
                ? 'Pajak pembelian diperbarui.'
                : 'Pajak diperbarui, tetapi total berbeda ' . $selisih . ' dari penjumlahan komponennya.'
        );
    }

    /** Lanjut ke tahap Pembayaran; tanggal pembelian dikunci hari ini. */
    public function lanjut(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Pembelian tidak ditemukan.', 404);
        }

        if (Uang::nol($rencana->grand_total)) {
            return ApiResponse::fail('Grand total masih nol — isi harga produk lebih dulu.', 422);
        }

        $status = self::status('Pembayaran');
        if (is_null($status)) {
            return ApiResponse::fail('Status "Pembayaran" belum ada. Jalankan StatusPurchaseSeeder.', 422);
        }

        DB::transaction(function () use ($rencana, $status) {
            // pastikan baris pajak ada sebelum masuk tahap pembayaran
            if (is_null($rencana->pajak_pembelian()->first())) {
                self::segarkanPajak($rencana);
            }

            $rencana->update([
                'tanggal_pembelian' => date('Y-m-d'),
                'status_purchase_id' => $status->status_purchase_id,
            ]);
        });

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Pembelian dilanjutkan ke tahap Pembayaran.');
    }

    /** Kembalikan dokumen ke tahap Rencana untuk direvisi. */
    public function kembalikan(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Pembelian tidak ditemukan.', 404);
        }

        $status = self::status('Rencana');
        $rencana->update([
            'status_purchase_id' => $status?->status_purchase_id ?? $rencana->status_purchase_id,
            'is_confirmation' => 0,
            'is_denied' => 0,
        ]);

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Dokumen dikembalikan ke tahap Perencanaan.');
    }
}
