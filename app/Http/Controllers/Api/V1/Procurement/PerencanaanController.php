<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Models\DetailRencanaPembelian;
use App\Models\RencanaPembelian;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tahap 1 — Perencanaan (Purchase Requisition).
 * Rencana dibuat kosong, diisi baris produk, lalu dikonfirmasi untuk naik ke
 * tahap Pembelian (atau ditolak dengan alasan).
 */
class PerencanaanController extends ProcurementController
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:aktif,ditolak,semua'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $status = self::status('Rencana');
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());

        $query = RencanaPembelian::query()
            ->when($status, fn ($q) => $q->where('status_purchase_id', $status->status_purchase_id))
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->when($request->filled('cari'), fn ($q) => $q->whereRaw('LOWER(kode_pembelian) LIKE ?', ['%' . mb_strtolower($request->cari) . '%']));

        match ($request->input('status', 'aktif')) {
            'ditolak' => $query->where('is_denied', 1),
            'semua' => null,
            default => $query->where('is_denied', 0),
        };

        $paginator = $query->orderBy('created_at', 'desc')->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (RencanaPembelian $r) => self::rencanaArray($r));
    }

    public function show(string $id)
    {
        $rencana = RencanaPembelian::find($id);

        return $rencana
            ? ApiResponse::ok(self::rencanaArray($rencana, lengkap: true))
            : ApiResponse::fail('Rencana pembelian tidak ditemukan.', 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'kode_pembelian' => ['nullable', 'string', 'max:32', 'unique:rencana_pembelian,kode_pembelian'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $status = self::status('Rencana');
        if (is_null($status)) {
            return ApiResponse::fail('Status "Rencana" belum ada. Jalankan StatusPurchaseSeeder.', 422);
        }

        $rencana = RencanaPembelian::create([
            'status_purchase_id' => $status->status_purchase_id,
            'spbu_id' => $request->input('spbu_id'),
            'member_id' => self::memberAktif(),
            'grand_total' => '0.000000',
            'kode_pembelian' => $request->input('kode_pembelian') ?: self::kodeBaru(),
            'pembelian_id' => null,
            'tanggal_pembelian' => null,
            'keterangan' => $request->input('keterangan'),
            'keterangan_denied' => null,
            'is_confirmation' => 0,
            'is_denied' => 0,
        ]);

        return ApiResponse::ok(self::rencanaArray($rencana, lengkap: true), 'Rencana pembelian dibuat.', 201);
    }

    public function update(Request $request, string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Rencana pembelian tidak ditemukan.', 404);
        }
        if ($gagal = $this->pastikanMasihRencana($rencana)) {
            return $gagal;
        }

        $request->validate(['keterangan' => ['nullable', 'string']]);
        $rencana->update(['keterangan' => $request->input('keterangan')]);

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Rencana pembelian diperbarui.');
    }

    public function destroy(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Rencana pembelian tidak ditemukan.', 404);
        }

        if ($rencana->pembayaran()->count() > 0) {
            return ApiResponse::fail('Rencana ini sudah punya data pembayaran dan tidak bisa dihapus.', 422);
        }
        if (self::bool($rencana->is_confirmation) && ! self::bool($rencana->is_denied)) {
            return ApiResponse::fail('Rencana yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }

        $rencana->delete();

        return ApiResponse::ok([], 'Rencana pembelian dihapus.');
    }

    /** Konfirmasi PJ: setuju → naik ke tahap Pembelian, tolak → ditandai denied. */
    public function konfirmasi(Request $request, string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Rencana pembelian tidak ditemukan.', 404);
        }

        $request->validate([
            'setuju' => ['required', 'boolean'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if ($rencana->detail_rencana_pembelian()->count() === 0) {
            return ApiResponse::fail('Tambahkan minimal satu produk sebelum mengonfirmasi.', 422);
        }

        $setuju = $request->boolean('setuju');

        DB::transaction(function () use ($rencana, $request, $setuju) {
            $atribut = [
                'is_confirmation' => 1,
                'is_denied' => $setuju ? 0 : 1,
                'keterangan_denied' => $setuju ? null : $request->input('keterangan'),
            ];

            if ($setuju) {
                $pembelian = self::status('Pembelian');
                if ($pembelian) {
                    $atribut['status_purchase_id'] = $pembelian->status_purchase_id;
                }
            }

            $rencana->update($atribut);

            // pajak dihitung dari grand_total terkini saat rencana disetujui
            if ($setuju) {
                self::segarkanPajak($rencana->fresh());
            }
        });

        return ApiResponse::ok(
            self::rencanaArray($rencana->fresh(), lengkap: true),
            $setuju ? 'Rencana disetujui dan dilanjutkan ke tahap Pembelian.' : 'Rencana ditolak.'
        );
    }

    // ------------------------------------------------------- baris produk

    public function tambahProduk(Request $request, string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Rencana pembelian tidak ditemukan.', 404);
        }
        if ($gagal = $this->pastikanMasihRencana($rencana)) {
            return $gagal;
        }

        $request->validate([
            'produk_id' => ['required', 'exists:produk,produk_id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'satuan_jumlah' => ['required', 'string', 'max:16'],
        ]);

        $detail = DB::transaction(function () use ($request, $rencana) {
            $jumlah = Uang::of($request->input('jumlah'));
            $harga = self::hargaBeliDefault($request->input('produk_id'));

            $detail = $rencana->detail_rencana_pembelian()->create([
                'produk_id' => $request->input('produk_id'),
                'jumlah' => $jumlah,
                'satuan_jumlah' => $request->input('satuan_jumlah'),
                'rit' => self::ritDisarankan($jumlah),
                'harga' => $harga,
                'total_harga' => Uang::kali($jumlah, $harga),
                'is_confirmation' => 0,
                'ms2' => 0,
                'delivery' => 0,
                // kolom kode_detail hanya varchar(16); dipakai web untuk
                // mengelompokkan baris, jadi cukup penanda pendek yang unik
                'kode_detail' => 'D-' . strtoupper(Str::random(12)),
            ]);

            self::hitungUlang($rencana);

            return $detail;
        });

        return ApiResponse::ok(
            self::rencanaArray($rencana->fresh(), lengkap: true),
            'Produk "' . optional($detail->produk()->first())->nama_produk . '" ditambahkan.',
            201
        );
    }

    public function ubahProduk(Request $request, string $id, string $detailId)
    {
        [$rencana, $detail, $gagal] = $this->cariDetail($id, $detailId);
        if ($gagal) {
            return $gagal;
        }
        if ($gagal = $this->pastikanMasihRencana($rencana)) {
            return $gagal;
        }

        $request->validate([
            'produk_id' => ['required', 'exists:produk,produk_id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'satuan_jumlah' => ['required', 'string', 'max:16'],
        ]);

        DB::transaction(function () use ($request, $rencana, $detail) {
            $jumlah = Uang::of($request->input('jumlah'));
            $harga = self::hargaBeliDefault($request->input('produk_id'));

            $detail->update([
                'produk_id' => $request->input('produk_id'),
                'jumlah' => $jumlah,
                'satuan_jumlah' => $request->input('satuan_jumlah'),
                'rit' => self::ritDisarankan($jumlah),
                'harga' => $harga,
                'total_harga' => Uang::kali($jumlah, $harga),
            ]);

            self::hitungUlang($rencana);
        });

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Baris produk diperbarui.');
    }

    public function hapusProduk(string $id, string $detailId)
    {
        [$rencana, $detail, $gagal] = $this->cariDetail($id, $detailId);
        if ($gagal) {
            return $gagal;
        }
        if ($gagal = $this->pastikanMasihRencana($rencana)) {
            return $gagal;
        }

        DB::transaction(function () use ($rencana, $detail) {
            $detail->delete();
            self::hitungUlang($rencana);
        });

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Baris produk dihapus.');
    }

    // ---------------------------------------------------------- pembantu

    /** @return array{0: ?RencanaPembelian, 1: ?DetailRencanaPembelian, 2: ?\Illuminate\Http\JsonResponse} */
    private function cariDetail(string $id, string $detailId): array
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return [null, null, ApiResponse::fail('Rencana pembelian tidak ditemukan.', 404)];
        }

        $detail = $rencana->detail_rencana_pembelian()->where('detail_rencana_pembelian_id', $detailId)->first();
        if (! $detail) {
            return [$rencana, null, ApiResponse::fail('Baris produk tidak ditemukan pada rencana ini.', 404)];
        }

        return [$rencana, $detail, null];
    }

    /** Baris hanya boleh diubah selama dokumen masih di tahap Rencana. */
    private function pastikanMasihRencana(RencanaPembelian $rencana): ?\Illuminate\Http\JsonResponse
    {
        $status = optional($rencana->status_purchase()->first())->nama_status;

        if ($status !== 'Rencana') {
            return ApiResponse::fail("Dokumen sudah berada di tahap \"$status\" dan tidak bisa diubah dari Perencanaan.", 422);
        }

        if (self::bool($rencana->is_confirmation) && ! self::bool($rencana->is_denied)) {
            return ApiResponse::fail('Rencana sudah dikonfirmasi dan tidak bisa diubah lagi.', 422);
        }

        return null;
    }

    /** Kode berformat A-YYYYMMDD-n, mengikuti generateKodePembelian() di web. */
    private static function kodeBaru(): string
    {
        $prefix = 'A-' . date('Ymd') . '-';
        $i = 1;
        while (RencanaPembelian::where('kode_pembelian', $prefix . $i)->exists()) {
            $i++;
        }

        return $prefix . $i;
    }
}
