<?php

namespace App\Http\Controllers\Api\V1\Setoran;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Penjualan;
use App\Support\ApiResponse;
use App\Support\Uang;
use App\Support\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Setoran Ke Bank: catatan penarikan uang kotak ke rekening bank.
 *
 * `penarikan.is_accepted` memicu jurnal Bank/Kas otomatis
 * (App\Support\PostingKeuangan::penarikanBerubah).
 *
 * `penjualan.total_uang_kotak` selalu diturunkan ulang dari jumlah seluruh
 * penarikan pada shift tersebut, karena angka itu dipakai jurnal penjualan
 * untuk menghitung selisih kurang/lebih setor.
 */
class PenarikanController extends Controller
{
    public function index(Request $request, string $penjualanId)
    {
        $p = Penjualan::find($penjualanId);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $p->penarikan()
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereRaw('LOWER(nama_bank) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(no_referensi) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(metode_transfer) LIKE ?', [$pola]));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::paginated($paginator, fn (Penarikan $t) => $this->ringkas($t));
    }

    public function show(string $penjualanId, string $id)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        return ApiResponse::ok($this->lengkap($t));
    }

    public function store(Request $request, string $penjualanId)
    {
        $p = Penjualan::find($penjualanId);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $request->validate($this->aturan());

        $t = DB::transaction(function () use ($p, $request) {
            $t = $p->penarikan()->create($this->isi($request));
            $this->sinkronkanUangKotak($p);

            return $t;
        });

        return ApiResponse::ok($this->lengkap($t), 'Setoran ke bank dicatat.', 201);
    }

    public function update(Request $request, string $penjualanId, string $id)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        if (filter_var($t->is_accepted, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail(
                'Setoran yang sudah dikonfirmasi tidak bisa diubah — cabut konfirmasinya dulu.',
                422
            );
        }

        $request->validate($this->aturan());

        DB::transaction(function () use ($p, $t, $request) {
            $t->update($this->isi($request));
            $this->sinkronkanUangKotak($p);
        });

        return ApiResponse::ok($this->lengkap($t->fresh()), 'Setoran ke bank diperbarui.');
    }

    /**
     * Konfirmasi / cabut konfirmasi. Saat dikonfirmasi, model event Penarikan
     * memposting jurnal Bank (debit) / Kas Operator (kredit).
     */
    public function konfirmasi(Request $request, string $penjualanId, string $id)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        $request->validate(['setuju' => ['required', 'boolean']]);

        try {
            $t->update(['is_accepted' => $request->boolean('setuju') ? 1 : 0]);
        } catch (\RuntimeException $e) {
            // periode akuntansi tertutup
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(
            $this->lengkap($t->fresh()),
            $request->boolean('setuju') ? 'Setoran dikonfirmasi.' : 'Konfirmasi dicabut.'
        );
    }

    public function destroy(string $penjualanId, string $id)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        if (filter_var($t->is_accepted, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Setoran yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($p, $t) {
            foreach ($t->file_penarikan()->get() as $f) {
                $path = 'public/file_penarikan/' . $f->nama_file;
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
            $t->delete();
            $this->sinkronkanUangKotak($p);
        });

        return ApiResponse::ok([], 'Setoran ke bank dihapus.');
    }

    // ------------------------------------------------------------- lampiran

    public function lampiran(string $penjualanId, string $id)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        return ApiResponse::ok(['items' => $this->berkas($t)]);
    }

    public function unggahLampiran(Request $request, string $penjualanId, string $id)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        $request->validate([
            'berkas' => ['required', 'file', 'max:' . Upload::maxKb()],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('berkas');
        $asli = $file->getClientOriginalName();
        $nama = pathinfo($asli, PATHINFO_FILENAME) . '_' . time() . '.' . ($file->getClientOriginalExtension() ?: 'bin');
        $file->storeAs('public/file_penarikan', $nama);

        $t->file_penarikan()->create([
            'tanggal' => now()->format('Y-m-d'),
            'nama_file' => $nama,
            'nama_dokumen' => $asli,
            'keterangan' => $request->input('keterangan'),
        ]);

        return ApiResponse::ok(['items' => $this->berkas($t->fresh())], 'Bukti setor diunggah.', 201);
    }

    public function hapusLampiran(string $penjualanId, string $id, string $fileId)
    {
        [$p, $t, $gagal] = $this->konteks($penjualanId, $id);
        if ($gagal) {
            return $gagal;
        }

        $f = $t->file_penarikan()->where('file_penarikan_id', $fileId)->first();
        if (is_null($f)) {
            return ApiResponse::fail('Lampiran tidak ditemukan.', 404);
        }

        $path = 'public/file_penarikan/' . $f->nama_file;
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
        $f->delete();

        return ApiResponse::ok(['items' => $this->berkas($t->fresh())], 'Lampiran dihapus.');
    }

    // --------------------------------------------------------------- privat

    private function aturan(): array
    {
        return [
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'member_id' => ['required', 'exists:member,member_id'],
            'nama_bank' => ['required', 'string', 'max:16'],
            'no_referensi' => ['required', 'string', 'max:16'],
            'metode_transfer' => ['required', 'string', 'max:64'],
            'total_uang' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function isi(Request $r): array
    {
        return [
            'member_id' => $r->input('member_id'),
            'tanggal' => $r->input('tanggal'),
            'nama_bank' => $r->input('nama_bank'),
            'no_referensi' => $r->input('no_referensi'),
            'metode_transfer' => $r->input('metode_transfer'),
            'total_uang' => Uang::of($r->input('total_uang')),
            'is_accepted' => false,
        ];
    }

    private function sinkronkanUangKotak(Penjualan $p): void
    {
        $p->update(['total_uang_kotak' => Uang::jumlah($p->penarikan()->get(), 'total_uang')]);
    }

    private function konteks(string $penjualanId, string $id): array
    {
        $p = Penjualan::find($penjualanId);
        if (is_null($p)) {
            return [null, null, ApiResponse::fail('Data setoran tidak ditemukan.', 404)];
        }

        $t = $p->penarikan()->where('penarikan_id', $id)->first();
        if (is_null($t)) {
            return [$p, null, ApiResponse::fail('Catatan setoran ke bank tidak ditemukan.', 404)];
        }

        return [$p, $t, null];
    }

    private function berkas(Penarikan $t): array
    {
        return $t->file_penarikan()->get()->map(function ($f) {
            $ext = strtolower(pathinfo($f->nama_file, PATHINFO_EXTENSION));

            return [
                'id' => $f->file_penarikan_id,
                'nama_dokumen' => $f->nama_dokumen,
                'keterangan' => $f->keterangan,
                'tanggal' => $f->tanggal,
                'url' => asset('storage/file_penarikan/' . $f->nama_file),
                'is_gambar' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'], true),
            ];
        })->values()->all();
    }

    private function ringkas(Penarikan $t): array
    {
        return [
            'id' => $t->penarikan_id,
            'penarikan_id' => $t->penarikan_id,
            'judul' => $t->nama_bank . ' · ' . Uang::of($t->total_uang),
            'subjudul' => $t->tanggal . ' · ' . $t->metode_transfer . ' · ref ' . $t->no_referensi,
            'tanggal' => $t->tanggal,
            'nama_bank' => $t->nama_bank,
            'no_referensi' => $t->no_referensi,
            'metode_transfer' => $t->metode_transfer,
            'total_uang' => Uang::of($t->total_uang),
            'member_id' => $t->member_id,
            'petugas' => optional(optional($t->member()->first())?->ktp()->first())->nama,
            'is_accepted' => filter_var($t->is_accepted, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function lengkap(Penarikan $t): array
    {
        return array_merge($this->ringkas($t), [
            'penjualan_id' => $t->penjualan_id,
            'lampiran' => $this->berkas($t),
        ]);
    }
}
