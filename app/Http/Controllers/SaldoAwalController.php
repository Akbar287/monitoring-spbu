<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\Spbu;
use App\Support\Akun;
use App\Support\JurnalPosting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

/**
 * Input saldo awal (jurnal pembuka) per SPBU pada tanggal cut-over.
 * Disimpan sebagai jurnal tipe_sumber=saldo_awal dengan sumber_id=spbu_id,
 * sehingga tiap SPBU hanya punya satu jurnal pembuka; simpan ulang akan
 * mengganti jurnal lama. Selisih debit-kredit otomatis diseimbangkan ke
 * akun Modal Disetor.
 */
class SaldoAwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Spbu::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $jurnal = Jurnal::where('tipe_sumber', 'saldo_awal')->where('sumber_id', $row->spbu_id)->first();
                    return $jurnal
                        ? 'Sudah diinput (' . Carbon::parse($jurnal->tanggal)->format('d F Y') . ')'
                        : 'Belum diinput';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('saldo-awal.form', $row->spbu_id) . '" class="edit btn btn-success btn-sm">Input / Ubah</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return Inertia::render('Table/Index', [
            'title' => 'Saldo Awal',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'SPBU', 'data' => 'nama_spbu'],
                    ['label' => 'Nomor', 'data' => 'nomor_spbu'],
                    ['label' => 'Status Saldo Awal', 'data' => 'status', 'orderable' => false, 'searchable' => false],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function form(Spbu $spbu)
    {
        $jurnal = Jurnal::where('tipe_sumber', 'saldo_awal')->where('sumber_id', $spbu->spbu_id)->with('jurnal_detail')->first();

        // saldo tersimpan per akun, ditampilkan pada sisi saldo normalnya
        $tersimpan = [];
        if ($jurnal) {
            foreach ($jurnal->jurnal_detail as $d) {
                $tersimpan[$d->coa_id] = ($tersimpan[$d->coa_id] ?? 0) + floatval($d->debit) - floatval($d->kredit);
            }
        }

        $fields = [
            ['name' => 'tanggal', 'label' => 'Tanggal Saldo Awal (cut-over)', 'type' => 'date', 'value' => $jurnal ? Carbon::parse($jurnal->tanggal)->toDateString() : null, 'col' => 'col-md-6', 'hint' => 'Saldo per akhir hari sebelum pembukuan GL dimulai.'],
        ];
        foreach ($this->akunRiil() as $a) {
            $saldo = $tersimpan[$a->coa_id] ?? null;
            if (! is_null($saldo)) {
                $saldo = $a->saldo_normal === 'debit' ? $saldo : -$saldo;
            }
            $fields[] = [
                'name' => 'akun_' . $a->coa_id,
                'label' => $a->kode_akun . ' — ' . $a->nama_akun . ' (' . ucfirst($a->saldo_normal) . ')',
                'type' => 'number',
                'value' => is_null($saldo) ? null : round($saldo, 2),
                'col' => 'col-md-6',
            ];
        }

        return Inertia::render('Form/Index', [
            'title' => 'Saldo Awal ' . $spbu->nama_spbu,
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Saldo Awal', 'url' => route('saldo-awal.index')]],
            'backUrl' => route('saldo-awal.index'),
            'method' => 'put',
            'action' => route('saldo-awal.store', $spbu->spbu_id),
            'fields' => $fields,
        ]);
    }

    /**
     * Store the opening balance journal for the given SPBU.
     */
    public function store(Request $request, Spbu $spbu)
    {
        $request->validate(['tanggal' => ['required', 'date']]);

        $totalDebit = 0;
        $totalKredit = 0;
        $lines = [];
        foreach ($this->akunRiil() as $a) {
            $nilai = floatval(str_replace(',', '', $request->input('akun_' . $a->coa_id) ?? 0));
            if (abs($nilai) < 0.0001) {
                continue;
            }
            // nilai positif dicatat di sisi saldo normal akun; negatif di sisi lawan
            $sisiDebit = ($a->saldo_normal === 'debit') === ($nilai > 0);
            $jumlah = abs($nilai);
            $lines[] = [
                'kode_akun' => $a->kode_akun,
                'debit' => $sisiDebit ? $jumlah : 0,
                'kredit' => $sisiDebit ? 0 : $jumlah,
                'keterangan' => 'Saldo awal ' . $a->nama_akun,
            ];
            $totalDebit += $sisiDebit ? $jumlah : 0;
            $totalKredit += $sisiDebit ? 0 : $jumlah;
        }

        $selisih = $totalDebit - $totalKredit;
        if (abs($selisih) > 0.0001) {
            $lines[] = [
                'kode_akun' => Akun::MODAL_DISETOR,
                'debit' => $selisih < 0 ? abs($selisih) : 0,
                'kredit' => $selisih > 0 ? $selisih : 0,
                'keterangan' => 'Penyeimbang saldo awal (otomatis)',
            ];
        }

        DB::transaction(function () use ($spbu, $request, $lines) {
            JurnalPosting::batalkan('saldo_awal', $spbu->spbu_id);
            if (count($lines) >= 2) {
                JurnalPosting::post(
                    $spbu->spbu_id,
                    $request->input('tanggal'),
                    'saldo_awal',
                    $spbu->spbu_id,
                    'Saldo awal ' . $spbu->nama_spbu . ' per ' . Carbon::parse($request->input('tanggal'))->format('d/m/Y'),
                    $lines,
                    Auth::user()?->member()->first()?->member_id
                );
            }
        });

        $pesan = count($lines) >= 2 ? 'Saldo awal tersimpan sebagai jurnal pembuka.' : 'Saldo awal dikosongkan (jurnal pembuka dihapus).';
        return redirect(route('saldo-awal.index'))->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> ' . $pesan . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    private function akunRiil()
    {
        return Coa::whereIn('tipe_akun', ['aset', 'kewajiban', 'modal'])
            ->where('is_header', false)
            ->where('is_aktif', true)
            ->where('kode_akun', '!=', Akun::IKHTISAR_LABA_RUGI)
            ->orderBy('kode_akun')
            ->get();
    }
}
