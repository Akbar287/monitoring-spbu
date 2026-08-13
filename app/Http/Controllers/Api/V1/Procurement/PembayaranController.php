<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Models\Pembayaran;
use App\Models\RencanaPembelian;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Uang;
use App\Support\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tahap 3 — Pembayaran (Invoice).
 * Satu rencana bisa dibayar beberapa kali (cek/BG). Tiap pembayaran perlu
 * konfirmasi ganda: Admin dan Top Manajemen.
 */
class PembayaranController extends ProcurementController
{
    private const FOLDER = 'public/pembayaran';

    public function index(Request $request)
    {
        $status = self::status('Pembayaran');
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());

        $paginator = RencanaPembelian::query()
            ->when($status, fn ($q) => $q->where('status_purchase_id', $status->status_purchase_id))
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->when($request->filled('cari'), fn ($q) => $q->whereRaw('LOWER(kode_pembelian) LIKE ?', ['%' . mb_strtolower($request->cari) . '%']))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, function (RencanaPembelian $r) {
            $ringkas = self::rencanaArray($r);
            $ringkas['subjudul'] = 'Tagihan Rp ' . self::tagihan($r) . ' · sisa Rp ' . self::rekap($r)['sisa'];

            return $ringkas;
        });
    }

    public function show(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Dokumen pembelian tidak ditemukan.', 404);
        }

        return ApiResponse::ok(array_merge(
            self::rencanaArray($rencana, lengkap: true),
            [
                'rekap_pembayaran' => self::rekap($rencana),
                'pembayaran' => $rencana->pembayaran()->orderBy('tanggal', 'desc')->get()
                    ->map(fn (Pembayaran $p) => self::bayarArray($p))->values()->all(),
            ]
        ));
    }

    public function store(Request $request, string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Dokumen pembelian tidak ditemukan.', 404);
        }

        $request->validate($this->aturan());

        $pembayaran = $rencana->pembayaran()->create([
            'member_id' => self::memberAktif(),
            'tanggal' => $request->input('tanggal'),
            'no_cek_bg' => $request->input('no_cek_bg'),
            'no_rekening' => $request->input('no_rekening'),
            'nama_rekening' => $request->input('nama_rekening'),
            'total_setoran' => Uang::of($request->input('total_setoran')),
            'is_confirmation_admin' => 0,
            'is_confirmation_top' => 0,
        ]);

        return ApiResponse::ok(self::bayarArray($pembayaran), 'Pembayaran dicatat.', 201);
    }

    public function update(Request $request, string $id, string $bayarId)
    {
        [$rencana, $pembayaran, $gagal] = $this->cari($id, $bayarId);
        if ($gagal) {
            return $gagal;
        }

        if (self::bool($pembayaran->is_confirmation_admin) || self::bool($pembayaran->is_confirmation_top)) {
            return ApiResponse::fail('Pembayaran yang sudah dikonfirmasi tidak bisa diubah. Cabut konfirmasi lebih dulu.', 422);
        }

        $request->validate($this->aturan());

        $pembayaran->update([
            'tanggal' => $request->input('tanggal'),
            'no_cek_bg' => $request->input('no_cek_bg'),
            'no_rekening' => $request->input('no_rekening'),
            'nama_rekening' => $request->input('nama_rekening'),
            'total_setoran' => Uang::of($request->input('total_setoran')),
        ]);

        return ApiResponse::ok(self::bayarArray($pembayaran->fresh()), 'Pembayaran diperbarui.');
    }

    public function destroy(string $id, string $bayarId)
    {
        [$rencana, $pembayaran, $gagal] = $this->cari($id, $bayarId);
        if ($gagal) {
            return $gagal;
        }

        if (self::bool($pembayaran->is_confirmation_admin) || self::bool($pembayaran->is_confirmation_top)) {
            return ApiResponse::fail('Pembayaran yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($pembayaran) {
            foreach ($pembayaran->file_pembayaran()->get() as $f) {
                Storage::delete(self::FOLDER . '/' . $f->nama_file);
                $f->delete();
            }
            $pembayaran->delete();
        });

        return ApiResponse::ok([], 'Pembayaran dihapus.');
    }

    /** Konfirmasi/pencabutan oleh Admin atau Top Manajemen. */
    public function konfirmasi(Request $request, string $id, string $bayarId)
    {
        [$rencana, $pembayaran, $gagal] = $this->cari($id, $bayarId);
        if ($gagal) {
            return $gagal;
        }

        $request->validate([
            'peran' => ['required', Rule::in(['admin', 'top'])],
            'setuju' => ['required', 'boolean'],
        ]);

        $kolom = $request->input('peran') === 'admin' ? 'is_confirmation_admin' : 'is_confirmation_top';
        $pembayaran->update([$kolom => $request->boolean('setuju') ? 1 : 0]);

        return ApiResponse::ok(
            self::bayarArray($pembayaran->fresh()),
            ($request->input('peran') === 'admin' ? 'Admin' : 'Top Manajemen')
                . ($request->boolean('setuju') ? ' mengonfirmasi pembayaran.' : ' mencabut konfirmasi pembayaran.')
        );
    }

    /** Lanjut ke tahap MS2 (alokasi stok Pertamina). */
    public function lanjut(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Dokumen pembelian tidak ditemukan.', 404);
        }

        $rekap = self::rekap($rencana);
        if ($rencana->pembayaran()->count() === 0) {
            return ApiResponse::fail('Belum ada pembayaran yang dicatat.', 422);
        }

        $status = self::status('MS2');
        if (is_null($status)) {
            return ApiResponse::fail('Status "MS2" belum ada. Jalankan StatusPurchaseSeeder.', 422);
        }

        $rencana->update(['status_purchase_id' => $status->status_purchase_id]);

        $pesan = 'Dokumen dilanjutkan ke tahap MS2.';
        if (Uang::bandingkan($rekap['sisa'], '0') > 0) {
            $pesan .= ' Catatan: masih ada sisa tagihan Rp ' . $rekap['sisa'] . '.';
        }

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), $pesan);
    }

    /** Tolak — kembalikan dokumen ke tahap Perencanaan. */
    public function tolak(string $id)
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return ApiResponse::fail('Dokumen pembelian tidak ditemukan.', 404);
        }

        $status = self::status('Rencana');
        $rencana->update([
            'status_purchase_id' => $status?->status_purchase_id ?? $rencana->status_purchase_id,
            'is_confirmation' => 1,
            'is_denied' => 0,
        ]);

        return ApiResponse::ok(self::rencanaArray($rencana->fresh(), lengkap: true), 'Dokumen dikembalikan ke tahap Perencanaan.');
    }

    // ------------------------------------------------------------ lampiran

    public function lampiranIndex(string $id, string $bayarId)
    {
        [$rencana, $pembayaran, $gagal] = $this->cari($id, $bayarId);
        if ($gagal) {
            return $gagal;
        }

        return ApiResponse::ok(['items' => self::lampiranArray($pembayaran)]);
    }

    public function lampiranStore(Request $request, string $id, string $bayarId)
    {
        [$rencana, $pembayaran, $gagal] = $this->cari($id, $bayarId);
        if ($gagal) {
            return $gagal;
        }

        $request->validate([
            'berkas' => ['required', 'file', 'max:' . Upload::maxKb()],
            'nama_dokumen' => ['nullable', 'string', 'max:64'],
        ]);

        $file = $request->file('berkas');
        $asli = $file->getClientOriginalName();
        $nama = pathinfo($asli, PATHINFO_FILENAME) . '_' . time() . '.' . ($file->getClientOriginalExtension() ?: 'bin');
        $file->storeAs(self::FOLDER, $nama);

        $pembayaran->file_pembayaran()->create([
            'nama_file' => $nama,
            // kolom nama_dokumen dibatasi 64 karakter
            'nama_dokumen' => mb_substr($request->input('nama_dokumen') ?: $asli, 0, 64),
            'tanggal_upload' => now()->format('Y-m-d'),
        ]);

        return ApiResponse::ok(['items' => self::lampiranArray($pembayaran->fresh())], 'Bukti pembayaran diunggah.', 201);
    }

    public function lampiranDestroy(string $id, string $bayarId, string $fileId)
    {
        [$rencana, $pembayaran, $gagal] = $this->cari($id, $bayarId);
        if ($gagal) {
            return $gagal;
        }

        $file = $pembayaran->file_pembayaran()->where('file_pembayaran_id', $fileId)->first();
        if (! $file) {
            return ApiResponse::fail('Lampiran tidak ditemukan.', 404);
        }

        Storage::delete(self::FOLDER . '/' . $file->nama_file);
        $file->delete();

        return ApiResponse::ok(['items' => self::lampiranArray($pembayaran->fresh())], 'Lampiran dihapus.');
    }

    // ------------------------------------------------------------ pembantu

    private function aturan(): array
    {
        return [
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'no_cek_bg' => ['required', 'string', 'max:64'],
            'no_rekening' => ['required', 'string', 'max:32'],
            'nama_rekening' => ['required', 'string', 'max:128'],
            'total_setoran' => ['required', 'numeric', 'gt:0'],
        ];
    }

    private function cari(string $id, string $bayarId): array
    {
        $rencana = RencanaPembelian::find($id);
        if (! $rencana) {
            return [null, null, ApiResponse::fail('Dokumen pembelian tidak ditemukan.', 404)];
        }

        $pembayaran = $rencana->pembayaran()->where('pembayaran_id', $bayarId)->first();
        if (! $pembayaran) {
            return [$rencana, null, ApiResponse::fail('Pembayaran tidak ditemukan pada dokumen ini.', 404)];
        }

        return [$rencana, $pembayaran, null];
    }

    /** Nilai tagihan = total_amount pajak bila ada, jika tidak grand_total. */
    private static function tagihan(RencanaPembelian $rencana): string
    {
        $pajak = $rencana->pajak_pembelian()->first();

        return Uang::of($pajak->total_amount ?? $rencana->grand_total);
    }

    /** Rekap pembayaran pada presisi penuh 6 desimal. */
    public static function rekap(RencanaPembelian $rencana): array
    {
        $semua = $rencana->pembayaran()->get();
        $tagihan = self::tagihan($rencana);
        $dibayar = Uang::jumlah($semua, 'total_setoran');

        $lunasTerkonfirmasi = Uang::jumlah(
            $semua->filter(fn (Pembayaran $p) => self::bool($p->is_confirmation_admin) && self::bool($p->is_confirmation_top)),
            'total_setoran'
        );

        return [
            'tagihan' => $tagihan,
            'dibayar' => $dibayar,
            'dibayar_terkonfirmasi' => $lunasTerkonfirmasi,
            'sisa' => Uang::kurang($tagihan, $dibayar),
            'sisa_terkonfirmasi' => Uang::kurang($tagihan, $lunasTerkonfirmasi),
            'jumlah_transaksi' => $semua->count(),
            'menunggu_konfirmasi' => $semua->filter(
                fn (Pembayaran $p) => ! self::bool($p->is_confirmation_admin) || ! self::bool($p->is_confirmation_top)
            )->count(),
        ];
    }

    public static function bayarArray(Pembayaran $p): array
    {
        $admin = self::bool($p->is_confirmation_admin);
        $top = self::bool($p->is_confirmation_top);

        return [
            'id' => $p->pembayaran_id,
            'pembayaran_id' => $p->pembayaran_id,
            'judul' => 'Rp ' . Uang::of($p->total_setoran),
            'subjudul' => $p->no_cek_bg . ' · ' . ($admin && $top ? 'Terkonfirmasi' : 'Menunggu konfirmasi'),
            'tanggal' => $p->tanggal,
            'no_cek_bg' => $p->no_cek_bg,
            'no_rekening' => $p->no_rekening,
            'nama_rekening' => $p->nama_rekening,
            'total_setoran' => Uang::of($p->total_setoran),
            'is_confirmation_admin' => $admin,
            'is_confirmation_top' => $top,
            'terkonfirmasi' => $admin && $top,
            'pencatat' => optional(optional($p->member()->first())->ktp()->first())->nama,
            'lampiran' => self::lampiranArray($p),
        ];
    }

    private static function lampiranArray(Pembayaran $p): array
    {
        return $p->file_pembayaran()->get()->map(function ($f) {
            $ext = strtolower(pathinfo($f->nama_file, PATHINFO_EXTENSION));

            return [
                'id' => $f->file_pembayaran_id,
                'nama_dokumen' => $f->nama_dokumen,
                'tanggal_upload' => $f->tanggal_upload,
                'url' => asset('storage/pembayaran/' . $f->nama_file),
                'is_gambar' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'], true),
            ];
        })->values()->all();
    }
}
