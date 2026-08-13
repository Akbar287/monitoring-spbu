<?php

namespace App\Http\Controllers\Api\V1\Financial;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Support\ApiResponse;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Jurnal Umum & Bagan Akun (COA) — baca-saja.
 *
 * Pencatatan jurnal hanya boleh lewat App\Support\JurnalPosting yang dipicu
 * transaksi operasional; API mobile sengaja tidak menyediakan jalur tulis.
 */
class JurnalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'spbu_id' => ['nullable', 'exists:spbu,spbu_id'],
            'tipe_sumber' => ['nullable', 'string', 'max:32'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $dari = $request->input('dari', Carbon::now()->firstOfMonth()->toDateString());
        $sampai = $request->input('sampai', Carbon::now()->endOfMonth()->toDateString());

        $paginator = Jurnal::whereBetween('tanggal', [$dari, $sampai])
            ->when($request->filled('spbu_id'), fn ($q) => $q->where('spbu_id', $request->input('spbu_id')))
            ->when($request->filled('tipe_sumber'), fn ($q) => $q->where('tipe_sumber', $request->input('tipe_sumber')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereRaw('LOWER(nomor_jurnal) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(keterangan) LIKE ?', [$pola])
                    ->orWhereRaw('LOWER(tipe_sumber) LIKE ?', [$pola]));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('nomor_jurnal', 'desc')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::paginated($paginator, fn (Jurnal $j) => $this->ringkas($j));
    }

    public function show(string $id)
    {
        $j = Jurnal::find($id);
        if (is_null($j)) {
            return ApiResponse::fail('Jurnal tidak ditemukan.', 404);
        }

        $detail = $j->jurnal_detail()->orderBy('urutan')->get()->map(function ($d) {
            $coa = $d->coa()->first();

            return [
                'kode_akun' => optional($coa)->kode_akun,
                'nama_akun' => optional($coa)->nama_akun,
                'tipe_akun' => optional($coa)->tipe_akun,
                'coa_id' => $d->coa_id,
                'debit' => Uang::of($d->debit),
                'kredit' => Uang::of($d->kredit),
                'keterangan' => $d->keterangan,
            ];
        })->values();

        $totalDebit = Uang::jumlah($detail, 'debit');
        $totalKredit = Uang::jumlah($detail, 'kredit');

        return ApiResponse::ok(array_merge($this->ringkas($j), [
            'detail' => $detail->all(),
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            // jurnal wajib seimbang; kalau tidak, ada kerusakan data
            'seimbang' => Uang::bandingkan($totalDebit, $totalKredit) === 0,
            'diposting_oleh' => optional(optional($j->member()->first())?->ktp()?->first())->nama,
        ]));
    }

    /** Bagan akun (COA) — rujukan kode akun untuk seluruh laporan. */
    public function coa(Request $request)
    {
        $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'tipe_akun' => ['nullable', 'string', 'max:20'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $cari = mb_strtolower(trim((string) $request->input('cari')));

        $paginator = Coa::orderBy('kode_akun')
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->whereRaw('LOWER(kode_akun) LIKE ?', ['%' . $cari . '%'])
                ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%' . $cari . '%'])))
            ->when($request->filled('tipe_akun'), fn ($q) => $q->where('tipe_akun', $request->input('tipe_akun')))
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::paginated($paginator, fn (Coa $c) => [
                'id' => $c->coa_id,
                'coa_id' => $c->coa_id,
                'kode_akun' => $c->kode_akun,
                'nama_akun' => $c->nama_akun,
                'tipe_akun' => $c->tipe_akun,
                'saldo_normal' => $c->saldo_normal,
                'kategori_arus_kas' => $c->kategori_arus_kas,
                'is_kas' => filter_var($c->is_kas, FILTER_VALIDATE_BOOLEAN),
                'is_header' => filter_var($c->is_header, FILTER_VALIDATE_BOOLEAN),
                'is_aktif' => filter_var($c->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    private function ringkas(Jurnal $j): array
    {
        $total = Uang::of($j->jurnal_detail()->sum('debit'));

        return [
            'id' => $j->jurnal_id,
            'jurnal_id' => $j->jurnal_id,
            'judul' => $j->nomor_jurnal,
            'subjudul' => $j->tanggal . ' · ' . $j->tipe_sumber . ' · ' . $total,
            'nomor_jurnal' => $j->nomor_jurnal,
            'tanggal' => $j->tanggal,
            'tipe_sumber' => $j->tipe_sumber,
            'keterangan' => $j->keterangan,
            'total' => $total,
            'spbu' => optional($j->spbu()->first())->nama_spbu,
            'is_posted' => filter_var($j->is_posted, FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
