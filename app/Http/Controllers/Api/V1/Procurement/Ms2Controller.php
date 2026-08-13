<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Models\DetailRencanaPembelian;
use App\Models\DetailRencanaPembelianMs2;
use App\Models\Ms2;
use App\Models\Pengiriman;
use App\Models\RencanaPembelian;
use App\Support\ApiResponse;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tahap 4 — MS2 (alokasi stok di Pertamina).
 * Baris produk yang sudah dibayar didaftarkan ke satu dokumen MS2 beserta
 * shift pengambilannya, lalu dikirim ke tahap Delivery.
 */
class Ms2Controller extends ProcurementController
{
    public function index(Request $request)
    {
        $paginator = Ms2::query()
            ->when($request->filled('cari'), fn ($q) => $q->whereRaw('LOWER(kode_sms) LIKE ?', ['%' . mb_strtolower($request->cari) . '%']))
            ->orderBy('tanggal', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (Ms2 $m) => self::ms2Array($m));
    }

    /**
     * Baris produk yang siap di-MS2-kan: dokumen sudah di tahap MS2 dan
     * barisnya belum dialokasikan maupun dikirim.
     */
    public function kandidat(Request $request)
    {
        $status = self::status('MS2');
        if (is_null($status)) {
            return ApiResponse::ok(['items' => []]);
        }

        $rencana = RencanaPembelian::where('status_purchase_id', $status->status_purchase_id)->get();

        $items = [];
        foreach ($rencana as $r) {
            foreach ($r->detail_rencana_pembelian()->get() as $d) {
                if (self::bool($d->ms2) || self::bool($d->delivery)) {
                    continue;
                }
                $items[] = array_merge(self::detailArray($d), [
                    'rencana_pembelian_id' => $r->rencana_pembelian_id,
                    'kode_pembelian' => $r->kode_pembelian,
                ]);
            }
        }

        return ApiResponse::ok(['items' => $items]);
    }

    public function show(string $id)
    {
        $ms2 = Ms2::find($id);

        return $ms2
            ? ApiResponse::ok(self::ms2Array($ms2, lengkap: true))
            : ApiResponse::fail('Dokumen MS2 tidak ditemukan.', 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.detail_rencana_pembelian_id' => ['required', 'exists:detail_rencana_pembelian,detail_rencana_pembelian_id'],
            'items.*.jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
        ]);

        $ms2 = DB::transaction(function () use ($request) {
            $ms2 = Ms2::create([
                'member_id' => self::memberAktif(),
                'tanggal' => $request->input('tanggal'),
                'is_done' => 0,
                // kode_sms di web adalah hash rekap; di sini dipakai penanda unik
                'kode_sms' => 'MS2-' . strtoupper(Str::random(8)),
            ]);

            foreach ($request->input('items') as $item) {
                DetailRencanaPembelian::where('detail_rencana_pembelian_id', $item['detail_rencana_pembelian_id'])
                    ->update(['ms2' => 1]);

                DetailRencanaPembelianMs2::create([
                    'ms2_id' => $ms2->ms2_id,
                    'detail_rencana_pembelian_id' => $item['detail_rencana_pembelian_id'],
                    'jam_kerja_id' => $item['jam_kerja_id'],
                    'is_delivery' => 0,
                ]);
            }

            return $ms2;
        });

        return ApiResponse::ok(self::ms2Array($ms2->fresh(), lengkap: true), 'Dokumen MS2 dibuat.', 201);
    }

    public function destroy(string $id)
    {
        $ms2 = Ms2::find($id);
        if (! $ms2) {
            return ApiResponse::fail('Dokumen MS2 tidak ditemukan.', 404);
        }
        if (self::bool($ms2->is_done)) {
            return ApiResponse::fail('MS2 yang sudah dikirim ke Delivery tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($ms2) {
            // kembalikan flag baris agar bisa dialokasikan ulang
            foreach ($ms2->detail_rencana_pembelian_ms2()->get() as $rel) {
                DetailRencanaPembelian::where('detail_rencana_pembelian_id', $rel->detail_rencana_pembelian_id)
                    ->update(['ms2' => 0]);
                $rel->delete();
            }
            $ms2->delete();
        });

        return ApiResponse::ok([], 'Dokumen MS2 dihapus dan produknya dikembalikan ke daftar kandidat.');
    }

    /**
     * Kirim ke tahap Delivery: buat dokumen Pengiriman dan tandai baris
     * terpilih sebagai delivery.
     */
    public function kirim(Request $request, string $id)
    {
        $ms2 = Ms2::find($id);
        if (! $ms2) {
            return ApiResponse::fail('Dokumen MS2 tidak ditemukan.', 404);
        }

        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'no_do' => ['nullable', 'string', 'max:32'],
            'no_pol' => ['required', 'string', 'max:16'],
            'catatan' => ['nullable', 'string'],
            'nama_supir' => ['nullable', 'string', 'max:255'],
            'detail_ids' => ['required', 'array', 'min:1'],
            'detail_ids.*' => ['string'],
        ]);

        $terpilih = $request->input('detail_ids');

        $pengiriman = DB::transaction(function () use ($request, $ms2, $terpilih) {
            $pengiriman = Pengiriman::create([
                'member_id' => $ms2->member_id ?? self::memberAktif(),
                'tanggal' => $request->input('tanggal'),
                'no_do' => $request->input('no_do'),
                'no_pol' => $request->input('no_pol'),
                'is_confirmation_admin' => 0,
                'is_confirmation_top' => 0,
                'is_done' => 0,
                'catatan' => $request->input('catatan'),
            ]);

            if ($request->filled('nama_supir')) {
                $pengiriman->supir()->create(['nama_supir' => $request->input('nama_supir')]);
            }

            foreach ($ms2->detail_rencana_pembelian_ms2()->get() as $rel) {
                $ikut = in_array($rel->detail_rencana_pembelian_id, $terpilih, true);

                DetailRencanaPembelian::where('detail_rencana_pembelian_id', $rel->detail_rencana_pembelian_id)
                    ->update(['ms2' => 0, 'delivery' => $ikut ? 1 : 0]);

                if ($ikut) {
                    $rel->update(['is_delivery' => 1]);
                }
            }

            DB::table('pengiriman_ms2')->insertOrIgnore([
                'pengiriman_id' => $pengiriman->pengiriman_id,
                'ms2_id' => $ms2->ms2_id,
            ]);

            $ms2->update(['is_done' => 1]);

            return $pengiriman;
        });

        return ApiResponse::ok(
            array_merge(self::ms2Array($ms2->fresh(), lengkap: true), ['pengiriman_id' => $pengiriman->pengiriman_id]),
            'MS2 dikirim ke tahap Delivery.'
        );
    }

    // --------------------------------------------------------- serialisasi

    public static function ms2Array(Ms2 $m, bool $lengkap = false): array
    {
        $relasi = $m->detail_rencana_pembelian_ms2()->get();

        $data = [
            'id' => $m->ms2_id,
            'ms2_id' => $m->ms2_id,
            'judul' => $m->kode_sms ?? 'MS2',
            'subjudul' => $m->tanggal . ' · ' . $relasi->count() . ' produk · ' . (self::bool($m->is_done) ? 'Sudah dikirim' : 'Menunggu'),
            'tanggal' => $m->tanggal,
            'kode_sms' => $m->kode_sms,
            'is_done' => self::bool($m->is_done),
            'jumlah_produk' => $relasi->count(),
        ];

        if (! $lengkap) {
            return $data;
        }

        $baris = $relasi->map(function (DetailRencanaPembelianMs2 $rel) {
            $detail = $rel->detail_rencana_pembelian()->first();
            if (is_null($detail)) {
                return null;
            }
            $rencana = $detail->rencana_pembelian()->first();

            return array_merge(self::detailArray($detail), [
                'jam_kerja' => optional($rel->jam_kerja()->first())->nama_jam_kerja,
                'jam_kerja_id' => $rel->jam_kerja_id,
                'is_delivery' => self::bool($rel->is_delivery),
                'kode_pembelian' => optional($rencana)->kode_pembelian,
            ]);
        })->filter()->values();

        return array_merge($data, [
            'pembuat' => optional(optional($m->member()->first())->ktp()->first())->nama,
            'detail' => $baris->all(),
            'kalkulasi' => [
                'total_kuantitas' => Uang::jumlah($baris, 'jumlah'),
                'total_rit' => Uang::jumlah($baris, 'rit'),
                'total_nilai' => Uang::jumlah($baris, 'total_harga'),
            ],
            'pengiriman' => $m->pengiriman()->get()->map(fn (Pengiriman $p) => [
                'pengiriman_id' => $p->pengiriman_id,
                'no_do' => $p->no_do,
                'no_pol' => $p->no_pol,
                'tanggal' => $p->tanggal,
            ])->values()->all(),
        ]);
    }
}
