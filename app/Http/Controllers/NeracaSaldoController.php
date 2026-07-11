<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NeracaSaldoController extends Controller
{
    /**
     * Neraca saldo (trial balance): saldo awal, mutasi, dan saldo akhir per
     * akun. Total kolom debit dan kredit harus sama — alat validasi utama
     * bahwa seluruh posting jurnal seimbang.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $spbuId = $request->spbu_id;
        $dari = $request->dari ?? Carbon::now()->firstOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->toDateString();

        $base = fn () => DB::table('jurnal_detail')
            ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
            ->where('jurnal.is_posted', true)
            ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId));

        $saldoAwal = $base()->where('jurnal.tanggal', '<', $dari)
            ->groupBy('jurnal_detail.coa_id')
            ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.debit - jurnal_detail.kredit) as saldo'))
            ->pluck('saldo', 'coa_id');

        $mutasi = $base()->whereBetween('jurnal.tanggal', [$dari, $sampai])
            ->groupBy('jurnal_detail.coa_id')
            ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.debit) as debit'), DB::raw('sum(jurnal_detail.kredit) as kredit'))
            ->get()->keyBy('coa_id');

        $rows = [];
        $tot = ['awal_d' => 0, 'awal_k' => 0, 'mut_d' => 0, 'mut_k' => 0, 'akhir_d' => 0, 'akhir_k' => 0];
        foreach (Coa::where('is_header', false)->orderBy('kode_akun')->get() as $a) {
            $awal = floatval($saldoAwal[$a->coa_id] ?? 0);
            $mutD = floatval($mutasi[$a->coa_id]->debit ?? 0);
            $mutK = floatval($mutasi[$a->coa_id]->kredit ?? 0);
            $akhir = $awal + $mutD - $mutK;
            if (abs($awal) < 0.005 && abs($mutD) < 0.005 && abs($mutK) < 0.005 && abs($akhir) < 0.005) {
                continue; // akun tanpa aktivitas tidak ditampilkan
            }

            $rows[] = [
                $a->kode_akun,
                $a->nama_akun,
                $awal > 0 ? $this->fmt($awal) : '',
                $awal < 0 ? $this->fmt(-$awal) : '',
                $mutD > 0 ? $this->fmt($mutD) : '',
                $mutK > 0 ? $this->fmt($mutK) : '',
                $akhir > 0 ? $this->fmt($akhir) : '',
                $akhir < 0 ? $this->fmt(-$akhir) : '',
            ];
            $tot['awal_d'] += max($awal, 0);
            $tot['awal_k'] += max(-$awal, 0);
            $tot['mut_d'] += $mutD;
            $tot['mut_k'] += $mutK;
            $tot['akhir_d'] += max($akhir, 0);
            $tot['akhir_k'] += max(-$akhir, 0);
        }
        $rows[] = ['cells' => ['', 'TOTAL', $this->fmt($tot['awal_d']), $this->fmt($tot['awal_k']), $this->fmt($tot['mut_d']), $this->fmt($tot['mut_k']), $this->fmt($tot['akhir_d']), $this->fmt($tot['akhir_k'])], 'bold' => true];

        $seimbang = abs($tot['akhir_d'] - $tot['akhir_k']) < 0.005 && abs($tot['mut_d'] - $tot['mut_k']) < 0.005;
        $notice = count($rows) <= 1
            ? ['text' => 'Belum ada jurnal pada periode ini.', 'color' => 'info']
            : ($seimbang
                ? ['text' => 'Neraca saldo seimbang: total debit = total kredit.', 'color' => 'success']
                : ['text' => 'PERINGATAN: neraca saldo TIDAK seimbang — ada jurnal yang rusak, segera periksa.', 'color' => 'danger']);

        return Inertia::render('Financial/Laporan', [
            'title' => 'Neraca Saldo',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'action' => route('neraca-saldo.index'),
            'filters' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $spbuId, 'allowAll' => true,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all()],
                ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date', 'value' => $dari],
                ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date', 'value' => $sampai],
            ],
            'summary' => [
                ['label' => 'Periode', 'value' => Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($sampai)->format('d/m/Y')],
                ['label' => 'SPBU', 'value' => $spbuId ? optional($spbu->firstWhere('spbu_id', $spbuId))->nama_spbu : 'Semua (konsolidasi)'],
            ],
            'tables' => [[
                'columns' => ['Kode', 'Nama Akun', 'Awal D', 'Awal K', 'Mutasi D', 'Mutasi K', 'Akhir D', 'Akhir K'],
                'alignRight' => [2, 3, 4, 5, 6, 7],
                'rows' => $rows,
            ]],
            'notice' => $notice,
        ]);
    }

    private function fmt(float $n): string
    {
        return number_format($n, 2, ',', '.');
    }
}
