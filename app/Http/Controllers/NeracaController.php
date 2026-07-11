<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NeracaController extends Controller
{
    /**
     * Neraca (laporan posisi keuangan) per tanggal: saldo akun riil sejak awal
     * pembukuan s.d. tanggal neraca + laba (rugi) berjalan dari akun nominal
     * yang belum ditutup. Total aset harus = total kewajiban + modal.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $spbuId = $request->spbu_id;
        $per = $request->per ?? Carbon::now()->toDateString();

        $saldo = DB::table('jurnal_detail')
            ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
            ->where('jurnal.is_posted', true)
            ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId))
            ->where('jurnal.tanggal', '<=', $per)
            ->groupBy('jurnal_detail.coa_id')
            ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.debit - jurnal_detail.kredit) as saldo'))
            ->pluck('saldo', 'coa_id');

        $akun = Coa::where('is_header', false)->orderBy('kode_akun')->get();

        // laba berjalan = akumulasi akun nominal yang belum ditutup ke laba ditahan
        $labaBerjalan = 0;
        foreach ($akun->whereIn('tipe_akun', Coa::TIPE_NOMINAL) as $a) {
            $labaBerjalan += -floatval($saldo[$a->coa_id] ?? 0); // kredit positif = laba
        }

        $bagian = function (array $tipe, int $arah) use ($akun, $saldo) {
            // arah 1: saldo positif di sisi debit (aset); -1: di sisi kredit (kewajiban/modal)
            $rows = [];
            $subtotal = 0;
            foreach ($akun->whereIn('tipe_akun', $tipe) as $a) {
                $nilai = floatval($saldo[$a->coa_id] ?? 0) * $arah;
                if (abs($nilai) < 0.005) {
                    continue;
                }
                $rows[] = [$a->kode_akun, $a->nama_akun, $this->fmt($nilai)];
                $subtotal += $nilai;
            }
            return [$rows, $subtotal];
        };

        [$rowsAset, $totalAset] = $bagian(['aset'], 1);
        $rowsAset[] = ['cells' => ['', 'TOTAL ASET', $this->fmt($totalAset)], 'bold' => true];

        [$rowsKewajiban, $totalKewajiban] = $bagian(['kewajiban'], -1);
        $rowsKewajiban[] = ['cells' => ['', 'Total Kewajiban', $this->fmt($totalKewajiban)], 'bold' => true];

        [$rowsModal, $totalModal] = $bagian(['modal'], -1);
        if (abs($labaBerjalan) >= 0.005) {
            $rowsModal[] = ['', 'Laba (Rugi) Berjalan', $this->fmt($labaBerjalan)];
            $totalModal += $labaBerjalan;
        }
        $rowsModal[] = ['cells' => ['', 'Total Modal', $this->fmt($totalModal)], 'bold' => true];

        $totalPasiva = $totalKewajiban + $totalModal;
        $rowsPasiva = array_merge(
            [['cells' => ['', 'KEWAJIBAN', ''], 'bold' => true]],
            $rowsKewajiban,
            [['cells' => ['', 'MODAL', ''], 'bold' => true]],
            $rowsModal,
            [['cells' => ['', 'TOTAL KEWAJIBAN + MODAL', $this->fmt($totalPasiva)], 'bold' => true]]
        );

        $adaData = $saldo->count() > 0;
        $seimbang = abs($totalAset - $totalPasiva) < 0.005;
        $notice = ! $adaData
            ? ['text' => 'Belum ada jurnal sampai tanggal ini.', 'color' => 'info']
            : ($seimbang
                ? ['text' => 'Neraca seimbang: total aset = total kewajiban + modal.', 'color' => 'success']
                : ['text' => 'PERINGATAN: neraca TIDAK seimbang (selisih ' . $this->fmt($totalAset - $totalPasiva) . '). Periksa saldo awal dan jurnal.', 'color' => 'danger']);

        return Inertia::render('Financial/Laporan', [
            'title' => 'Neraca',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'action' => route('neraca.index'),
            'filters' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $spbuId, 'allowAll' => true,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all()],
                ['name' => 'per', 'label' => 'Per Tanggal', 'type' => 'date', 'value' => $per],
            ],
            'summary' => [
                ['label' => 'Per Tanggal', 'value' => Carbon::parse($per)->format('d/m/Y')],
                ['label' => 'SPBU', 'value' => $spbuId ? optional($spbu->firstWhere('spbu_id', $spbuId))->nama_spbu : 'Semua (konsolidasi)'],
                ['label' => 'Total Aset', 'value' => $this->fmt($totalAset)],
                ['label' => 'Kewajiban + Modal', 'value' => $this->fmt($totalPasiva)],
            ],
            'tables' => $adaData ? [
                ['title' => 'Aset', 'columns' => ['Kode', 'Uraian', 'Jumlah'], 'alignRight' => [2], 'rows' => $rowsAset],
                ['title' => 'Kewajiban & Modal', 'columns' => ['Kode', 'Uraian', 'Jumlah'], 'alignRight' => [2], 'rows' => $rowsPasiva],
            ] : [],
            'notice' => $notice,
        ]);
    }

    private function fmt(float $n): string
    {
        return $n < 0 ? '(' . number_format(abs($n), 2, ',', '.') . ')' : number_format($n, 2, ',', '.');
    }
}
