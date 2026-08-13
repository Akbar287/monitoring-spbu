<?php

namespace App\Http\Controllers\Api\V1\Persediaan;

use App\Http\Controllers\Controller;
use App\Models\Dombak;
use App\Models\DokumenStok;
use App\Support\ApiResponse;
use App\Support\Persediaan;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Stok Taking: pengukuran fisik isi dombak.
 *
 * Tinggi cairan diubah menjadi volume lewat tabel konversi milik dombak,
 * selisihnya terhadap saldo tercatat dibukukan sebagai losses/profit, dan
 * saldo baru berlaku setelah dokumen dikonfirmasi.
 */
class StokTakingController extends Controller
{
    public function index(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: false);
    }

    public function riwayat(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: true);
    }

    public function show(string $id)
    {
        $d = $this->temukan($id);
        if (is_null($d)) {
            return ApiResponse::fail('Dokumen stok taking tidak ditemukan.', 404);
        }

        return ApiResponse::ok(Persediaan::serialDokumen($d, lengkap: true));
    }

    /**
     * Catat hasil pengukuran. Idempoten: pengiriman ulang dengan nilai sama
     * tidak menghasilkan dokumen kedua.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dombak_id' => ['required', 'exists:dombak,dombak_id'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tinggi' => ['required', 'numeric', 'min:0'],
        ]);

        $dombak = Dombak::find($request->input('dombak_id'));
        $inventory = Persediaan::inventoryDariDombak($dombak);

        if (is_null($inventory)) {
            return ApiResponse::fail(
                "Dombak {$dombak->nama_dombak} belum terhubung ke produk mana pun. "
                . 'Hubungkan lewat Monitoring Stok.',
                422
            );
        }

        $memberId = $request->user()->member()->first()->member_id;

        try {
            $dokumen = DB::transaction(function () use ($request, $dombak, $inventory, $memberId) {
                $konversi = Persediaan::konversi($dombak, $request->input('tinggi'));
                if (is_null($konversi)) {
                    throw new RuntimeException(
                        "Tinggi {$request->input('tinggi')} tidak ada di tabel konversi dombak {$dombak->nama_dombak}."
                    );
                }

                $awal = Persediaan::stokDombak($dombak);
                $akhir = Uang::of($konversi->volume);

                $dokumen = Persediaan::dokumen(
                    Persediaan::tipe(Persediaan::GERAK_STOK_TAKING),
                    $inventory,
                    $memberId,
                    $dombak->dombak_id,
                    $request->input('jam_kerja_id'),
                    $request->input('tanggal'),
                    $awal,
                    $akhir
                );

                Persediaan::kaitkanKonversi($dokumen, $konversi);
                // selisih positif = profit ("lebih"), negatif = losses ("kurang")
                Persediaan::catatSelisih(
                    $dokumen,
                    Persediaan::GERAK_STOK_TAKING,
                    $request->input('tanggal'),
                    Uang::kurang($akhir, $awal)
                );

                return $dokumen;
            });
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(
            Persediaan::serialDokumen($dokumen->fresh(), lengkap: true),
            'Stok taking dicatat. Saldo berubah setelah dikonfirmasi.',
            201
        );
    }

    public function konfirmasi(string $id)
    {
        $d = $this->temukan($id);
        if (is_null($d)) {
            return ApiResponse::fail('Dokumen stok taking tidak ditemukan.', 404);
        }

        if (filter_var($d->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::ok(Persediaan::serialDokumen($d, lengkap: true), 'Dokumen ini sudah dikonfirmasi.');
        }

        try {
            DB::transaction(fn () => Persediaan::terapkan($d));
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(
            Persediaan::serialDokumen($d->fresh(), lengkap: true),
            'Stok taking dikonfirmasi dan saldo diperbarui.'
        );
    }

    public function destroy(string $id)
    {
        $d = $this->temukan($id);
        if (is_null($d)) {
            return ApiResponse::fail('Dokumen stok taking tidak ditemukan.', 404);
        }

        if (filter_var($d->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail(
                'Dokumen yang sudah dikonfirmasi tidak bisa dihapus karena saldo sudah terlanjur berubah.',
                422
            );
        }

        Persediaan::hapusDokumen($d); // losses dibatalkan lewat model agar jurnalnya ikut batal

        return ApiResponse::ok([], 'Stok taking dihapus.');
    }

    // --------------------------------------------------------------- bantuan

    private function temukan(string $id): ?DokumenStok
    {
        $tipe = Persediaan::tipe(Persediaan::GERAK_STOK_TAKING);

        return DokumenStok::where('dokumen_stok_id', $id)
            ->where('type_dokumen_stok_id', $tipe->type_dokumen_stok_id)
            ->first();
    }

    private function daftar(Request $request, bool $terkonfirmasi)
    {
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'dombak_id' => ['nullable', 'exists:dombak,dombak_id'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tipe = Persediaan::tipe(Persediaan::GERAK_STOK_TAKING);

        $paginator = DokumenStok::where('type_dokumen_stok_id', $tipe->type_dokumen_stok_id)
            ->where('is_confirmation', $terkonfirmasi ? 1 : 0)
            ->when($request->filled('dari'), fn ($q) => $q->where('tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('dombak_id'), fn ($q) => $q->where('dombak_id', $request->input('dombak_id')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereRaw('LOWER(CAST(tanggal AS TEXT)) LIKE ?', [$pola])
                    ->orWhereHas('dombak', fn ($d) => $d->whereRaw('LOWER(nama_dombak) LIKE ?', [$pola]))
                    ->orWhereHas('stok_inventory.produk', fn ($p) => $p->whereRaw('LOWER(nama_produk) LIKE ?', [$pola])));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, function (DokumenStok $d) {
            $data = Persediaan::serialDokumen($d);
            $data['judul'] = ($data['dombak'] ?? '-') . ' · ' . ($data['produk'] ?? '-');
            $data['subjudul'] = $data['tanggal'] . ' · ' . $data['stok_akhir'] . ' L';

            return $data;
        });
    }
}
