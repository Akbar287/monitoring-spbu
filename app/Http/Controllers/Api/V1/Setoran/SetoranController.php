<?php

namespace App\Http\Controllers\Api\V1\Setoran;

use App\Http\Controllers\Controller;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\StatusSetoran;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Modul Setoran: satu record `penjualan` per shift yang berjalan melewati
 * lima tahap `status_setoran`.
 *
 *   Open Penjualan → Penitipan Setoran → Closing Penjualan → Setoran Ke Bank → Selesai
 *
 * Satu daftar generik melayani seluruh tahap (parameter `tahap`), lengkap
 * dengan pencarian, filter, dan paginasi seperti halaman web.
 *
 * PENTING: `penjualan.is_accepted_admin` + `is_accepted_top` adalah pemicu
 * posting jurnal otomatis (App\Support\PostingKeuangan) — mengubahnya membuat
 * atau membatalkan jurnal penjualan & HPP.
 */
class SetoranController extends Controller
{
    public const TAHAP = [
        'open' => 'Open Penjualan',
        'penitipan' => 'Penitipan Setoran',
        'closing' => 'Closing Penjualan',
        'bank' => 'Setoran Ke Bank',
        'selesai' => 'Selesai',
    ];

    public function ringkasan(Request $request)
    {
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());
        $bulanIni = Carbon::now()->startOfMonth()->format('Y-m-d');
        $hariIni = Carbon::now()->format('Y-m-d');

        $per = [];
        foreach (self::TAHAP as $kunci => $nama) {
            $status = StatusSetoran::where('nama_status', $nama)->first();
            $per[$kunci] = [
                'label' => $nama,
                'jumlah' => $status
                    ? Penjualan::where('status_setoran_id', $status->status_setoran_id)
                        ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))->count()
                    : 0,
            ];
        }

        $bulan = Penjualan::whereBetween('tanggal', [$bulanIni, $hariIni])
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))->get();

        return ApiResponse::ok([
            'spbu' => $spbu ? $spbu->nama_spbu : 'Semua SPBU',
            'tahap' => $per,
            'bulan_ini' => [
                'total_penjualan' => Uang::jumlah($bulan, 'total_penjualan'),
                'total_uang_kotak' => Uang::jumlah($bulan, 'total_uang_kotak'),
                'selisih' => Uang::kurang(
                    Uang::jumlah($bulan, 'total_penjualan'),
                    Uang::jumlah($bulan, 'total_uang_kotak')
                ),
                'jumlah_shift' => $bulan->count(),
            ],
            'menunggu_persetujuan' => Penjualan::when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
                ->where(fn ($q) => $q->where('is_accepted_admin', false)->orWhere('is_accepted_top', false))
                ->count(),
        ]);
    }

    /** Daftar setoran per tahap — pencarian, filter lengkap, dan paginasi. */
    public function index(Request $request)
    {
        $request->validate([
            'tahap' => ['nullable', 'in:semua,open,penitipan,closing,bank,selesai'],
            'cari' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'spbu_id' => ['nullable', 'exists:spbu,spbu_id'],
            'jam_kerja_id' => ['nullable', 'exists:jam_kerja,jam_kerja_id'],
            'diterima_admin' => ['nullable', 'boolean'],
            'diterima_top' => ['nullable', 'boolean'],
            'nama_bank' => ['nullable', 'string', 'max:32'],
            'metode_transfer' => ['nullable', 'string', 'max:64'],
            'no_referensi' => ['nullable', 'string', 'max:32'],
            'member_id' => ['nullable', 'exists:member,member_id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        // rentang default = bulan berjalan, sama seperti halaman web
        [$dari, $sampai] = $this->rentang($request);

        $query = Penjualan::query()->whereBetween('tanggal', [$dari, $sampai]);

        $tahap = $request->input('tahap', 'semua');
        if ($tahap !== 'semua') {
            $status = StatusSetoran::where('nama_status', self::TAHAP[$tahap])->first();
            $query->where('status_setoran_id', optional($status)->status_setoran_id ?? '-');
        }

        $query->when($request->filled('spbu_id'), fn ($q) => $q->where('spbu_id', $request->input('spbu_id')))
            ->when($request->filled('jam_kerja_id'), fn ($q) => $q->where('jam_kerja_id', $request->input('jam_kerja_id')))
            ->when($request->has('diterima_admin'), fn ($q) => $q->where('is_accepted_admin', $request->boolean('diterima_admin')))
            ->when($request->has('diterima_top'), fn ($q) => $q->where('is_accepted_top', $request->boolean('diterima_top')));

        // filter khusus tahap Setoran Ke Bank — menyaring lewat penarikan
        foreach (['nama_bank', 'metode_transfer', 'no_referensi'] as $kolom) {
            $query->when($request->filled($kolom), fn ($q) => $q->whereHas(
                'penarikan',
                fn ($p) => $p->whereRaw('LOWER(' . $kolom . ') LIKE ?', ['%' . mb_strtolower($request->input($kolom)) . '%'])
            ));
        }
        $query->when($request->filled('member_id'), fn ($q) => $q->whereHas(
            'penarikan',
            fn ($p) => $p->where('member_id', $request->input('member_id'))
        ));

        if ($cari = trim((string) $request->input('cari'))) {
            $this->cari($query, $cari);
        }

        $paginator = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::paginated($paginator, fn (Penjualan $p) => $this->ringkas($p));
    }

    public function show(string $id)
    {
        $p = Penjualan::find($id);

        return $p
            ? ApiResponse::ok($this->lengkap($p))
            : ApiResponse::fail('Data setoran tidak ditemukan.', 404);
    }

    /** Pindahkan ke tahap berikutnya. */
    public function maju(string $id)
    {
        return $this->geser($id, +1);
    }

    /** Kembalikan ke tahap sebelumnya. */
    public function mundur(string $id)
    {
        return $this->geser($id, -1);
    }

    public function destroy(string $id)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        if (filter_var($p->is_accepted_admin, FILTER_VALIDATE_BOOLEAN)
            || filter_var($p->is_accepted_top, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail(
                'Setoran yang sudah disetujui tidak bisa dihapus karena jurnal keuangannya sudah terbentuk.',
                422
            );
        }

        if ($p->penarikan()->count() > 0) {
            return ApiResponse::fail('Hapus dulu seluruh catatan setoran ke bank pada shift ini.', 422);
        }

        $p->delete(); // detail_penjualan & pj ikut terhapus (cascade)

        return ApiResponse::ok([], 'Setoran dihapus.');
    }

    // ------------------------------------------------- konfirmasi per detail

    public function konfirmasiDetail(string $id, string $detailId, Request $request)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $detail = $p->detail_penjualan()->where('detail_penjualan_id', $detailId)->first();
        if (is_null($detail)) {
            return ApiResponse::fail('Baris stand meter tidak ditemukan pada setoran ini.', 404);
        }

        $detail->update(['is_confirmed' => $request->boolean('konfirmasi', true) ? 1 : 0]);

        return ApiResponse::ok($this->lengkap($p->fresh()), 'Status baris stand meter diperbarui.');
    }

    // ---------------------------------------------------- persetujuan akhir

    /**
     * Persetujuan admin / top manajemen.
     *
     * Ketika keduanya bernilai true, model event Penjualan memicu posting
     * jurnal penjualan + HPP; mencabut salah satunya membatalkan jurnalnya.
     */
    public function persetujuan(Request $request, string $id)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $request->validate([
            'peran' => ['required', 'in:admin,top'],
            'setuju' => ['required', 'boolean'],
        ]);

        $kolom = $request->input('peran') === 'admin' ? 'is_accepted_admin' : 'is_accepted_top';
        $setuju = $request->boolean('setuju');

        try {
            $p->update([$kolom => $setuju ? 1 : 0]);
        } catch (\RuntimeException $e) {
            // JurnalPosting menolak bila periode akuntansi sudah ditutup
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(
            $this->lengkap($p->fresh()),
            $setuju ? 'Setoran disetujui.' : 'Persetujuan dicabut.'
        );
    }

    /** Tandai selesai (tahap terakhir). */
    public function selesai(string $id)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $status = StatusSetoran::where('nama_status', 'Selesai')->first();
        if (is_null($status)) {
            return ApiResponse::fail('Status "Selesai" belum ada di tabel status_setoran.', 422);
        }

        $p->update(['status_setoran_id' => $status->status_setoran_id]);

        return ApiResponse::ok($this->lengkap($p->fresh()), 'Setoran ditandai selesai.');
    }

    // --------------------------------------------------------------- privat

    private function geser(string $id, int $arah)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $urutan = array_values(self::TAHAP);
        $sekarang = optional($p->status_setoran()->first())->nama_status;
        $posisi = array_search($sekarang, $urutan, true);

        if ($posisi === false) {
            return ApiResponse::fail('Status setoran saat ini tidak dikenal: ' . $sekarang, 422);
        }

        $tujuan = $posisi + $arah;
        if ($tujuan < 0 || $tujuan >= count($urutan)) {
            return ApiResponse::fail(
                $arah > 0 ? 'Sudah berada di tahap terakhir.' : 'Sudah berada di tahap pertama.',
                422
            );
        }

        $status = StatusSetoran::where('nama_status', $urutan[$tujuan])->first();
        $p->update(['status_setoran_id' => $status->status_setoran_id]);

        return ApiResponse::ok($this->lengkap($p->fresh()), 'Dipindahkan ke tahap ' . $urutan[$tujuan] . '.');
    }

    private function rentang(Request $request): array
    {
        $dari = $request->filled('dari')
            ? Carbon::createFromFormat('Y-m-d', $request->input('dari'))
            : Carbon::now()->startOfMonth();
        $sampai = $request->filled('sampai')
            ? Carbon::createFromFormat('Y-m-d', $request->input('sampai'))
            : Carbon::now()->endOfMonth();

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari->format('Y-m-d'), $sampai->format('Y-m-d')];
    }

    /** Pencarian bebas: tanggal, shift, SPBU, nozzle, bank, no. referensi. */
    private function cari(Builder $query, string $cari): void
    {
        $pola = '%' . mb_strtolower($cari) . '%';

        $query->where(function (Builder $q) use ($pola) {
            $q->whereRaw('LOWER(CAST(tanggal AS TEXT)) LIKE ?', [$pola])
                ->orWhereHas('jam_kerja', fn ($j) => $j->whereRaw('LOWER(nama_jam_kerja) LIKE ?', [$pola]))
                ->orWhereHas('spbu', fn ($s) => $s->whereRaw('LOWER(nama_spbu) LIKE ?', [$pola]))
                ->orWhereHas('penarikan', fn ($p) => $p
                    ->whereRaw('LOWER(nama_bank) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(no_referensi) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(metode_transfer) LIKE ?', [$pola]))
                ->orWhereHas('detail_penjualan.stand_meter.nozzle', fn ($n) => $n
                    ->whereRaw('LOWER(nama_nozzle) LIKE ?', [$pola]));
        });
    }

    private function ringkas(Penjualan $p): array
    {
        $nozzle = $p->detail_penjualan()->get()
            ->map(fn (DetailPenjualan $d) => optional(optional($d->stand_meter()->first())?->nozzle()->first())->nama_nozzle)
            ->filter()->unique()->values();

        $status = optional($p->status_setoran()->first())->nama_status;

        return [
            'id' => $p->penjualan_id,
            'penjualan_id' => $p->penjualan_id,
            'judul' => $p->tanggal . ' · ' . (optional($p->jam_kerja()->first())->nama_jam_kerja ?? '-'),
            'subjudul' => $status . ' · ' . ($nozzle->isEmpty() ? 'tanpa nozzle' : $nozzle->implode(', ')),
            'tanggal' => $p->tanggal,
            'jam_kerja' => optional($p->jam_kerja()->first())->nama_jam_kerja,
            'status' => $status,
            'tahap' => array_search($status, self::TAHAP, true) ?: null,
            'total_penjualan' => Uang::of($p->total_penjualan),
            'total_uang_kotak' => Uang::of($p->total_uang_kotak),
            'selisih' => Uang::kurang($p->total_penjualan, $p->total_uang_kotak),
            'nozzle' => $nozzle->all(),
            'is_accepted_admin' => filter_var($p->is_accepted_admin, FILTER_VALIDATE_BOOLEAN),
            'is_accepted_top' => filter_var($p->is_accepted_top, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function lengkap(Penjualan $p): array
    {
        $detail = $p->detail_penjualan()->get()->map(function (DetailPenjualan $d) {
            $sm = $d->stand_meter()->first();
            $nozzle = $sm?->nozzle()->first();
            $harga = $d->harga()->first();

            return [
                'detail_penjualan_id' => $d->detail_penjualan_id,
                'nozzle' => optional($nozzle)->nama_nozzle,
                'produk' => optional(optional($nozzle)->produk()->first())->nama_produk,
                'stand_meter_awal' => $sm ? Uang::of($sm->stand_meter_awal) : null,
                'stand_meter_akhir' => $sm ? Uang::of($sm->stand_meter_akhir) : null,
                'liter' => Uang::of($d->liter),
                'harga_jual' => $harga ? Uang::of($harga->harga_jual) : null,
                'total' => Uang::of($d->total_detail_jual),
                'is_confirmed' => filter_var($d->is_confirmed, FILTER_VALIDATE_BOOLEAN),
            ];
        })->values()->all();

        $pj = $p->pj_operasional_setoran()->get()
            ->groupBy('jabatan_id')
            ->map(fn ($grup) => [
                'jabatan' => optional($grup->first()->jabatan()->first())->nama_jabatan,
                'petugas' => $grup->map(fn ($x) => optional(optional($x->member()->first())?->ktp()->first())->nama)
                    ->filter()->values()->all(),
            ])->values()->all();

        $penarikan = $p->penarikan()->orderBy('tanggal', 'desc')->get()->map(fn ($t) => [
            'penarikan_id' => $t->penarikan_id,
            'tanggal' => $t->tanggal,
            'nama_bank' => $t->nama_bank,
            'no_referensi' => $t->no_referensi,
            'metode_transfer' => $t->metode_transfer,
            'total_uang' => Uang::of($t->total_uang),
            'is_accepted' => filter_var($t->is_accepted, FILTER_VALIDATE_BOOLEAN),
            'petugas' => optional(optional($t->member()->first())?->ktp()->first())->nama,
            'jumlah_lampiran' => $t->file_penarikan()->count(),
        ])->values()->all();

        return array_merge($this->ringkas($p), [
            'spbu' => optional($p->spbu()->first())->nama_spbu,
            'spbu_id' => $p->spbu_id,
            'jam_kerja_id' => $p->jam_kerja_id,
            'detail' => $detail,
            'detail_terkonfirmasi' => collect($detail)->where('is_confirmed', true)->count(),
            'pj' => $pj,
            'penarikan' => $penarikan,
            'total_disetor' => Uang::jumlah($penarikan, 'total_uang'),
        ]);
    }
}
