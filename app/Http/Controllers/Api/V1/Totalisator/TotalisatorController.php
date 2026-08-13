<?php

namespace App\Http\Controllers\Api\V1\Totalisator;

use App\Http\Controllers\Controller;
use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Nozzle;
use App\Models\StandMeter;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Persediaan;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Totalisator (tabel `stand_meter`): angka meter tiap nozzle per shift.
 *
 * Alurnya tiga langkah:
 *   1. Buka shift  — stand_meter_awal diambil otomatis dari stand_meter_akhir
 *                    shift sebelumnya pada nozzle yang sama
 *   2. Isi akhir   — angka meter saat shift ditutup
 *   3. Konfirmasi  — liter terjual (akhir − awal) memotong saldo dombak dan
 *                    membuat dokumen stok "Good Issue Sales"
 *
 * Semua angka meter decimal(16,6) dan dikirim sebagai string 6 desimal.
 */
class TotalisatorController extends Controller
{
    public function ringkasan(Request $request)
    {
        $hariIni = Carbon::now()->format('Y-m-d');

        $terbuka = StandMeter::where('is_confirmation', 0)->get();
        $hariIniSemua = StandMeter::where('tanggal', $hariIni)->get();

        return ApiResponse::ok([
            'tanggal' => $hariIni,
            'shift_terbuka' => $terbuka->count(),
            'belum_diisi_akhir' => $terbuka->filter(fn ($s) => Uang::nol($s->stand_meter_akhir))->count(),
            'siap_konfirmasi' => $terbuka->filter(fn ($s) => ! Uang::nol($s->stand_meter_akhir))->count(),
            'liter_hari_ini' => Uang::jumlah(
                $hariIniSemua->filter(fn ($s) => ! Uang::nol($s->stand_meter_akhir))
                    ->map(fn ($s) => ['liter' => Uang::kurang($s->stand_meter_akhir, $s->stand_meter_awal)]),
                'liter'
            ),
            'dikonfirmasi_hari_ini' => $hariIniSemua->filter(
                fn ($s) => filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN)
            )->count(),
        ]);
    }

    // ------------------------------------------------------------- daftar

    public function index(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: false);
    }

    public function riwayat(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: true);
    }

    public function show(string $id)
    {
        $s = StandMeter::find($id);

        return $s
            ? ApiResponse::ok($this->lengkap($s))
            : ApiResponse::fail('Data totalisator tidak ditemukan.', 404);
    }

    // ------------------------------------------------------------- bantuan input

    /** Nozzle yang melayani sebuah dombak (lewat payung → dispenser). */
    public function nozzle(string $dombakId)
    {
        $dombak = Dombak::find($dombakId);
        if (is_null($dombak)) {
            return ApiResponse::fail('Dombak tidak ditemukan.', 404);
        }

        $payungIds = $dombak->payung()->pluck('payung.payung_id')->all();

        $items = Nozzle::where('is_aktif', true)
            ->whereHas('dispenser', fn ($q) => $q->whereIn('payung_id', $payungIds))
            ->orderBy('nama_nozzle')
            ->get()
            ->map(fn (Nozzle $n) => [
                'value' => $n->nozzle_id,
                'label' => $n->nama_nozzle . ' — ' . (optional($n->produk()->first())->nama_produk ?? '-'),
                'nozzle_id' => $n->nozzle_id,
                'nama_nozzle' => $n->nama_nozzle,
                'produk' => optional($n->produk()->first())->nama_produk,
            ])->values()->all();

        return ApiResponse::ok(['dombak' => $dombak->nama_dombak, 'items' => $items]);
    }

    /**
     * Saran isian untuk membuka shift baru pada sebuah nozzle: tanggal, shift
     * berikutnya, dan angka meter awal yang terbawa dari shift sebelumnya.
     */
    public function saran(string $nozzleId)
    {
        $nozzle = Nozzle::find($nozzleId);
        if (is_null($nozzle)) {
            return ApiResponse::fail('Nozzle tidak ditemukan.', 404);
        }

        $terakhir = $this->terakhir($nozzle);
        $jumlahShift = JamKerja::count();

        if (is_null($terakhir)) {
            $jamKerja = JamKerja::orderBy('urutan')->first();

            return ApiResponse::ok([
                'tanggal' => Carbon::now()->format('Y-m-d'),
                'jam_kerja_id' => optional($jamKerja)->jam_kerja_id,
                'jam_kerja' => optional($jamKerja)->nama_jam_kerja,
                'stand_meter_awal' => '0.000000',
                'awal_terkunci' => false,
                'catatan' => 'Belum ada riwayat untuk nozzle ini — angka awal diisi manual.',
            ]);
        }

        $urutanTerakhir = (int) optional($terakhir->jam_kerja()->first())->urutan;
        $habis = $urutanTerakhir >= $jumlahShift;
        $urutanBerikut = $habis ? 1 : $urutanTerakhir + 1;
        $jamKerja = JamKerja::where('urutan', $urutanBerikut)->first();
        $tanggal = Carbon::parse($terakhir->tanggal);

        // shift terakhir dalam sehari -> lanjut ke hari berikutnya
        $akhir = Uang::of($terakhir->stand_meter_akhir);
        $belumDitutup = Uang::nol($akhir);

        return ApiResponse::ok([
            'tanggal' => ($habis ? $tanggal->addDay() : $tanggal)->format('Y-m-d'),
            'jam_kerja_id' => optional($jamKerja)->jam_kerja_id,
            'jam_kerja' => optional($jamKerja)->nama_jam_kerja,
            'stand_meter_awal' => $belumDitutup ? '0.000000' : $akhir,
            // angka awal dikunci bila shift sebelumnya sudah punya angka akhir
            'awal_terkunci' => ! $belumDitutup,
            'catatan' => $belumDitutup
                ? 'Shift sebelumnya belum diisi angka akhir — angka awal diisi manual.'
                : 'Angka awal terbawa otomatis dari angka akhir shift sebelumnya.',
            'sebelumnya' => [
                'tanggal' => $terakhir->tanggal,
                'jam_kerja' => optional($terakhir->jam_kerja()->first())->nama_jam_kerja,
                'stand_meter_akhir' => $akhir,
                'dombak_id' => $terakhir->dombak_id,
            ],
        ]);
    }

    // ------------------------------------------------------------- tulis

    /** Buka shift baru untuk satu nozzle. */
    public function store(Request $request)
    {
        $request->validate([
            'nozzle_id' => ['required', 'exists:nozzle,nozzle_id'],
            'dombak_id' => ['required', 'exists:dombak,dombak_id'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'stand_meter_awal' => ['required', 'numeric', 'min:0'],
        ]);

        $nozzle = Nozzle::find($request->input('nozzle_id'));
        $jamKerja = JamKerja::find($request->input('jam_kerja_id'));
        $awal = Uang::of($request->input('stand_meter_awal'));

        $duplikat = StandMeter::where('nozzle_id', $nozzle->nozzle_id)
            ->where('tanggal', $request->input('tanggal'))
            ->where('jam_kerja_id', $jamKerja->jam_kerja_id)
            ->first();

        if ($duplikat) {
            return ApiResponse::fail(
                'Nozzle ini sudah punya catatan totalisator pada tanggal dan shift tersebut.',
                422,
                ['stand_meter_id' => $duplikat->stand_meter_id]
            );
        }

        $s = StandMeter::create([
            'nozzle_id' => $nozzle->nozzle_id,
            'dombak_id' => $request->input('dombak_id'),
            'jam_kerja_id' => $jamKerja->jam_kerja_id,
            'tanggal' => $request->input('tanggal'),
            'stand_meter_awal' => $awal,
            'stand_meter_akhir' => 0,
            'urutan' => $jamKerja->urutan,
            'is_confirmation' => 0,
        ]);

        return ApiResponse::ok($this->lengkap($s), 'Shift totalisator dibuka.', 201);
    }

    /** Isi / ubah angka meter akhir. */
    public function akhir(Request $request, string $id)
    {
        $s = StandMeter::find($id);
        if (is_null($s)) {
            return ApiResponse::fail('Data totalisator tidak ditemukan.', 404);
        }

        if (filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Data yang sudah dikonfirmasi tidak bisa diubah.', 422);
        }

        $request->validate(['stand_meter_akhir' => ['required', 'numeric', 'min:0']]);
        $akhir = Uang::of($request->input('stand_meter_akhir'));

        if (Uang::bandingkan($akhir, $s->stand_meter_awal) < 0) {
            return ApiResponse::fail(
                'Angka akhir tidak boleh lebih kecil dari angka awal (' . Uang::of($s->stand_meter_awal) . ').',
                422
            );
        }

        $s->update(['stand_meter_akhir' => $akhir]);

        return ApiResponse::ok($this->lengkap($s->fresh()), 'Angka akhir disimpan.');
    }

    public function ubah(Request $request, string $id)
    {
        $s = StandMeter::find($id);
        if (is_null($s)) {
            return ApiResponse::fail('Data totalisator tidak ditemukan.', 404);
        }
        if (filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Data yang sudah dikonfirmasi tidak bisa diubah.', 422);
        }

        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'stand_meter_awal' => ['required', 'numeric', 'min:0'],
            'stand_meter_akhir' => ['nullable', 'numeric', 'min:0'],
        ]);

        $jamKerja = JamKerja::find($request->input('jam_kerja_id'));

        $s->update([
            'tanggal' => $request->input('tanggal'),
            'jam_kerja_id' => $jamKerja->jam_kerja_id,
            'urutan' => $jamKerja->urutan,
            'stand_meter_awal' => Uang::of($request->input('stand_meter_awal')),
            'stand_meter_akhir' => Uang::of($request->input('stand_meter_akhir', 0)),
        ]);

        return ApiResponse::ok($this->lengkap($s->fresh()), 'Data totalisator diperbarui.');
    }

    /**
     * Konfirmasi: liter terjual memotong saldo dombak dan membuat dokumen
     * "Good Issue Sales".
     *
     * Formula liter terjual mengikuti StandMeterController web apa adanya —
     * termasuk penyesuaian ketika ada penerimaan BBM pada shift yang sama —
     * karena angka ini menjadi dasar penjualan dan HPP.
     */
    public function konfirmasi(Request $request, string $id)
    {
        $s = StandMeter::find($id);
        if (is_null($s)) {
            return ApiResponse::fail('Data totalisator tidak ditemukan.', 404);
        }
        if (filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::ok($this->lengkap($s), 'Data ini sudah dikonfirmasi.');
        }
        if (Uang::nol($s->stand_meter_akhir)) {
            return ApiResponse::fail('Isi angka meter akhir lebih dulu sebelum konfirmasi.', 422);
        }

        $memberId = $request->user()->member()->first()->member_id;

        try {
            DB::transaction(function () use ($s, $memberId) {
                $dombak = $s->dombak()->first();
                $barisStok = $dombak?->stok_inventory_dombak()->first();
                $inventory = $barisStok?->stok_inventory()->first();

                if (is_null($barisStok) || is_null($inventory)) {
                    throw new RuntimeException(
                        "Dombak {$dombak?->nama_dombak} belum terhubung ke produk. Hubungkan lewat Monitoring Stok."
                    );
                }

                $liter = Uang::kurang($s->stand_meter_akhir, $s->stand_meter_awal);
                $selisih = $this->selisihStok($s, $inventory, $barisStok, $liter);

                $stokAwal = Uang::of($barisStok->stok);
                $stokAkhir = Uang::kurang($stokAwal, $selisih);

                $s->update(['is_confirmation' => 1]);

                $sudahAda = DB::table('stand_meter_dokumen_stok')
                    ->where('stand_meter_id', $s->stand_meter_id)->exists();

                if (! $sudahAda) {
                    $jamKerja = $s->jam_kerja()->first();

                    $dokumen = Persediaan::tipe('Good Issue Sales')->dokumen_stok()->create([
                        'stok_inventory_id' => $inventory->stok_inventory_id,
                        'jam_kerja_id' => $s->jam_kerja_id,
                        'member_id' => $memberId,
                        'dombak_id' => $s->dombak_id,
                        'tanggal' => $s->tanggal,
                        'stok_awal' => $stokAwal,
                        'stok_akhir' => $stokAkhir,
                        'is_confirmation' => 1,
                        'waktu' => optional($jamKerja)->jam_pulang_out ?? '00:00:00',
                    ]);

                    DB::table('stand_meter_dokumen_stok')->insert([
                        'stand_meter_id' => $s->stand_meter_id,
                        'dokumen_stok_id' => $dokumen->dokumen_stok_id,
                    ]);

                    Persediaan::kaitkanKonversi(
                        $dokumen,
                        $dombak->konversi()->whereRaw('ROUND(volume::numeric, 6) = ?', [$stokAkhir])->first()
                    );

                    $barisStok->update(['stok' => $stokAkhir]);
                    Persediaan::sinkronkanTotal($inventory);
                }
            });
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok($this->lengkap($s->fresh()), 'Totalisator dikonfirmasi dan stok dipotong.');
    }

    public function destroy(string $id)
    {
        $s = StandMeter::find($id);
        if (is_null($s)) {
            return ApiResponse::fail('Data totalisator tidak ditemukan.', 404);
        }
        if (filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Data yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }
        if ($s->detail_penjualan()->exists()) {
            return ApiResponse::fail('Data ini sudah dipakai pada setoran penjualan.', 422);
        }

        $s->delete();

        return ApiResponse::ok([], 'Data totalisator dihapus.');
    }

    // --------------------------------------------------------------- privat

    /**
     * Bila pada shift yang sama ada penerimaan BBM tetapi belum ada dokumen
     * penjualan, angka pemotongan stok direkonsiliasi terhadap isi tangki —
     * mengikuti perhitungan alur web.
     */
    private function selisihStok(StandMeter $s, $inventory, $barisStok, string $liter): string
    {
        $tipeTaking = Persediaan::tipe(Persediaan::GERAK_TERIMA_TAKING);
        $tipeTerima = Persediaan::tipe(Persediaan::GERAK_TERIMA);
        $tipeJual = Persediaan::tipe('Good Issue Sales');

        $padaShift = fn ($tipeId) => $inventory->dokumen_stok()
            ->where('tanggal', $s->tanggal)
            ->where('jam_kerja_id', $s->jam_kerja_id)
            ->where('type_dokumen_stok_id', $tipeId)
            ->get();

        $goodReceive = $padaShift($tipeTerima->type_dokumen_stok_id);
        $sudahAdaPenjualan = $padaShift($tipeJual->type_dokumen_stok_id);

        if ($goodReceive->isEmpty() || $sudahAdaPenjualan->isNotEmpty()) {
            return $liter;
        }

        $taking = $padaShift($tipeTaking->type_dokumen_stok_id);

        // stok awal shift = stok_awal terkecil dari dokumen penyelarasan
        $stokAwalShift = '0';
        foreach ($taking as $t) {
            $nilai = Uang::of($t->stok_awal);
            if (Uang::nol($stokAwalShift) || Uang::bandingkan($stokAwalShift, $nilai) > 0) {
                $stokAwalShift = $nilai;
            }
        }

        // jumlah BBM yang masuk, dibersihkan dari losses penerimaan
        $masuk = '0';
        foreach ($goodReceive as $r) {
            $delta = Uang::kurang($r->stok_akhir, $r->stok_awal);
            $loss = $r->losses()->first();
            if ($loss) {
                $delta = $loss->simbol === 'lebih'
                    ? Uang::kurang($delta, $loss->jumlah)
                    : Uang::tambah($delta, $loss->jumlah);
            }
            $masuk = Uang::tambah($masuk, $delta);
        }

        return Uang::kurang(
            Uang::of($barisStok->stok),
            Uang::kurang(Uang::tambah($stokAwalShift, $masuk), $liter)
        );
    }

    private function terakhir(Nozzle $nozzle): ?StandMeter
    {
        return $nozzle->stand_meter()
            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')
            ->orderBy('stand_meter.tanggal', 'desc')
            ->orderBy('jam_kerja.urutan', 'desc')
            ->select('stand_meter.*')
            ->first();
    }

    private function daftar(Request $request, bool $terkonfirmasi)
    {
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'nozzle_id' => ['nullable', 'exists:nozzle,nozzle_id'],
            'dombak_id' => ['nullable', 'exists:dombak,dombak_id'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = StandMeter::where('is_confirmation', $terkonfirmasi ? 1 : 0)
            ->when($request->filled('dari'), fn ($q) => $q->where('tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('nozzle_id'), fn ($q) => $q->where('nozzle_id', $request->input('nozzle_id')))
            ->when($request->filled('dombak_id'), fn ($q) => $q->where('dombak_id', $request->input('dombak_id')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereRaw('LOWER(CAST(tanggal AS TEXT)) LIKE ?', [$pola])
                    ->orWhereHas('nozzle', fn ($n) => $n->whereRaw('LOWER(nama_nozzle) LIKE ?', [$pola]))
                    ->orWhereHas('dombak', fn ($d) => $d->whereRaw('LOWER(nama_dombak) LIKE ?', [$pola]))
                    ->orWhereHas('nozzle.produk', fn ($p) => $p->whereRaw('LOWER(nama_produk) LIKE ?', [$pola])));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('urutan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::paginated($paginator, fn (StandMeter $s) => $this->ringkas($s));
    }

    private function ringkas(StandMeter $s): array
    {
        $nozzle = $s->nozzle()->first();
        $liter = Uang::kurang($s->stand_meter_akhir, $s->stand_meter_awal);
        $belumAkhir = Uang::nol($s->stand_meter_akhir);

        return [
            'id' => $s->stand_meter_id,
            'stand_meter_id' => $s->stand_meter_id,
            'judul' => (optional($nozzle)->nama_nozzle ?? '-') . ' · ' . (optional($s->dombak()->first())->nama_dombak ?? '-'),
            'subjudul' => $s->tanggal . ' · ' . (optional($s->jam_kerja()->first())->nama_jam_kerja ?? '-')
                . ' · ' . ($belumAkhir ? 'belum diisi' : $liter . ' L'),
            'tanggal' => $s->tanggal,
            'nozzle' => optional($nozzle)->nama_nozzle,
            'produk' => optional(optional($nozzle)->produk()->first())->nama_produk,
            'dombak' => optional($s->dombak()->first())->nama_dombak,
            'jam_kerja' => optional($s->jam_kerja()->first())->nama_jam_kerja,
            'urutan' => (int) $s->urutan,
            'stand_meter_awal' => Uang::of($s->stand_meter_awal),
            'stand_meter_akhir' => Uang::of($s->stand_meter_akhir),
            'liter' => $belumAkhir ? '0.000000' : $liter,
            'belum_diisi_akhir' => $belumAkhir,
            'is_confirmation' => filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function lengkap(StandMeter $s): array
    {
        $dombak = $s->dombak()->first();
        $barisStok = $dombak?->stok_inventory_dombak()->first();
        $dokumen = $s->dokumen_stok()->first();

        return array_merge($this->ringkas($s), [
            'nozzle_id' => $s->nozzle_id,
            'dombak_id' => $s->dombak_id,
            'jam_kerja_id' => $s->jam_kerja_id,
            'stok_dombak_sekarang' => $barisStok ? Uang::of($barisStok->stok) : null,
            'dokumen_stok' => $dokumen ? Persediaan::serialDokumen($dokumen) : null,
            'dipakai_setoran' => $s->detail_penjualan()->exists(),
            'jumlah_lampiran' => $s->file_stand_meter()->count(),
        ]);
    }
}
