<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LabaRugiController extends Controller
{
    /**
     * Laporan laba rugi: agregasi akun nominal (pendapatan, hpp, beban,
     * pendapatan/beban lain-lain) dalam rentang tanggal, diturunkan dari jurnal.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $spbuId = $request->spbu_id;
        $dari = $request->dari ?? Carbon::now()->firstOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->toDateString();

        // saldo per akun nominal dalam periode; pendapatan bernilai positif
        // pada sisi kredit, hpp/beban positif pada sisi debit
        $saldo = DB::table('jurnal_detail')
            ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
            ->where('jurnal.is_posted', true)
            ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId))
            ->whereBetween('jurnal.tanggal', [$dari, $sampai])
            ->groupBy('jurnal_detail.coa_id')
            ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.debit - jurnal_detail.kredit) as saldo'))
            ->pluck('saldo', 'coa_id');

        $akun = Coa::where('is_header', false)->whereIn('tipe_akun', Coa::TIPE_NOMINAL)->orderBy('kode_akun')->get();

        $rows = [];
        $total = [];
        $bagian = function (string $judul, array $tipe, int $arah) use ($akun, $saldo, &$rows, &$total) {
            // arah 1: positif di sisi kredit (pendapatan); -1: positif di sisi debit (beban)
            $rows[] = ['cells' => ['', strtoupper($judul), ''], 'bold' => true];
            $subtotal = 0;
            foreach ($akun->whereIn('tipe_akun', $tipe) as $a) {
                $nilai = floatval($saldo[$a->coa_id] ?? 0) * ($arah === 1 ? -1 : 1);
                if (abs($nilai) < 0.005) {
                    continue;
                }
                $rows[] = [$a->kode_akun, $a->nama_akun, $this->fmt($nilai)];
                $subtotal += $nilai;
            }
            $rows[] = ['cells' => ['', 'Total ' . $judul, $this->fmt($subtotal)], 'bold' => true];
            $total[$judul] = $subtotal;
        };

        $bagian('Pendapatan', ['pendapatan'], 1);
        $bagian('Harga Pokok Penjualan', ['hpp'], -1);
        $labaKotor = $total['Pendapatan'] - $total['Harga Pokok Penjualan'];
        $rows[] = ['cells' => ['', 'LABA KOTOR', $this->fmt($labaKotor)], 'bold' => true];

        $bagian('Beban Operasional', ['beban'], -1);
        $labaOperasional = $labaKotor - $total['Beban Operasional'];
        $rows[] = ['cells' => ['', 'LABA OPERASIONAL', $this->fmt($labaOperasional)], 'bold' => true];

        $bagian('Pendapatan Lain-lain', ['pendapatan_lain'], 1);
        $bagian('Beban Lain-lain', ['beban_lain'], -1);
        $labaBersih = $labaOperasional + $total['Pendapatan Lain-lain'] - $total['Beban Lain-lain'];
        $rows[] = ['cells' => ['', 'LABA (RUGI) BERSIH', $this->fmt($labaBersih)], 'bold' => true];

        $adaData = $saldo->count() > 0;

        return Inertia::render('Financial/Laporan', [
            'title' => 'Laba Rugi',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'action' => route('laba-rugi.index'),
            'filters' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $spbuId, 'allowAll' => true,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all()],
                ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date', 'value' => $dari],
                ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date', 'value' => $sampai],
            ],
            'summary' => [
                ['label' => 'Periode', 'value' => Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($sampai)->format('d/m/Y')],
                ['label' => 'SPBU', 'value' => $spbuId ? optional($spbu->firstWhere('spbu_id', $spbuId))->nama_spbu : 'Semua (konsolidasi)'],
                ['label' => 'Laba Kotor', 'value' => $this->fmt($labaKotor)],
                ['label' => 'Laba Operasional', 'value' => $this->fmt($labaOperasional)],
                ['label' => 'Laba (Rugi) Bersih', 'value' => $this->fmt($labaBersih)],
            ],
            'tables' => $adaData ? [[
                'columns' => ['Kode', 'Uraian', 'Jumlah'],
                'alignRight' => [2],
                'rows' => $rows,
            ]] : [],
            'notice' => $adaData ? null : ['text' => 'Belum ada jurnal pada periode ini.', 'color' => 'info'],
        ]);
    }

    private function fmt(float $n): string
    {
        return $n < 0 ? '(' . number_format(abs($n), 2, ',', '.') . ')' : number_format($n, 2, ',', '.');
    }
}
