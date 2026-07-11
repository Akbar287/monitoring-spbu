<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BukuBesarController extends Controller
{
    /**
     * Buku besar per akun: saldo awal + mutasi + saldo berjalan,
     * seluruhnya diturunkan dari jurnal (lihat docs/keuangan.md).
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $akun = Coa::where('is_header', false)->where('is_aktif', true)->orderBy('kode_akun')->get();

        $coaId = $request->coa_id;
        $spbuId = $request->spbu_id;
        $dari = $request->dari ?? Carbon::now()->firstOfMonth()->toDateString();
        $sampai = $request->sampai ?? Carbon::now()->endOfMonth()->toDateString();

        $summary = [];
        $tables = [];
        $notice = null;

        $coa = $coaId ? Coa::find($coaId) : null;
        if ($coa) {
            $base = DB::table('jurnal_detail')
                ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
                ->where('jurnal.is_posted', true)
                ->where('jurnal_detail.coa_id', $coa->coa_id)
                ->when($spbuId, fn ($q) => $q->where('jurnal.spbu_id', $spbuId));

            $saldoAwal = floatval((clone $base)->where('jurnal.tanggal', '<', $dari)
                ->sum(DB::raw('jurnal_detail.debit - jurnal_detail.kredit')));

            $mutasi = (clone $base)->whereBetween('jurnal.tanggal', [$dari, $sampai])
                ->orderBy('jurnal.tanggal')->orderBy('jurnal.nomor_jurnal')->orderBy('jurnal_detail.urutan')
                ->select('jurnal.tanggal', 'jurnal.nomor_jurnal', 'jurnal.keterangan as ket_jurnal', 'jurnal_detail.keterangan as ket_detail', 'jurnal_detail.debit', 'jurnal_detail.kredit')
                ->get();

            // saldo tampil mengikuti sisi saldo normal akun
            $arah = $coa->saldo_normal === 'debit' ? 1 : -1;
            $saldo = $saldoAwal;

            $rows = [['cells' => [Carbon::parse($dari)->format('d/m/Y'), '', 'Saldo Awal', '', '', $this->fmt($saldo * $arah)], 'bold' => true]];
            $totalDebit = 0;
            $totalKredit = 0;
            foreach ($mutasi as $m) {
                $saldo += floatval($m->debit) - floatval($m->kredit);
                $totalDebit += floatval($m->debit);
                $totalKredit += floatval($m->kredit);
                $rows[] = [
                    Carbon::parse($m->tanggal)->format('d/m/Y'),
                    $m->nomor_jurnal,
                    $m->ket_detail ?: $m->ket_jurnal,
                    floatval($m->debit) > 0 ? $this->fmt($m->debit) : '',
                    floatval($m->kredit) > 0 ? $this->fmt($m->kredit) : '',
                    $this->fmt($saldo * $arah),
                ];
            }
            $rows[] = ['cells' => ['', '', 'Saldo Akhir', $this->fmt($totalDebit), $this->fmt($totalKredit), $this->fmt($saldo * $arah)], 'bold' => true];

            $summary = [
                ['label' => 'Akun', 'value' => $coa->kode_akun . ' — ' . $coa->nama_akun],
                ['label' => 'Periode', 'value' => Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($sampai)->format('d/m/Y')],
                ['label' => 'Saldo Normal', 'value' => ucfirst($coa->saldo_normal)],
                ['label' => 'Saldo Awal', 'value' => $this->fmt($saldoAwal * $arah)],
                ['label' => 'Mutasi', 'value' => 'D ' . $this->fmt($totalDebit) . ' / K ' . $this->fmt($totalKredit)],
                ['label' => 'Saldo Akhir', 'value' => $this->fmt($saldo * $arah)],
            ];
            $tables = [[
                'columns' => ['Tanggal', 'No. Jurnal', 'Keterangan', 'Debit', 'Kredit', 'Saldo'],
                'alignRight' => [3, 4, 5],
                'rows' => $rows,
            ]];
        } else {
            $notice = ['text' => 'Pilih akun lalu klik Tampilkan untuk melihat buku besar.', 'color' => 'info'];
        }

        return Inertia::render('Financial/Laporan', [
            'title' => 'Buku Besar',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'action' => route('buku-besar.index'),
            'filters' => [
                ['name' => 'coa_id', 'label' => 'Akun', 'type' => 'select', 'value' => $coaId,
                    'options' => $akun->map(fn ($a) => ['value' => $a->coa_id, 'label' => $a->kode_akun . ' — ' . $a->nama_akun])->values()->all()],
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $spbuId, 'allowAll' => true,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all()],
                ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date', 'value' => $dari],
                ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date', 'value' => $sampai],
            ],
            'summary' => $summary,
            'tables' => $tables,
            'notice' => $notice,
        ]);
    }

    private function fmt(float $n): string
    {
        return number_format($n, 2, ',', '.');
    }
}
