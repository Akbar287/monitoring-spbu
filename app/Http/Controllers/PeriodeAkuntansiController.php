<?php

namespace App\Http\Controllers;

use App\Models\PeriodeAkuntansi;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

/**
 * Tutup buku bulanan per SPBU. Periode yang ditutup menolak posting dan
 * pembatalan jurnal (ditegakkan oleh JurnalPosting::pastikanPeriodeTerbuka),
 * sehingga laporan periode itu terkunci dari perubahan.
 */
class PeriodeAkuntansiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = PeriodeAkuntansi::with('spbu')->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_spbu', fn ($row) => optional($row->spbu)->nama_spbu)
                ->addColumn('periode', fn ($row) => str_pad($row->bulan, 2, '0', STR_PAD_LEFT) . '-' . $row->tahun)
                ->addColumn('status', fn ($row) => $row->is_closed ? 'Ditutup' : 'Terbuka')
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('periode-akuntansi.show', $row->periode_akuntansi_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return Inertia::render('Table/Index', [
            'title' => 'Tutup Buku',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'createUrl' => route('periode-akuntansi.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'SPBU', 'data' => 'nama_spbu'],
                    ['label' => 'Periode', 'data' => 'periode'],
                    ['label' => 'Status', 'data' => 'status'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::get();
        return Inertia::render('Form/Index', [
            'title' => 'Tutup Periode',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Tutup Buku', 'url' => route('periode-akuntansi.index')]],
            'backUrl' => route('periode-akuntansi.index'),
            'method' => 'post',
            'action' => route('periode-akuntansi.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'periode', 'label' => 'Periode (bulan)', 'type' => 'month', 'value' => Carbon::now()->subMonthNoOverflow()->format('Y-m'), 'col' => 'col-md-6',
                    'hint' => 'Setelah ditutup, jurnal pada bulan ini tidak bisa ditambah/dibatalkan sampai dibuka kembali.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'periode' => ['required', 'date_format:Y-m'],
        ]);
        $periode = Carbon::createFromFormat('Y-m', $request->periode);

        PeriodeAkuntansi::updateOrCreate(
            ['spbu_id' => $request->spbu_id, 'tahun' => $periode->year, 'bulan' => $periode->month],
            ['is_closed' => true, 'closed_at' => now(), 'closed_by' => Auth::user()?->member()->first()?->member_id]
        );

        return redirect(route('periode-akuntansi.index'))->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Periode ' . $periode->format('m-Y') . ' sudah ditutup.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(PeriodeAkuntansi $periode)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Periode',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Tutup Buku', 'url' => route('periode-akuntansi.index')]],
            'backUrl' => route('periode-akuntansi.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional($periode->spbu)->nama_spbu],
                ['label' => 'Periode', 'value' => str_pad($periode->bulan, 2, '0', STR_PAD_LEFT) . '-' . $periode->tahun],
                ['label' => 'Status', 'value' => $periode->is_closed ? 'Ditutup' : 'Terbuka'],
                ['label' => 'Ditutup Pada', 'value' => $periode->closed_at ? Carbon::parse($periode->closed_at)->format('d F Y H:i') : '-'],
            ],
            'actions' => [
                $periode->is_closed
                    ? ['label' => 'Buka Kembali', 'url' => route('periode-akuntansi.toggle', $periode), 'color' => 'warning', 'icon' => 'lock-open', 'method' => 'put',
                        'confirm' => 'Buka kembali periode ini? Jurnal pada bulan ini akan bisa diubah lagi.']
                    : ['label' => 'Tutup Periode', 'url' => route('periode-akuntansi.toggle', $periode), 'color' => 'danger', 'icon' => 'lock', 'method' => 'put',
                        'confirm' => 'Tutup periode ini? Jurnal pada bulan ini tidak akan bisa ditambah/dibatalkan.'],
            ],
        ]);
    }

    /**
     * Toggle the closed state of the specified resource.
     */
    public function toggle(PeriodeAkuntansi $periode)
    {
        $tutup = ! $periode->is_closed;
        $periode->update([
            'is_closed' => $tutup,
            'closed_at' => $tutup ? now() : null,
            'closed_by' => $tutup ? Auth::user()?->member()->first()?->member_id : null,
        ]);

        return redirect(route('periode-akuntansi.index'))->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Periode ' . ($tutup ? 'ditutup' : 'dibuka kembali') . '.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
