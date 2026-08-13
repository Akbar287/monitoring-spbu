<?php

namespace App\Http\Controllers\Api\V1\Persediaan;

use App\Http\Controllers\Controller;
use App\Models\Dombak;
use App\Models\FileLo;
use App\Models\Penerimaan;
use App\Support\ApiResponse;
use App\Support\Persediaan;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Penerimaan BBM: pembongkaran muatan LO ke dalam dombak.
 *
 * Untuk tiap alokasi dombak dibuat tiga dokumen stok, mengikuti alur web
 * supaya penerimaan yang dibuat lewat mobile tetap bisa dibaca & dikonfirmasi
 * dari web:
 *   1. Stok Taking Good Receive Penerimaan — selaraskan saldo dengan tinggi
 *      terukur sebelum bongkar
 *   2. Good Receive Penerimaan             — penambahan nyata
 *   3. Stok Taking Good Receive Penyesuaian — penutup (awal = akhir)
 *
 * Catatan: dokumen ke-3 di web menambahkan kembali selisih yang sudah
 * diperbaiki dokumen ke-1 sehingga menggandakan koreksi. Di sini dokumen itu
 * tetap dibuat demi kompatibilitas, tetapi nilainya no-op (stok_awal =
 * stok_akhir) agar saldo tidak terdistorsi.
 */
class PenerimaanController extends Controller
{
    /** LO yang muatannya belum dibongkar. */
    public function index(Request $request)
    {
        $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = FileLo::whereDoesntHave('penerimaan')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereRaw('LOWER(no_lo) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(no_faktur) LIKE ?', [$pola])
                    ->orWhereHas('produk', fn ($p) => $p->whereRaw('LOWER(nama_produk) LIKE ?', [$pola]))
                    ->orWhereHas('pengiriman', fn ($k) => $k->whereRaw('LOWER(no_pol) LIKE ?', [$pola])));
            })
            ->orderBy('tanggal_upload', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (FileLo $lo) => $this->ringkasLo($lo));
    }

    public function showLo(string $id)
    {
        $lo = FileLo::find($id);
        if (is_null($lo)) {
            return ApiResponse::fail('Dokumen LO tidak ditemukan.', 404);
        }

        $pengiriman = $lo->pengiriman()->first();

        return ApiResponse::ok(array_merge($this->ringkasLo($lo), [
            'no_faktur' => $lo->no_faktur,
            'satuan' => $lo->satuan_jumlah,
            'pengiriman' => $pengiriman ? [
                'no_do' => $pengiriman->no_do,
                'no_pol' => $pengiriman->no_pol,
                'tanggal' => $pengiriman->tanggal,
                'supir' => $pengiriman->supir()->get()->pluck('nama_supir')->values()->all(),
            ] : null,
            'segel' => $lo->segel()->get()->pluck('no_segel')->values()->all(),
        ]));
    }

    /**
     * Bongkar muatan LO ke satu atau beberapa dombak.
     *
     * `alokasi[].jumlah` adalah liter yang seharusnya masuk menurut surat
     * jalan; selisihnya terhadap kenaikan tinggi terukur dibukukan sebagai
     * losses "Good Issue Penerimaan".
     */
    public function store(Request $request, string $loId)
    {
        $lo = FileLo::find($loId);
        if (is_null($lo)) {
            return ApiResponse::fail('Dokumen LO tidak ditemukan.', 404);
        }

        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'waktu' => ['nullable', 'date_format:H:i,H:i:s'],
            'alokasi' => ['required', 'array', 'min:1'],
            'alokasi.*.dombak_id' => ['required', 'exists:dombak,dombak_id'],
            'alokasi.*.tinggi_sebelum' => ['required', 'numeric', 'min:0'],
            'alokasi.*.tinggi_sesudah' => ['required', 'numeric', 'min:0'],
            'alokasi.*.jumlah' => ['required', 'numeric', 'min:0'],
        ]);

        $memberId = $request->user()->member()->first()->member_id;

        try {
            $dibuat = DB::transaction(function () use ($request, $lo, $memberId) {
                $tanggal = $request->input('tanggal');
                $jamKerja = $request->input('jam_kerja_id');
                $waktu = $request->input('waktu');
                $hasil = [];

                $tipeTaking = Persediaan::tipe(Persediaan::GERAK_TERIMA_TAKING);
                $tipeTerima = Persediaan::tipe(Persediaan::GERAK_TERIMA);
                $tipePenyesuaian = Persediaan::tipe(Persediaan::GERAK_TERIMA_PENYESUAIAN);

                foreach ($request->input('alokasi') as $baris) {
                    $dombak = Dombak::find($baris['dombak_id']);
                    $inv = Persediaan::inventoryDariDombak($dombak);

                    if (is_null($inv)) {
                        throw new RuntimeException("Dombak {$dombak->nama_dombak} belum terhubung ke produk mana pun.");
                    }

                    $sebelum = Persediaan::volumeDariTinggi($dombak, $baris['tinggi_sebelum']);
                    $sesudah = Persediaan::volumeDariTinggi($dombak, $baris['tinggi_sesudah']);

                    if (Uang::bandingkan($sesudah, $sebelum) <= 0) {
                        throw new RuntimeException(
                            "Tinggi setelah bongkar di dombak {$dombak->nama_dombak} harus lebih tinggi dari sebelumnya."
                        );
                    }

                    $terukur = Uang::kurang($sesudah, $sebelum);
                    $tercatat = Uang::of($baris['jumlah']);
                    // selisih positif = terima lebih banyak dari surat jalan
                    $selisih = Uang::kurang($terukur, $tercatat);

                    // 1 — selaraskan saldo tercatat ke tinggi terukur sebelum bongkar
                    $taking = Persediaan::dokumen($tipeTaking, $inv, $memberId, $dombak->dombak_id, $jamKerja, $tanggal, Persediaan::stokDombak($dombak), $sebelum, $waktu);
                    Persediaan::kaitkanKonversi($taking, Persediaan::konversi($dombak, $baris['tinggi_sebelum']));

                    // 2 — penerimaan nyata
                    $terima = Persediaan::dokumen($tipeTerima, $inv, $memberId, $dombak->dombak_id, $jamKerja, $tanggal, $sebelum, $sesudah, $waktu);
                    Persediaan::kaitkanKonversi($terima, Persediaan::konversi($dombak, $baris['tinggi_sesudah']));
                    Persediaan::catatSelisih($terima, 'Good Issue Penerimaan', $tanggal, $selisih);

                    // 3 — penutup no-op, hanya demi kompatibilitas alur web
                    $penyesuaian = Persediaan::dokumen($tipePenyesuaian, $inv, $memberId, $dombak->dombak_id, $jamKerja, $tanggal, $sesudah, $sesudah, $waktu);

                    $penerimaan = $lo->penerimaan()->where('dokumen_stok_id', $terima->dokumen_stok_id)->first();
                    if (is_null($penerimaan)) {
                        $penerimaan = $lo->penerimaan()->create([
                            'dokumen_stok_id' => $terima->dokumen_stok_id,
                            'tanggal' => $tanggal,
                        ]);
                        $penerimaan->penerimaan_stok_taking()->create(['dokumen_stok_id' => $taking->dokumen_stok_id]);
                        $penerimaan->penerimaan_stok_taking_penyesuaian()->create(['dokumen_stok_id' => $penyesuaian->dokumen_stok_id]);
                    }

                    $hasil[] = $penerimaan;
                }

                return $hasil;
            });
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(
            ['items' => collect($dibuat)->map(fn (Penerimaan $p) => $this->ringkas($p))->values()->all()],
            'Penerimaan dicatat. Saldo berubah setelah dikonfirmasi.',
            201
        );
    }

    /** Penerimaan yang menunggu verifikasi. */
    public function verifikasi(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: false);
    }

    public function riwayat(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: true);
    }

    public function show(string $id)
    {
        $p = Penerimaan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Penerimaan tidak ditemukan.', 404);
        }

        return ApiResponse::ok($this->lengkap($p));
    }

    public function konfirmasi(string $id)
    {
        $p = Penerimaan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Penerimaan tidak ditemukan.', 404);
        }

        try {
            DB::transaction(function () use ($p) {
                // urutan: penyelarasan → penerimaan → penutup
                foreach ($this->dokumenTerurut($p) as $d) {
                    if (! filter_var($d->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
                        Persediaan::terapkan($d);
                    }
                }
            });
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok($this->lengkap($p->fresh()), 'Penerimaan dikonfirmasi dan saldo diperbarui.');
    }

    public function destroy(string $id)
    {
        $p = Penerimaan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Penerimaan tidak ditemukan.', 404);
        }

        if ($this->sudahKonfirmasi($p)) {
            return ApiResponse::fail('Penerimaan yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($p) {
            foreach ($this->dokumenTerurut($p) as $d) {
                Persediaan::hapusDokumen($d);
            }
            $p->delete();
        });

        return ApiResponse::ok([], 'Penerimaan dihapus.');
    }

    // --------------------------------------------------------------- bantuan

    /** @return \Illuminate\Support\Collection<int,\App\Models\DokumenStok> */
    private function dokumenTerurut(Penerimaan $p)
    {
        return collect([
            optional($p->penerimaan_stok_taking()->first())->dokumen_stok()->first(),
            $p->dokumen_stok()->first(),
            optional($p->penerimaan_stok_taking_penyesuaian()->first())->dokumen_stok()->first(),
        ])->filter()->values();
    }

    private function sudahKonfirmasi(Penerimaan $p): bool
    {
        $utama = $p->dokumen_stok()->first();

        return $utama && filter_var($utama->is_confirmation, FILTER_VALIDATE_BOOLEAN);
    }

    private function ringkasLo(FileLo $lo): array
    {
        $produk = $lo->produk()->first();
        $pengiriman = $lo->pengiriman()->first();

        return [
            'id' => $lo->file_lo_id,
            'file_lo_id' => $lo->file_lo_id,
            'judul' => 'LO ' . $lo->no_lo,
            'subjudul' => (optional($produk)->nama_produk ?? '-') . ' · ' . Uang::of($lo->jumlah) . ' ' . $lo->satuan_jumlah,
            'no_lo' => $lo->no_lo,
            'produk' => optional($produk)->nama_produk,
            'produk_id' => optional($produk)->produk_id,
            'jumlah' => Uang::of($lo->jumlah),
            'tanggal_upload' => $lo->tanggal_upload,
            'no_pol' => optional($pengiriman)->no_pol,
        ];
    }

    private function ringkas(Penerimaan $p): array
    {
        $d = $p->dokumen_stok()->first();
        $lo = $p->file_lo()->first();

        return [
            'id' => $p->penerimaan_id,
            'penerimaan_id' => $p->penerimaan_id,
            'judul' => 'LO ' . optional($lo)->no_lo . ' → ' . (Persediaan::namaDombak($d) ?? '-'),
            'subjudul' => $p->tanggal . ' · ' . ($d ? Uang::kurang($d->stok_akhir, $d->stok_awal) : '0') . ' L',
            'tanggal' => $p->tanggal,
            'no_lo' => optional($lo)->no_lo,
            'dombak' => Persediaan::namaDombak($d),
            'diterima' => $d ? Uang::kurang($d->stok_akhir, $d->stok_awal) : '0.000000',
            'is_confirmation' => $this->sudahKonfirmasi($p),
        ];
    }

    private function lengkap(Penerimaan $p): array
    {
        return array_merge($this->ringkas($p), [
            'lo' => optional($p->file_lo()->first()) ? $this->ringkasLo($p->file_lo()->first()) : null,
            'dokumen' => $this->dokumenTerurut($p)
                ->map(fn ($d) => Persediaan::serialDokumen($d, lengkap: true))
                ->values()->all(),
        ]);
    }

    private function daftar(Request $request, bool $terkonfirmasi)
    {
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = Penerimaan::whereHas('dokumen_stok', fn ($q) => $q->where('is_confirmation', $terkonfirmasi ? 1 : 0))
            ->when($request->filled('dari'), fn ($q) => $q->where('tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereHas('file_lo', fn ($l) => $l->whereRaw('LOWER(no_lo) LIKE ?', [$pola]))
                    ->orWhereHas('dokumen_stok.dombak', fn ($d) => $d->whereRaw('LOWER(nama_dombak) LIKE ?', [$pola])));
            })
            ->orderBy('tanggal', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, fn (Penerimaan $p) => $this->ringkas($p));
    }
}
