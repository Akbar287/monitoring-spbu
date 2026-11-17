<?php

namespace App\Http\Controllers;

use App\Http\Requests\KualitasMinyakRequest;
use App\Http\Requests\KualitasMinyakPemeriksaanRequest;
use App\Models\AstmDensity;
use App\Models\Dombak;
use App\Models\KualitasMinyak;
use App\Models\KualitasMinyakPenerimaan;
use App\Models\Produk;
use App\Support\AstmLookup;
use App\Support\KualitasMinyakAkses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class KualitasMinyakController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('KualitasMinyak/Index', $this->daftar($request, false));
    }

    public function riwayat(Request $request)
    {
        return Inertia::render('KualitasMinyak/Index', $this->daftar($request, true));
    }

    private function daftar(Request $request, bool $riwayat): array
    {
        $filters = $request->validate([
            'mulai' => ['nullable', 'date_format:Y-m-d'],
            'selesai' => ['nullable', 'date_format:Y-m-d', ...($request->filled('mulai') ? ['after_or_equal:mulai'] : [])],
            'dombak_id' => ['nullable', 'uuid', 'exists:dombak,dombak_id'],
            'produk_id' => ['nullable', 'uuid', 'exists:produk,produk_id'],
        ]);
        $query = KualitasMinyak::with('penerimaan')->orderByDesc('tanggal')->orderByDesc('created_at');
        $query->where(function ($q) use ($riwayat) {
            if ($riwayat) {
                $q->whereNotNull('disetujui_pada')->orWhereHas('penerimaan', fn ($p) => $p
                    ->whereNotNull('penerimaan_disetujui_pada')->orWhereNotNull('akhir_disetujui_pada'));
            } else {
                $q->whereNull('disetujui_pada')->orWhereHas('penerimaan', fn ($p) => $p
                    ->whereNull('penerimaan_disetujui_pada')->orWhereNull('akhir_disetujui_pada'));
            }
        });
        $query->when($filters['mulai'] ?? null, fn ($q, $v) => $q->where('tanggal', '>=', $v))
            ->when($filters['selesai'] ?? null, fn ($q, $v) => $q->where('tanggal', '<=', $v));
        foreach (['dombak_id', 'produk_id'] as $key) {
            $query->when($filters[$key] ?? null, fn ($q, $v) => $q->where($key, $v));
        }

        return [
            'title' => $riwayat ? 'Riwayat Kualitas Minyak' : 'Kualitas Minyak',
            'riwayat' => $riwayat, 'filters' => $filters,
            'records' => $query->paginate(25)->withQueryString(),
            'dombak' => Dombak::orderBy('nama_dombak')->get(['dombak_id', 'nama_dombak']),
            'produk' => Produk::orderBy('nama_produk')->get(['produk_id', 'nama_produk']),
            'bolehMencatat' => KualitasMinyakAkses::mencatat($request->user()),
            'baseUrl' => route('kualitas-minyak.index'),
        ];
    }

    public function create(Request $request)
    {
        abort_unless(KualitasMinyakAkses::mencatat($request->user()), 403);
        $identitas = $request->validate([
            'dombak_id' => ['nullable', 'required_with:tanggal', 'uuid', 'exists:dombak,dombak_id'],
            'tanggal' => ['nullable', 'required_with:dombak_id', 'date_format:Y-m-d'],
        ]);
        if (! empty($identitas['dombak_id']) && ! empty($identitas['tanggal'])) {
            $record = KualitasMinyak::where('dombak_id', $identitas['dombak_id'])->where('tanggal', $identitas['tanggal'])->first();
            if ($record) {
                return redirect()->route('kualitas-minyak.show', $record);
            }
        }

        return Inertia::render('KualitasMinyak/Form', $this->formProps() + [
            'identitas' => $identitas, 'identitasDiperiksa' => ! empty($identitas['dombak_id']),
        ]);
    }

    public function edit(Request $request, KualitasMinyak $kualitasMinyak)
    {
        $data = $request->validate([
            'tahap' => ['sometimes', Rule::in(['pagi', 'penerimaan', 'akhir'])],
            'penerimaan_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $tahap = $data['tahap'] ?? 'pagi';
        $row = isset($data['penerimaan_id']) ? $kualitasMinyak->penerimaan()->findOrFail($data['penerimaan_id']) : null;
        abort_if($tahap === 'akhir' && ! $row, 404);
        $this->bolehTahap($request, $kualitasMinyak, $tahap, $row);

        return Inertia::render('KualitasMinyak/Form', $this->formProps($kualitasMinyak, $tahap, $row));
    }

    private function formProps(?KualitasMinyak $record = null, string $tahap = 'pagi', ?KualitasMinyakPenerimaan $row = null): array
    {
        $url = $record ? route('kualitas-minyak.show', $record) : route('kualitas-minyak.index');
        $action = match ($tahap) {
            'penerimaan' => $url.'/penerimaan'.($row ? '/'.$row->id : ''),
            'akhir' => $url.'/penerimaan/'.$row->id.'/akhir',
            default => $url,
        };

        return [
            'title' => (($tahap === 'akhir' ? $row?->waktu_akhir : $row || ($record && $tahap === 'pagi')) ? 'Ubah' : 'Isi').' Density '.ucfirst($tahap),
            'tahap' => $tahap, 'pemeriksaan' => $row, 'record' => $record,
            'action' => $action, 'method' => $row || ($record && $tahap === 'pagi') ? 'put' : 'post',
            'dombak' => Dombak::with(['spbu', 'stok_inventory_dombak.stok_inventory.produk'])
                ->where('is_aktif', 1)->orderBy('nama_dombak')->get()->map(function ($d) {
                    $produk = $d->stok_inventory_dombak->map(fn ($s) => $s->stok_inventory?->produk)->filter()->unique('produk_id');

                    return ['id' => $d->getKey(), 'nama' => $d->nama_dombak, 'nomor_spbu' => $d->spbu?->nomor_spbu,
                        'nama_produk' => $produk->count() === 1 ? $produk->first()->nama_produk : null,
                        'singkatan' => $produk->count() === 1 ? $produk->first()->singkatan : null];
                }),
            'baseUrl' => route('kualitas-minyak.index'),
            'today' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
        ];
    }

    public function store(KualitasMinyakRequest $request)
    {
        $data = $this->hitungPagi($request);
        $record = KualitasMinyak::create($data + [
            'dibuat_oleh' => $request->user()->getKey(), 'nama_operator' => $this->nama($request),
        ]);

        return redirect()->route('kualitas-minyak.show', $record);
    }

    public function update(KualitasMinyakRequest $request, KualitasMinyak $kualitasMinyak)
    {
        $data = $this->hitungPagi($request, $kualitasMinyak);
        DB::transaction(function () use ($request, $kualitasMinyak, $data) {
            $record = KualitasMinyak::lockForUpdate()->findOrFail($kualitasMinyak->getKey());
            $this->bolehTahap($request, $record, 'pagi');
            if ($record->penerimaan()->where('waktu_penerimaan', '<', Carbon::parse($record->tanggal.' '.$data['jam_awal']))->exists()) {
                throw ValidationException::withMessages(['jam_awal' => 'Pemeriksaan Pagi tidak boleh sesudah penerimaan yang sudah dicatat.']);
            }
            $record->update($data);
        });

        return redirect()->route('kualitas-minyak.show', $kualitasMinyak);
    }

    private function hitungPagi(KualitasMinyakRequest $request, ?KualitasMinyak $record = null): array
    {
        $values = $request->validated();
        unset($values['penerimaan']);
        $dombak = Dombak::with(['spbu', 'stok_inventory_dombak.stok_inventory.produk'])->findOrFail($values['dombak_id']);
        $products = $dombak->stok_inventory_dombak->map(fn ($s) => $s->stok_inventory?->produk)->filter()->unique('produk_id');
        $produk = $record ? Produk::find($record->produk_id) : ($products->count() === 1 ? $products->first() : null);
        if (! $produk || ! $dombak->spbu || (! $record && (! $dombak->is_aktif || $produk->spbu_id !== $dombak->spbu_id))) {
            throw ValidationException::withMessages(['dombak_id' => 'Dombak harus aktif dan memiliki tepat satu produk dari SPBU yang sama. Lengkapi master persediaan dombak.']);
        }
        $awal = $this->konversi($values['density_awal'], $values['suhu_awal'], $produk, 'density_awal');

        return $values + [
            'spbu_id' => $record?->spbu_id ?? $dombak->spbu_id, 'produk_id' => $produk->getKey(),
            'nomor_spbu' => $record?->nomor_spbu ?? $dombak->spbu->nomor_spbu,
            'nama_dombak' => $record?->nama_dombak ?? $dombak->nama_dombak,
            'nama_produk' => $record?->nama_produk ?? $produk->nama_produk,
            'density_15_awal' => $awal->density_15, 'astm_awal_id' => $awal->id,
        ];
    }

    public function tambahPenerimaan(KualitasMinyakPemeriksaanRequest $request, KualitasMinyak $kualitasMinyak)
    {
        return $this->simpanPemeriksaan($request, $kualitasMinyak, 'penerimaan');
    }

    public function ubahPenerimaan(KualitasMinyakPemeriksaanRequest $request, KualitasMinyak $kualitasMinyak, KualitasMinyakPenerimaan $penerimaan)
    {
        return $this->simpanPemeriksaan($request, $kualitasMinyak, 'penerimaan', $penerimaan);
    }

    public function simpanAkhir(KualitasMinyakPemeriksaanRequest $request, KualitasMinyak $kualitasMinyak, KualitasMinyakPenerimaan $penerimaan)
    {
        return $this->simpanPemeriksaan($request, $kualitasMinyak, 'akhir', $penerimaan);
    }

    private function simpanPemeriksaan(KualitasMinyakPemeriksaanRequest $request, KualitasMinyak $parent, string $tahap, ?KualitasMinyakPenerimaan $row = null)
    {
        DB::transaction(function () use ($request, $parent, $tahap, $row) {
            $record = KualitasMinyak::lockForUpdate()->findOrFail($parent->getKey());
            $current = $row ? $record->penerimaan()->lockForUpdate()->findOrFail($row->id) : null;
            $this->bolehTahap($request, $record, $tahap, $current);
            $data = $request->validated();
            $waktu = Carbon::parse($data['waktu_'.$tahap]);
            $data['waktu_'.$tahap] = $waktu->format('Y-m-d H:i:s');
            // Ulangi pemeriksaan waktu di dalam kunci transaksi untuk pembaruan bersamaan.
            if ($tahap === 'akhir') {
                if ($waktu->lt(Carbon::parse($current->waktu_penerimaan)->addHours(2))) {
                    throw ValidationException::withMessages(['waktu_akhir' => 'Pemeriksaan Akhir dilakukan paling cepat dua jam setelah penerimaan.']);
                }
            } elseif ($waktu->lt(Carbon::parse($record->tanggal.' '.$record->jam_awal))
                || ($current?->waktu_akhir && $waktu->copy()->addHours(2)->gt(Carbon::parse($current->waktu_akhir)))) {
                throw ValidationException::withMessages(['waktu_penerimaan' => 'Jam penerimaan harus sesudah pemeriksaan Pagi dan setidaknya dua jam sebelum pemeriksaan Akhir yang sudah ada.']);
            }
            $cell = $this->konversi($data['density_'.$tahap], $data['suhu_'.$tahap], Produk::findOrFail($record->produk_id), 'density_'.$tahap);
            $data['density_15_'.$tahap] = $cell->density_15;
            $data['astm_'.$tahap.'_id'] = $cell->id;
            if ($tahap === 'penerimaan') {
                $data['selisih_density'] = round((float) $cell->density_15 - (float) $data['density_15_pnbp'], 4);
            }
            if (! $current?->{$tahap.'_dibuat_oleh'}) {
                $data[$tahap.'_dibuat_oleh'] = $request->user()->getKey();
                $data[$tahap.'_nama_operator'] = $this->nama($request);
            }
            $current ? $current->update($data) : $record->penerimaan()->create($data);
        });

        return redirect()->route('kualitas-minyak.show', $parent);
    }

    private function nama(Request $request): string
    {
        return $request->user()->member?->ktp?->nama ?? $request->user()->username;
    }

    private function konversi($density, $suhu, Produk $produk, string $field): AstmDensity
    {
        $singkatan = mb_strtolower(trim($produk->singkatan));
        $nama = mb_strtolower($produk->nama_produk);
        $bensin = in_array($singkatan, ['pl', 'px'], true) || str_contains($nama, 'pertalite') || str_contains($nama, 'pertamax');
        $diesel = in_array($singkatan, ['bs', 'pd', 'pdx', 'dx', 'dex'], true) || str_contains($nama, 'solar') || str_contains($nama, 'dex');
        if (($bensin && ($density < 0.7 || $density >= 0.8)) || ($diesel && ($density < 0.8 || $density >= 0.9))) {
            throw ValidationException::withMessages([$field => $bensin ? 'Density PL/PX harus 0,7 sekian g/cm³.' : 'Density BS/Dex harus 0,8 sekian g/cm³.']);
        }

        return AstmLookup::cari((float) $density, (float) $suhu, $field);
    }

    public function show(Request $request, KualitasMinyak $kualitasMinyak)
    {
        $kualitasMinyak->load('penerimaan');
        $approver = KualitasMinyakAkses::menyetujui($request->user());
        foreach ($kualitasMinyak->penerimaan as $row) {
            foreach (['penerimaan', 'akhir'] as $tahap) {
                $row->setAttribute('boleh_ubah_'.$tahap, ! $row->{$tahap.'_disetujui_pada'}
                    && ($row->{$tahap.'_dibuat_oleh'} ? $row->{$tahap.'_dibuat_oleh'} === $request->user()->getKey() || $approver : KualitasMinyakAkses::mencatat($request->user())));
            }
        }

        return Inertia::render('KualitasMinyak/Show', [
            'title' => 'Catatan Kualitas Harian', 'record' => $kualitasMinyak,
            'bolehMengubahPagi' => ! $kualitasMinyak->disetujui_pada && ($kualitasMinyak->dibuat_oleh === $request->user()->getKey() || $approver),
            'bolehMencatat' => KualitasMinyakAkses::mencatat($request->user()),
            'bolehMenyetujui' => $approver,
            'peringatan' => $kualitasMinyak->penerimaan->flatMap(fn ($r) => $r->peringatan())->values(),
            'baseUrl' => route('kualitas-minyak.index'),
        ]);
    }

    public function setujui(Request $request, KualitasMinyak $kualitasMinyak)
    {
        abort_unless(KualitasMinyakAkses::menyetujui($request->user()), 403);
        $data = $request->validate([
            'tahap' => ['required', Rule::in(['pagi', 'penerimaan', 'akhir'])],
            'penerimaan_id' => ['required_unless:tahap,pagi', 'nullable', 'integer', 'min:1'],
        ]);
        DB::transaction(function () use ($request, $kualitasMinyak, $data) {
            $record = KualitasMinyak::lockForUpdate()->findOrFail($kualitasMinyak->getKey());
            $tahap = $data['tahap'];
            $target = $tahap === 'pagi' ? $record : $record->penerimaan()->lockForUpdate()->findOrFail($data['penerimaan_id']);
            $prefix = $tahap === 'pagi' ? '' : $tahap.'_';
            abort_if($target->{$prefix.'disetujui_pada'}, 409, 'Pemeriksaan density ini sudah disetujui.');
            if ($tahap === 'akhir' && ! $target->waktu_akhir) {
                throw ValidationException::withMessages(['persetujuan' => 'Isi pemeriksaan Akhir sebelum menyetujui density Akhir.']);
            }
            $target->update([
                $prefix.'disetujui_oleh' => $request->user()->getKey(),
                $prefix.'nama_penyetuju' => $this->nama($request),
                $prefix.'disetujui_pada' => now(),
            ]);
        });

        return redirect()->route('kualitas-minyak.show', $kualitasMinyak);
    }

    private function bolehTahap(Request $request, KualitasMinyak $record, string $tahap, ?KualitasMinyakPenerimaan $row = null): void
    {
        $target = $tahap === 'pagi' ? $record : $row;
        $prefix = $tahap === 'pagi' ? '' : $tahap.'_';
        abort_if($target?->{$prefix.'disetujui_pada'}, 403, 'Pemeriksaan density ini sudah disetujui dan dikunci.');
        $owner = $target?->{$prefix.'dibuat_oleh'};
        abort_unless($owner
            ? $owner === $request->user()->getKey() || KualitasMinyakAkses::menyetujui($request->user())
            : KualitasMinyakAkses::mencatat($request->user()), 403);
    }

    public function astm(Request $request)
    {
        $filters = $request->validate([
            'density' => ['nullable', 'numeric', 'between:0.690,0.899'],
            'suhu' => ['nullable', 'numeric', 'between:0,50'],
        ]);

        return Inertia::render('KualitasMinyak/Astm', [
            'title' => 'Tabel ASTM Density', 'filters' => $filters,
            'rows' => AstmDensity::when($filters['density'] ?? null, fn ($q, $v) => $q->where('density_observed', $v))
                ->when(isset($filters['suhu']), fn ($q) => $q->where('suhu', $filters['suhu']))
                ->orderBy('density_observed')->orderBy('suhu')->paginate(50)->withQueryString(),
            'manifest' => json_decode(file_get_contents(database_path('data/astm_d53.json')), true),
            'baseUrl' => route('kualitas-minyak.index'),
        ]);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate(['density' => ['required', 'numeric', 'between:0.690,0.899'], 'suhu' => ['required', 'numeric', 'between:0,50']]);
        $cell = AstmLookup::cari((float) $data['density'], (float) $data['suhu']);

        return response()->json(['density_15' => $cell->density_15, 'halaman' => $cell->halaman]);
    }
}
