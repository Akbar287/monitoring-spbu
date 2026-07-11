<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PerubahanModalController extends Controller
{
    /**
     * Laporan perubahan modal (ekuitas): modal awal periode + mutasi akun
     * modal (setoran, prive, dsb.) + laba (rugi) bersih periode = modal akhir.
     * Modal awal/akhir memperhitungkan laba berjalan dari akun nominal yang
     * belum ditutup, sehingga konsisten dengan bagian Modal di Neraca.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $spbuId = $request->spbu_id;
        $dari = $request->dari ?? Carbon::now()->firstOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->toDateString();

        $akunModal = Coa::where('is_header', false)->where('tipe_akun', 'modal')->orderBy('kode_akun')->get();
        $idModal = $akunModal->pluck('coa_id');
        $idNominal = Coa::where('is_header', false)->whereIn('tipe_akun', Coa::TIPE_NOMINAL)->pluck('coa_id');

        // saldo kredit-dikurangi-debit (positif = menambah modal)
        $saldoKD = function ($coaIds, $operator, $a, $b = null) use ($spbuId) {
            $q = DB::table('jurnal_detail')
                ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
                ->where('jurnal.is_posted', true)
                ->when($spbuId, fn ($qq) => $qq->where('jurnal.spbu_id', $spbuId))
                ->whereIn('jurnal_detail.coa_id', $coaIds);
            $operator === 'between' ? $q->whereBetween('jurnal.tanggal', [$a, $b]) : $q->where('jurnal.tanggal', $operator, $a);
            return floatval($q->sum(DB::raw('jurnal_detail.kredit - jurnal_detail.debit')));
        };

        $modalAwal = $saldoKD($idModal, '<', $dari) + $saldoKD($idNominal, '<', $dari);
        $labaPeriode = $saldoKD($idNominal, 'between', $dari, $sampai);
        $modalAkhirAktual = $saldoKD($idModal, '<=', $sampai) + $saldoKD($idNominal, '<=', $sampai);

        $rows = [['cells' => ['', 'Modal Awal per ' . Carbon::parse($dari)->subDay()->format('d/m/Y'), $this->fmt($modalAwal)], 'bold' => true]];

        // mutasi per akun modal dalam periode (setoran, prive, closing, dll.)
        $totalMutasi = 0;
        $mutasi = DB::table('jurnal_detail')
            ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
            ->where('jurnal.is_posted', true)
            ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId))
            ->whereIn('jurnal_detail.coa_id', $idModal)
            ->whereBetween('jurnal.tanggal', [$dari, $sampai])
            ->groupBy('jurnal_detail.coa_id')
            ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.kredit - jurnal_detail.debit) as mutasi'))
            ->pluck('mutasi', 'coa_id');
        foreach ($akunModal as $a) {
            $nilai = floatval($mutasi[$a->coa_id] ?? 0);
            if (abs($nilai) < 0.005) {
                continue;
            }
            $rows[] = [$a->kode_akun, 'Mutasi ' . $a->nama_akun, $this->fmt($nilai)];
            $totalMutasi += $nilai;
        }

        $rows[] = ['', 'Laba (Rugi) Bersih Periode', $this->fmt($labaPeriode)];
        $modalAkhir = $modalAwal + $totalMutasi + $labaPeriode;
        $rows[] = ['cells' => ['', 'Modal Akhir per ' . Carbon::parse($sampai)->format('d/m/Y'), $this->fmt($modalAkhir)], 'bold' => true];

        $adaData = abs($modalAwal) >= 0.005 || abs($totalMutasi) >= 0.005 || abs($labaPeriode) >= 0.005;
        $cocok = abs($modalAkhir - $modalAkhirAktual) < 0.005;
        $notice = ! $adaData
            ? ['text' => 'Belum ada data modal pada periode ini.', 'color' => 'info']
            : ($cocok
                ? ['text' => 'Modal akhir cocok dengan bagian Modal di Neraca.', 'color' => 'success']
                : ['text' => 'PERINGATAN: modal akhir (' . $this->fmt($modalAkhir) . ') tidak cocok dengan buku besar (' . $this->fmt($modalAkhirAktual) . ').', 'color' => 'danger']);

        return Inertia::render('Financial/Laporan', [
            'title' => 'Perubahan Modal',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'action' => route('perubahan-modal.index'),
            'filters' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $spbuId, 'allowAll' => true,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all()],
                ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date', 'value' => $dari],
                ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date', 'value' => $sampai],
            ],
            'summary' => [
                ['label' => 'Periode', 'value' => Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($sampai)->format('d/m/Y')],
                ['label' => 'SPBU', 'value' => $spbuId ? optional($spbu->firstWhere('spbu_id', $spbuId))->nama_spbu : 'Semua (konsolidasi)'],
                ['label' => 'Modal Awal', 'value' => $this->fmt($modalAwal)],
                ['label' => 'Laba (Rugi) Periode', 'value' => $this->fmt($labaPeriode)],
                ['label' => 'Modal Akhir', 'value' => $this->fmt($modalAkhir)],
            ],
            'tables' => $adaData ? [[
                'columns' => ['Kode', 'Uraian', 'Jumlah'],
                'alignRight' => [2],
                'rows' => $rows,
            ]] : [],
            'notice' => $notice,
        ]);
    }

    private function fmt(float $n): string
    {
        return $n < 0 ? '(' . number_format(abs($n), 2, ',', '.') . ')' : number_format($n, 2, ',', '.');
    }
}
