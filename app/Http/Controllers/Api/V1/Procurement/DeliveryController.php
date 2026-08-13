<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Models\DetailRencanaPembelian;
use App\Models\FileLo;
use App\Models\Pengiriman;
use App\Support\ApiResponse;
use App\Support\Uang;
use App\Support\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tahap 5 — Delivery (pengiriman & dokumen LO).
 * Setiap produk yang diangkut didokumentasikan sebagai satu file_lo
 * (Loading Order) yang nantinya menjadi dasar Penerimaan BBM di modul stok.
 */
class DeliveryController extends ProcurementController
{
    private const FOLDER = 'public/file_lo';

    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', 'in:berjalan,selesai,semua']]);

        $query = Pengiriman::query()
            ->when($request->filled('cari'), fn ($q) => $q->where(function ($w) use ($request) {
                $pola = '%' . mb_strtolower($request->cari) . '%';
                $w->whereRaw('LOWER(no_do) LIKE ?', [$pola])->orWhereRaw('LOWER(no_pol) LIKE ?', [$pola]);
            }));

        match ($request->input('status', 'berjalan')) {
            'selesai' => $query->where('is_done', 1),
            'semua' => null,
            default => $query->where('is_done', 0),
        };

        $paginator = $query->orderBy('tanggal', 'desc')->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (Pengiriman $p) => self::kirimArray($p));
    }

    public function show(string $id)
    {
        $pengiriman = Pengiriman::find($id);

        return $pengiriman
            ? ApiResponse::ok(self::kirimArray($pengiriman, lengkap: true))
            : ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
    }

    public function ubahCatatan(Request $request, string $id)
    {
        $pengiriman = Pengiriman::find($id);
        if (! $pengiriman) {
            return ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
        }

        $request->validate([
            'catatan' => ['nullable', 'string'],
            'no_do' => ['nullable', 'string', 'max:32'],
            'no_pol' => ['nullable', 'string', 'max:16'],
        ]);

        $pengiriman->update(array_filter([
            'catatan' => $request->input('catatan'),
            'no_do' => $request->input('no_do'),
            'no_pol' => $request->input('no_pol'),
        ], fn ($v) => ! is_null($v)));

        return ApiResponse::ok(self::kirimArray($pengiriman->fresh(), lengkap: true), 'Data pengiriman diperbarui.');
    }

    public function konfirmasi(Request $request, string $id)
    {
        $pengiriman = Pengiriman::find($id);
        if (! $pengiriman) {
            return ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
        }

        $request->validate([
            'peran' => ['required', Rule::in(['admin', 'top'])],
            'setuju' => ['required', 'boolean'],
        ]);

        $kolom = $request->input('peran') === 'admin' ? 'is_confirmation_admin' : 'is_confirmation_top';
        $pengiriman->update([$kolom => $request->boolean('setuju') ? 1 : 0]);

        return ApiResponse::ok(
            self::kirimArray($pengiriman->fresh(), lengkap: true),
            ($request->input('peran') === 'admin' ? 'Admin' : 'Top Manajemen')
                . ($request->boolean('setuju') ? ' mengonfirmasi pengiriman.' : ' mencabut konfirmasi pengiriman.')
        );
    }

    public function selesai(string $id)
    {
        $pengiriman = Pengiriman::find($id);
        if (! $pengiriman) {
            return ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
        }

        if ($pengiriman->file_lo()->count() === 0) {
            return ApiResponse::fail('Unggah minimal satu dokumen LO sebelum menyelesaikan pengiriman.', 422);
        }

        $pengiriman->update(['is_done' => 1]);

        return ApiResponse::ok(self::kirimArray($pengiriman->fresh(), lengkap: true), 'Pengiriman ditandai selesai.');
    }

    // ----------------------------------------------------------- dokumen LO

    public function loIndex(string $id)
    {
        $pengiriman = Pengiriman::find($id);
        if (! $pengiriman) {
            return ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
        }

        return ApiResponse::ok(['items' => self::loArray($pengiriman)]);
    }

    /**
     * Unggah LO untuk satu baris produk. Kuantitas & satuan disalin dari baris
     * rencana pembelian supaya angka LO tidak menyimpang dari dokumen asal.
     */
    public function loStore(Request $request, string $id)
    {
        $pengiriman = Pengiriman::find($id);
        if (! $pengiriman) {
            return ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
        }

        $request->validate([
            'detail_rencana_pembelian_id' => ['required', 'exists:detail_rencana_pembelian,detail_rencana_pembelian_id'],
            'no_faktur' => ['required', 'string', 'max:16'],
            'no_lo' => ['required', 'string', 'max:16'],
            'berkas' => ['required', 'image', 'max:' . Upload::maxKb()],
        ]);

        $detail = DetailRencanaPembelian::find($request->input('detail_rencana_pembelian_id'));

        $file = $request->file('berkas');
        $asli = $file->getClientOriginalName();
        $nama = pathinfo($asli, PATHINFO_FILENAME) . '_' . time() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
        $file->storeAs(self::FOLDER, $nama);

        DB::transaction(function () use ($pengiriman, $detail, $request, $nama, $asli) {
            $lo = $pengiriman->file_lo()->create([
                'produk_id' => $detail->produk_id,
                'jumlah' => Uang::of($detail->jumlah),
                'satuan_jumlah' => $detail->satuan_jumlah,
                'no_faktur' => $request->input('no_faktur'),
                'no_lo' => $request->input('no_lo'),
                'nama_dokumen' => mb_substr($asli, 0, 128),
                'nama_file' => $nama,
                'tanggal_upload' => now()->format('Y-m-d'),
            ]);

            DB::table('detail_rencana_file')->insertOrIgnore([
                'detail_rencana_pembelian_id' => $detail->detail_rencana_pembelian_id,
                'file_lo_id' => $lo->file_lo_id,
            ]);

            $detail->update(['ms2' => 0]);
        });

        return ApiResponse::ok(['items' => self::loArray($pengiriman->fresh())], 'Dokumen LO diunggah.', 201);
    }

    public function loDestroy(string $id, string $loId)
    {
        $pengiriman = Pengiriman::find($id);
        if (! $pengiriman) {
            return ApiResponse::fail('Pengiriman tidak ditemukan.', 404);
        }

        $lo = $pengiriman->file_lo()->where('file_lo_id', $loId)->first();
        if (! $lo) {
            return ApiResponse::fail('Dokumen LO tidak ditemukan.', 404);
        }

        if ($lo->penerimaan()->count() > 0) {
            return ApiResponse::fail('LO ini sudah dipakai penerimaan BBM dan tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($lo) {
            DB::table('detail_rencana_file')->where('file_lo_id', $lo->file_lo_id)->delete();
            Storage::delete(self::FOLDER . '/' . $lo->nama_file);
            $lo->delete();
        });

        return ApiResponse::ok(['items' => self::loArray($pengiriman->fresh())], 'Dokumen LO dihapus.');
    }

    // --------------------------------------------------------- serialisasi

    public static function kirimArray(Pengiriman $p, bool $lengkap = false): array
    {
        $lo = $p->file_lo()->get();
        $admin = self::bool($p->is_confirmation_admin);
        $top = self::bool($p->is_confirmation_top);

        $data = [
            'id' => $p->pengiriman_id,
            'pengiriman_id' => $p->pengiriman_id,
            'judul' => ($p->no_do ?: 'Tanpa DO') . ' · ' . $p->no_pol,
            'subjudul' => $p->tanggal . ' · ' . $lo->count() . ' LO · ' . (self::bool($p->is_done) ? 'Selesai' : 'Berjalan'),
            'tanggal' => $p->tanggal,
            'no_do' => $p->no_do,
            'no_pol' => $p->no_pol,
            'is_confirmation_admin' => $admin,
            'is_confirmation_top' => $top,
            'is_done' => self::bool($p->is_done),
        ];

        if (! $lengkap) {
            return $data;
        }

        $daftarLo = self::loArray($p);

        return array_merge($data, [
            'catatan' => $p->catatan,
            'supir' => $p->supir()->get()->pluck('nama_supir')->values()->all(),
            'pembawa' => optional(optional($p->member()->first())->ktp()->first())->nama,
            'lo' => $daftarLo,
            'kalkulasi' => [
                'jumlah_lo' => count($daftarLo),
                'total_kuantitas' => Uang::jumlah($daftarLo, 'jumlah'),
            ],
            'ms2' => $p->ms2()->get()->map(fn ($m) => [
                'ms2_id' => $m->ms2_id,
                'kode_sms' => $m->kode_sms,
                'tanggal' => $m->tanggal,
            ])->values()->all(),
            'kandidat_lo' => self::kandidatLo($p),
        ]);
    }

    /**
     * Baris produk yang ikut diangkut pengiriman ini tetapi belum punya
     * dokumen LO — dipakai klien sebagai pilihan saat mengunggah LO.
     */
    private static function kandidatLo(Pengiriman $p): array
    {
        $sudahAdaLo = DB::table('detail_rencana_file')
            ->whereIn('file_lo_id', $p->file_lo()->pluck('file_lo_id'))
            ->pluck('detail_rencana_pembelian_id')
            ->all();

        $items = [];
        foreach ($p->ms2()->get() as $ms2) {
            foreach ($ms2->detail_rencana_pembelian_ms2()->where('is_delivery', 1)->get() as $rel) {
                if (in_array($rel->detail_rencana_pembelian_id, $sudahAdaLo, true)) {
                    continue;
                }

                $detail = $rel->detail_rencana_pembelian()->first();
                if (is_null($detail)) {
                    continue;
                }

                $items[] = array_merge(self::detailArray($detail), [
                    'kode_pembelian' => optional($detail->rencana_pembelian()->first())->kode_pembelian,
                ]);
            }
        }

        return $items;
    }

    private static function loArray(Pengiriman $p): array
    {
        return $p->file_lo()->get()->map(fn (FileLo $lo) => [
            'id' => $lo->file_lo_id,
            'file_lo_id' => $lo->file_lo_id,
            'produk' => optional($lo->produk()->first())->nama_produk,
            'jumlah' => Uang::of($lo->jumlah),
            'satuan_jumlah' => $lo->satuan_jumlah,
            'no_faktur' => $lo->no_faktur,
            'no_lo' => $lo->no_lo,
            'nama_dokumen' => $lo->nama_dokumen,
            'tanggal_upload' => $lo->tanggal_upload,
            'url' => asset('storage/file_lo/' . $lo->nama_file),
            'sudah_diterima' => $lo->penerimaan()->count() > 0,
        ])->values()->all();
    }
}
