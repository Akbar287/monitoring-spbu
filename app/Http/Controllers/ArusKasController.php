<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ArusKasController extends Controller
{
    /**
     * Laporan arus kas metode langsung. Setiap jurnal yang menyentuh akun kas
     * (coa.is_kas) dianalisis dari sisi akun lawannya: nilai kredit-debit akun
     * lawan = arus kas yang dijelaskannya, diklasifikasikan menurut
     * kategori_arus_kas akun lawan (operasi/investasi/pendanaan).
     * Transfer antar sesama akun kas otomatis tereksklusi (tidak punya lawan
     * non-kas), dan jurnal tanpa akun kas (mis. HPP, faktur) tidak ikut.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $spbuId = $request->spbu_id;
        $dari = $request->dari ?? Carbon::now()->firstOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->toDateString();

        $kasIds = Coa::where('is_kas', true)->pluck('coa_id');

        $saldoKas = fn ($operator, $tanggal) => floatval(
            DB::table('jurnal_detail')
                ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
                ->where('jurnal.is_posted', true)
                ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId))
                ->whereIn('jurnal_detail.coa_id', $kasIds)
                ->where('jurnal.tanggal', $operator, $tanggal)
                ->sum(DB::raw('jurnal_detail.debit - jurnal_detail.kredit'))
        );
        $kasAwal = $saldoKas('<', $dari);
        $kasAkhirAktual = $saldoKas('<=', $sampai);

        // akun lawan (non-kas) dari jurnal periode ini yang menyentuh akun kas
        $arus = DB::table('jurnal_detail')
            ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
            ->join('coa', 'coa.coa_id', 'jurnal_detail.coa_id')
            ->where('jurnal.is_posted', true)
            ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId))
            ->whereBetween('jurnal.tanggal', [$dari, $sampai])
            ->where('coa.is_kas', false)
            ->whereIn('jurnal.jurnal_id', function ($q) use ($kasIds) {
                $q->select('jurnal_id')->from('jurnal_detail')->whereIn('coa_id', $kasIds);
            })
            ->groupBy('coa.coa_id', 'coa.kode_akun', 'coa.nama_akun', 'coa.kategori_arus_kas')
            ->select('coa.kode_akun', 'coa.nama_akun', 'coa.kategori_arus_kas', DB::raw('sum(jurnal_detail.kredit - jurnal_detail.debit) as arus'))
            ->orderBy('coa.kode_akun')
            ->get();

        $rows = [];
        $totalArus = 0;
        foreach (['operasi' => 'AKTIVITAS OPERASI', 'investasi' => 'AKTIVITAS INVESTASI', 'pendanaan' => 'AKTIVITAS PENDANAAN'] as $kategori => $judul) {
            // akun tanpa kategori arus kas ikut ke operasi (kategori bawaan)
            $bagian = $arus->filter(fn ($a) => ($a->kategori_arus_kas ?? 'operasi') === $kategori && abs(floatval($a->arus)) >= 0.005);
            if ($bagian->isEmpty()) {
                continue;
            }
            $rows[] = ['cells' => ['', $judul, ''], 'bold' => true];
            $subtotal = 0;
            foreach ($bagian as $a) {
                $rows[] = [$a->kode_akun, $a->nama_akun, $this->fmt(floatval($a->arus))];
                $subtotal += floatval($a->arus);
            }
            $rows[] = ['cells' => ['', 'Kas Bersih dari ' . ucwords(strtolower($judul)), $this->fmt($subtotal)], 'bold' => true];
            $totalArus += $subtotal;
        }
        $rows[] = ['cells' => ['', 'KENAIKAN (PENURUNAN) KAS BERSIH', $this->fmt($totalArus)], 'bold' => true];
        $rows[] = ['cells' => ['', 'Kas & Setara Kas Awal', $this->fmt($kasAwal)], 'bold' => true];
        $rows[] = ['cells' => ['', 'Kas & Setara Kas Akhir', $this->fmt($kasAwal + $totalArus)], 'bold' => true];

        $adaData = $arus->count() > 0 || abs($kasAwal) >= 0.005;
        $cocok = abs(($kasAwal + $totalArus) - $kasAkhirAktual) < 0.005;
        $notice = ! $adaData
            ? ['text' => 'Belum ada mutasi kas pada periode ini.', 'color' => 'info']
            : ($cocok
                ? ['text' => 'Arus kas cocok dengan saldo akun kas di buku besar.', 'color' => 'success']
                : ['text' => 'PERINGATAN: kas akhir arus kas (' . $this->fmt($kasAwal + $totalArus) . ') tidak cocok dengan saldo buku besar (' . $this->fmt($kasAkhirAktual) . ').', 'color' => 'danger']);

        return Inertia::render('Financial/Laporan', [
            'title' => 'Arus Kas',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'action' => route('arus-kas.index'),
            'filters' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $spbuId, 'allowAll' => true,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all()],
                ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date', 'value' => $dari],
                ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date', 'value' => $sampai],
            ],
            'summary' => [
                ['label' => 'Periode', 'value' => Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($sampai)->format('d/m/Y')],
                ['label' => 'SPBU', 'value' => $spbuId ? optional($spbu->firstWhere('spbu_id', $spbuId))->nama_spbu : 'Semua (konsolidasi)'],
                ['label' => 'Kas Awal', 'value' => $this->fmt($kasAwal)],
                ['label' => 'Arus Kas Bersih', 'value' => $this->fmt($totalArus)],
                ['label' => 'Kas Akhir', 'value' => $this->fmt($kasAwal + $totalArus)],
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
