<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Fasilitas;
use App\Support\ApiResponse;
use App\Support\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Berkas/foto lampiran untuk master Aset dan Fasilitas.
 *
 * Kedua tabel lampiran (`file_aset`, `file_fasilitas`) berbentuk identik:
 * nama_file (nama tersimpan) + nama_dokumen (nama asli) + tanggal_upload,
 * dengan berkas fisik di storage/app/public/<folder>. Satu controller
 * melayani keduanya lewat segmen route `{jenis}`.
 */
class LampiranController extends Controller
{
    private const JENIS = [
        'aset' => [
            'model' => Aset::class,
            'relasi' => 'file_aset',
            'kunci' => 'file_aset_id',
            'induk' => 'aset_id',
            'folder' => 'aset',
            'label' => 'Aset',
        ],
        'fasilitas' => [
            'model' => Fasilitas::class,
            'relasi' => 'file_fasilitas',
            'kunci' => 'file_fasilitas_id',
            'induk' => 'fasilitas_id',
            'folder' => 'fasilitas',
            'label' => 'Fasilitas',
        ],
    ];

    public function index(string $jenis, string $id)
    {
        [$konf, $induk, $gagal] = $this->konteks($jenis, $id);
        if ($gagal) {
            return $gagal;
        }

        $items = $induk->{$konf['relasi']}()->orderBy('tanggal_upload', 'desc')->get()
            ->map(fn (Model $f) => $this->serialize($f, $konf))
            ->values()->all();

        return ApiResponse::ok(['items' => $items]);
    }

    public function store(Request $request, string $jenis, string $id)
    {
        [$konf, $induk, $gagal] = $this->konteks($jenis, $id);
        if ($gagal) {
            return $gagal;
        }

        $request->validate([
            'berkas' => ['required', 'file', 'max:' . Upload::maxKb()],
            'nama_dokumen' => ['nullable', 'string', 'max:120'],
        ]);

        $file = $request->file('berkas');
        $asli = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension() ?: 'bin';
        // pola nama mengikuti konvensi web: <nama>_<timestamp>.<ext>
        $nama = pathinfo($asli, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
        $file->storeAs('public/' . $konf['folder'], $nama);

        $lampiran = $induk->{$konf['relasi']}()->create([
            'nama_file' => $nama,
            'nama_dokumen' => $request->input('nama_dokumen') ?: $asli,
            'tanggal_upload' => now()->format('Y-m-d'),
        ]);

        return ApiResponse::ok($this->serialize($lampiran, $konf), 'Lampiran berhasil diunggah.', 201);
    }

    public function destroy(string $jenis, string $id, string $lampiranId)
    {
        [$konf, $induk, $gagal] = $this->konteks($jenis, $id);
        if ($gagal) {
            return $gagal;
        }

        $lampiran = $induk->{$konf['relasi']}()->where($konf['kunci'], $lampiranId)->first();
        if (! $lampiran) {
            return ApiResponse::fail('Lampiran tidak ditemukan.', 404);
        }

        $path = 'public/' . $konf['folder'] . '/' . $lampiran->nama_file;
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
        $lampiran->delete();

        return ApiResponse::ok([], 'Lampiran dihapus.');
    }

    // --------------------------------------------------------------- bantuan

    private function konteks(string $jenis, string $id): array
    {
        $konf = self::JENIS[$jenis] ?? null;
        if (! $konf) {
            return [null, null, ApiResponse::fail('Jenis lampiran tidak dikenal.', 404)];
        }

        $induk = $konf['model']::find($id);
        if (! $induk) {
            return [$konf, null, ApiResponse::fail($konf['label'] . ' tidak ditemukan.', 404)];
        }

        return [$konf, $induk, null];
    }

    private function serialize(Model $f, array $konf): array
    {
        $ext = strtolower(pathinfo($f->nama_file, PATHINFO_EXTENSION));

        return [
            'id' => $f->{$konf['kunci']},
            'nama_file' => $f->nama_file,
            'nama_dokumen' => $f->nama_dokumen,
            'tanggal_upload' => $f->tanggal_upload,
            'url' => asset('storage/' . $konf['folder'] . '/' . $f->nama_file),
            'is_gambar' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'], true),
        ];
    }
}
