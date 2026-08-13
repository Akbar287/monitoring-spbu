<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kerangka CRUD untuk resource sederhana di API mobile — dipakai modul
 * Master Data (Api\V1\Master) dan Konfigurasi (Api\V1\Konfigurasi).
 *
 * Subkelas cukup mendeklarasikan model, kolom pencarian, urutan, aturan
 * validasi, pemetaan payload, dan bentuk serialisasi. Perilaku index/show/
 * store/update/destroy diwarisi dari sini supaya seluruh resource berperilaku
 * identik di sisi klien.
 *
 * Semua route yang memakai controller ini dijaga `level:Penanggung Jawab`
 * (lihat routes/mobile.php).
 */
abstract class CrudController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    abstract protected function label(): string;

    /** Bentuk ringkas untuk daftar. */
    abstract protected function ringkas(Model $m): array;

    /** Aturan validasi; $m terisi saat update. */
    abstract protected function aturan(Request $r, ?Model $m): array;

    /** Atribut yang disimpan ke tabel utama. */
    abstract protected function isi(Request $r): array;

    /** Bentuk lengkap untuk halaman detail. */
    protected function lengkap(Model $m): array
    {
        return $this->ringkas($m);
    }

    /** Kolom teks yang bisa dicari. */
    protected function kolomCari(): array
    {
        return [];
    }

    /** [kolom, arah] — banyak tabel master tidak punya timestamps. */
    protected function urutan(): array
    {
        return ['created_at', 'desc'];
    }

    protected function query(): Builder
    {
        return $this->model()::query();
    }

    /** Pencarian default: LIKE case-insensitive pada kolomCari(). */
    protected function terapkanCari(Builder $query, string $cari): void
    {
        $kolom = $this->kolomCari();
        if (! $kolom) {
            return;
        }

        $query->where(function (Builder $q) use ($kolom, $cari) {
            foreach ($kolom as $k) {
                $q->orWhereRaw('LOWER(' . $k . ') LIKE ?', ['%' . mb_strtolower($cari) . '%']);
            }
        });
    }

    /**
     * Relasi yang menghalangi penghapusan, dalam bentuk ['label' => jumlah].
     * Penting karena hampir semua FK memakai cascadeOnDelete: menghapus satu
     * produk bisa merontokkan harga, nozzle, stok, sampai detail penjualan.
     */
    protected function penghalangHapus(Model $m): array
    {
        return [];
    }

    /** Dijalankan setelah create/update dalam transaksi yang sama. */
    protected function setelahSimpan(Request $r, Model $m, bool $baru): void
    {
        //
    }

    // ------------------------------------------------------------------ aksi

    public function index(Request $request)
    {
        $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'spbu_id' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $this->query();

        if ($cari = trim((string) $request->input('cari'))) {
            $this->terapkanCari($query, $cari);
        }

        if ($request->filled('spbu_id') && $this->punyaKolom('spbu_id')) {
            $query->where('spbu_id', $request->input('spbu_id'));
        }

        [$kolomUrut, $arah] = $this->urutan();
        $paginator = $query->orderBy($kolomUrut, $arah)->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (Model $m) => $this->ringkas($m));
    }

    public function show(string $id)
    {
        $m = $this->temukan($id);

        return $m
            ? ApiResponse::ok($this->lengkap($m))
            : ApiResponse::fail($this->label() . ' tidak ditemukan.', 404);
    }

    public function store(Request $request)
    {
        $request->validate($this->aturan($request, null));

        $m = DB::transaction(function () use ($request) {
            $model = $this->model();
            /** @var Model $m */
            $m = new $model();
            $m->fill($this->isi($request));
            $m->save();
            $this->setelahSimpan($request, $m, true);

            return $m;
        });

        return ApiResponse::ok($this->lengkap($m->fresh()), $this->label() . ' berhasil ditambahkan.', 201);
    }

    public function update(Request $request, string $id)
    {
        $m = $this->temukan($id);
        if (! $m) {
            return ApiResponse::fail($this->label() . ' tidak ditemukan.', 404);
        }

        $request->validate($this->aturan($request, $m));

        DB::transaction(function () use ($request, $m) {
            $m->fill($this->isi($request));
            $m->save();
            $this->setelahSimpan($request, $m, false);
        });

        return ApiResponse::ok($this->lengkap($m->fresh()), $this->label() . ' berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $m = $this->temukan($id);
        if (! $m) {
            return ApiResponse::fail($this->label() . ' tidak ditemukan.', 404);
        }

        $penghalang = array_filter($this->penghalangHapus($m));
        if ($penghalang) {
            $rincian = collect($penghalang)->map(fn ($n, $label) => "$n $label")->values()->implode(', ');

            return ApiResponse::fail(
                $this->label() . ' tidak bisa dihapus karena masih dipakai: ' . $rincian . '.',
                422,
                ['penghalang' => $penghalang]
            );
        }

        $m->delete();

        return ApiResponse::ok([], $this->label() . ' berhasil dihapus.');
    }

    // --------------------------------------------------------------- bantuan

    protected function temukan(string $id): ?Model
    {
        return $this->model()::find($id);
    }

    protected function punyaKolom(string $kolom): bool
    {
        $model = $this->model();

        return in_array($kolom, (new $model())->getFillable(), true);
    }

    /** Terima "12,500" maupun 12500 dari klien. */
    protected static function angka(mixed $nilai): float
    {
        return (float) str_replace(',', '', (string) $nilai);
    }

    protected static function bool(Request $r, string $key, bool $default = false): int
    {
        return $r->has($key) ? ($r->boolean($key) ? 1 : 0) : ($default ? 1 : 0);
    }
}
