<?php

namespace App\Http\Controllers\Api\V1\Persediaan;

use App\Http\Controllers\Controller;
use App\Models\Dombak;
use App\Models\Produk;
use App\Models\StokInventory;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Persediaan;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Monitoring Stok: saldo persediaan per produk beserta rincian per dombak,
 * plus riwayat seluruh pergerakan stok yang menyentuhnya.
 */
class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());
        $cari = mb_strtolower(trim((string) $request->input('cari')));

        $paginator = StokInventory::query()
            ->when($spbu, fn ($q) => $q->whereHas('produk', fn ($p) => $p->where('spbu_id', $spbu->spbu_id)))
            ->when($cari !== '', fn ($q) => $q->whereHas(
                'produk',
                fn ($p) => $p->whereRaw('LOWER(nama_produk) LIKE ?', ['%' . $cari . '%'])
            ))
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (StokInventory $s) => $this->ringkas($s));
    }

    public function show(string $id)
    {
        $inv = StokInventory::find($id);
        if (is_null($inv)) {
            return ApiResponse::fail('Data stok tidak ditemukan.', 404);
        }

        return ApiResponse::ok(array_merge($this->ringkas($inv), [
            'dombak' => $inv->stok_inventory_dombak()->get()->map(fn ($d) => [
                'stok_inventory_dombak_id' => $d->stok_inventory_dombak_id,
                'dombak_id' => $d->dombak_id,
                'dombak' => optional($d->dombak()->first())->nama_dombak,
                'stok' => Uang::of($d->stok),
            ])->sortBy('dombak')->values()->all(),
            'total_dokumen' => $inv->dokumen_stok()->count(),
        ]));
    }

    /** Riwayat pergerakan stok untuk satu produk, terbaru lebih dulu. */
    public function riwayat(Request $request, string $id)
    {
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'cari' => ['nullable', 'string', 'max:100'],
            'type_dokumen_stok_id' => ['nullable', 'exists:type_dokumen_stok,type_dokumen_stok_id'],
            'dombak_id' => ['nullable', 'exists:dombak,dombak_id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $inv = StokInventory::find($id);
        if (is_null($inv)) {
            return ApiResponse::fail('Data stok tidak ditemukan.', 404);
        }

        $paginator = $inv->dokumen_stok()
            ->when($request->filled('dari'), fn ($q) => $q->where('tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('type_dokumen_stok_id'), fn ($q) => $q->where('type_dokumen_stok_id', $request->input('type_dokumen_stok_id')))
            ->when($request->filled('dombak_id'), fn ($q) => $q->where('dombak_id', $request->input('dombak_id')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereHas('type_dokumen_stok', fn ($t) => $t->whereRaw('LOWER(type_movement) LIKE ?', [$pola]))
                    ->orWhereHas('dombak', fn ($d) => $d->whereRaw('LOWER(nama_dombak) LIKE ?', [$pola])));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn ($d) => Persediaan::serialDokumen($d));
    }

    /** Pilihan penyaring riwayat: jenis pergerakan & dombak milik produk ini. */
    public function opsiRiwayat(string $id)
    {
        $inv = StokInventory::find($id);
        if (is_null($inv)) {
            return ApiResponse::fail('Data stok tidak ditemukan.', 404);
        }

        return ApiResponse::ok([
            'tipe' => \App\Models\TypeDokumenStok::orderBy('type_movement')->get()
                ->map(fn ($t) => ['value' => $t->type_dokumen_stok_id, 'label' => $t->type_movement])
                ->values()->all(),
            'dombak' => $inv->stok_inventory_dombak()->get()
                ->map(fn ($d) => [
                    'value' => $d->dombak_id,
                    'label' => optional($d->dombak()->first())->nama_dombak ?? '-',
                ])->values()->all(),
        ]);
    }

    /**
     * Buat catatan stok untuk produk yang belum punya (saldo awal per dombak).
     * Satu produk hanya boleh punya satu baris stok_inventory.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => ['required', 'exists:produk,produk_id'],
            'dombak' => ['required', 'array', 'min:1'],
            'dombak.*.dombak_id' => ['required', 'exists:dombak,dombak_id'],
            'dombak.*.stok' => ['required', 'numeric', 'min:0'],
        ]);

        $produk = Produk::find($request->input('produk_id'));
        if ($produk->stok_inventory()->count() > 0) {
            return ApiResponse::fail('Produk ini sudah punya catatan stok. Gunakan menu ubah saldo.', 422);
        }

        $inv = DB::transaction(function () use ($produk, $request) {
            $inv = $produk->stok_inventory()->create(['stok' => '0']);

            foreach ($request->input('dombak') as $baris) {
                $inv->stok_inventory_dombak()->create([
                    'dombak_id' => $baris['dombak_id'],
                    'stok' => Uang::of($baris['stok']),
                ]);
            }

            Persediaan::sinkronkanTotal($inv);

            return $inv;
        });

        return ApiResponse::ok($this->ringkas($inv->fresh()), 'Catatan stok dibuat.', 201);
    }

    /** Hubungkan satu dombak ke produk (saldo awal 0). */
    public function tambahDombak(Request $request, string $id)
    {
        $inv = StokInventory::find($id);
        if (is_null($inv)) {
            return ApiResponse::fail('Data stok tidak ditemukan.', 404);
        }

        $request->validate([
            'dombak_id' => ['required', 'exists:dombak,dombak_id'],
            'stok' => ['nullable', 'numeric', 'min:0'],
        ]);

        $dombakId = $request->input('dombak_id');

        if ($inv->stok_inventory_dombak()->where('dombak_id', $dombakId)->exists()) {
            return ApiResponse::fail('Dombak ini sudah terhubung ke produk tersebut.', 422);
        }

        // satu dombak hanya boleh memegang satu produk
        $lain = DB::table('stok_inventory_dombak')->where('dombak_id', $dombakId)->first();
        if ($lain) {
            return ApiResponse::fail('Dombak ini sudah dipakai produk lain. Lepas dulu dari produk tersebut.', 422);
        }

        DB::transaction(function () use ($inv, $dombakId, $request) {
            $inv->stok_inventory_dombak()->create([
                'dombak_id' => $dombakId,
                'stok' => Uang::of($request->input('stok', 0)),
            ]);
            Persediaan::sinkronkanTotal($inv);
        });

        return ApiResponse::ok($this->ringkas($inv->fresh()), 'Dombak dihubungkan.');
    }

    public function hapusDombak(string $id, string $dombakId)
    {
        $inv = StokInventory::find($id);
        if (is_null($inv)) {
            return ApiResponse::fail('Data stok tidak ditemukan.', 404);
        }

        $baris = $inv->stok_inventory_dombak()->where('dombak_id', $dombakId)->first();
        if (is_null($baris)) {
            return ApiResponse::fail('Dombak ini tidak terhubung ke produk tersebut.', 404);
        }

        $dokumen = $inv->dokumen_stok()->where('dombak_id', $dombakId)->count();
        if ($dokumen > 0) {
            return ApiResponse::fail(
                "Dombak tidak bisa dilepas karena sudah punya {$dokumen} dokumen pergerakan stok.",
                422
            );
        }

        DB::transaction(function () use ($baris, $inv) {
            $baris->delete();
            Persediaan::sinkronkanTotal($inv);
        });

        return ApiResponse::ok($this->ringkas($inv->fresh()), 'Dombak dilepas.');
    }

    /**
     * Koreksi saldo dombak secara manual. Dicatat sebagai dokumen
     * "Commit Change From User" agar tetap ada jejak, bukan menimpa diam-diam.
     */
    public function koreksi(Request $request, string $id)
    {
        $inv = StokInventory::find($id);
        if (is_null($inv)) {
            return ApiResponse::fail('Data stok tidak ditemukan.', 404);
        }

        $request->validate([
            'dombak_id' => ['required', 'exists:dombak,dombak_id'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'stok' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $baris = $inv->stok_inventory_dombak()->where('dombak_id', $request->input('dombak_id'))->first();
        if (is_null($baris)) {
            return ApiResponse::fail('Dombak tidak terhubung ke produk ini.', 422);
        }

        $memberId = $request->user()->member()->first()->member_id;

        try {
            $dokumen = DB::transaction(function () use ($inv, $baris, $request, $memberId) {
                $dokumen = Persediaan::dokumen(
                    Persediaan::tipe('Commit Change From User'),
                    $inv,
                    $memberId,
                    $request->input('dombak_id'),
                    $request->input('jam_kerja_id'),
                    $request->input('tanggal'),
                    $baris->stok,
                    $request->input('stok')
                );

                Persediaan::terapkan($dokumen);

                return $dokumen;
            });
        } catch (\RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(
            Persediaan::serialDokumen($dokumen->fresh(), lengkap: true),
            'Saldo dikoreksi dan tercatat sebagai perubahan manual.'
        );
    }

    private function ringkas(StokInventory $s): array
    {
        $produk = $s->produk()->first();
        $jumlahDombak = $s->stok_inventory_dombak()->count();

        return [
            'id' => $s->stok_inventory_id,
            'stok_inventory_id' => $s->stok_inventory_id,
            'judul' => optional($produk)->nama_produk ?? '-',
            'subjudul' => Uang::of($s->stok) . ' L · ' . $jumlahDombak . ' dombak',
            'produk_id' => optional($produk)->produk_id,
            'produk' => optional($produk)->nama_produk,
            'stok' => Uang::of($s->stok),
            'jumlah_dombak' => $jumlahDombak,
        ];
    }
}
