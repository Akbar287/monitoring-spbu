<?php

namespace App\Http\Controllers;

use App\Http\Requests\JamKerjaRequest;
use App\Models\JamKerja;
use App\Models\Spbu;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class JamKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = JamKerja::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('jam_kerja.show', $row->jam_kerja_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Jam Kerja',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('jam_kerja.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Jam Kerja', 'data' => 'nama_jam_kerja'],
                    ['label' => 'Batas Awal', 'data' => 'jam_datang_in'],
                    ['label' => 'Batas Akhir', 'data' => 'jam_pulang_out'],
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
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Jam Kerja',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Jam Kerja', 'url' => route('jam_kerja.index')]],
            'backUrl' => route('jam_kerja.index'),
            'method' => 'post',
            'action' => route('jam_kerja.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_jam_kerja', 'label' => 'Nama Jam Kerja', 'type' => 'text', 'value' => null],
                ['name' => 'batas_awal_in', 'label' => 'Batas Awal Masuk', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'batas_akhir_in', 'label' => 'Batas Akhir Masuk', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'jam_datang_in', 'label' => 'Jam Datang', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'jam_pulang_out', 'label' => 'Jam Pulang', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'akhir_out', 'label' => 'Batas Akhir Pulang', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'jam_istirahat', 'label' => 'Jam Istirahat', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'masuk_istirahat', 'label' => 'Masuk Istirahat', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'batas_telat', 'label' => 'Batas Telat', 'type' => 'time', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JamKerjaRequest $jamKerjaRequest)
    {
        $spbu = Spbu::find($jamKerjaRequest->spbu_id);

        $jamKerja = $spbu->jam_kerja()->where("spbu_id", request('spbu_id'))->where("nama_jam_kerja", request('nama_jam_kerja'))->where("batas_awal_in", request('batas_awal_in'))->where("batas_akhir_in", request('batas_akhir_in'))->where("jam_datang_in", request('jam_datang_in'))->where("jam_pulang_out", request('jam_pulang_out'))->where("akhir_out", request('akhir_out'))->where("jam_istirahat", request('jam_istirahat'))->where("masuk_istirahat", request('masuk_istirahat'))->where("batas_telat", request('batas_telat'))->where("urutan", request('urutan'))->first();
        if (is_null($jamKerja)) {
            $jamKerja = $spbu->jam_kerja()->create($this->jamKerjaData());
        }

        return redirect('/konfigurasi/jam_kerja')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jam Kerja sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(JamKerja $jamKerja)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Jam Kerja',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Jam Kerja', 'url' => route('jam_kerja.index')]],
            'backUrl' => route('jam_kerja.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional(\Illuminate\Support\Facades\DB::table('spbu')->where('spbu_id', $jamKerja->spbu_id)->first())->nama_spbu],
                ['label' => 'Nama Jam Kerja', 'value' => $jamKerja->nama_jam_kerja],
                ['label' => 'Batas Awal Masuk', 'value' => $jamKerja->batas_awal_in],
                ['label' => 'Batas Akhir Masuk', 'value' => $jamKerja->batas_akhir_in],
                ['label' => 'Jam Datang', 'value' => $jamKerja->jam_datang_in],
                ['label' => 'Jam Pulang', 'value' => $jamKerja->jam_pulang_out],
                ['label' => 'Batas Akhir Pulang', 'value' => $jamKerja->akhir_out],
                ['label' => 'Jam Istirahat', 'value' => $jamKerja->jam_istirahat],
                ['label' => 'Masuk Istirahat', 'value' => $jamKerja->masuk_istirahat],
                ['label' => 'Batas Telat', 'value' => $jamKerja->batas_telat],
                ['label' => 'Urutan', 'value' => $jamKerja->urutan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('jam_kerja.edit', $jamKerja), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('jam_kerja.destroy', $jamKerja),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JamKerja $jamKerja)
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Jam Kerja',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Jam Kerja', 'url' => route('jam_kerja.index')]],
            'backUrl' => route('jam_kerja.index'),
            'method' => 'put',
            'action' => route('jam_kerja.update', $jamKerja),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $jamKerja->spbu_id, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_jam_kerja', 'label' => 'Nama Jam Kerja', 'type' => 'text', 'value' => $jamKerja->nama_jam_kerja],
                ['name' => 'batas_awal_in', 'label' => 'Batas Awal Masuk', 'type' => 'time', 'value' => $jamKerja->batas_awal_in, 'col' => 'col-md-6'],
                ['name' => 'batas_akhir_in', 'label' => 'Batas Akhir Masuk', 'type' => 'time', 'value' => $jamKerja->batas_akhir_in, 'col' => 'col-md-6'],
                ['name' => 'jam_datang_in', 'label' => 'Jam Datang', 'type' => 'time', 'value' => $jamKerja->jam_datang_in, 'col' => 'col-md-6'],
                ['name' => 'jam_pulang_out', 'label' => 'Jam Pulang', 'type' => 'time', 'value' => $jamKerja->jam_pulang_out, 'col' => 'col-md-6'],
                ['name' => 'akhir_out', 'label' => 'Batas Akhir Pulang', 'type' => 'time', 'value' => $jamKerja->akhir_out, 'col' => 'col-md-6'],
                ['name' => 'jam_istirahat', 'label' => 'Jam Istirahat', 'type' => 'time', 'value' => $jamKerja->jam_istirahat, 'col' => 'col-md-6'],
                ['name' => 'masuk_istirahat', 'label' => 'Masuk Istirahat', 'type' => 'time', 'value' => $jamKerja->masuk_istirahat, 'col' => 'col-md-6'],
                ['name' => 'batas_telat', 'label' => 'Batas Telat', 'type' => 'time', 'value' => $jamKerja->batas_telat, 'col' => 'col-md-6'],
                ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number', 'value' => $jamKerja->urutan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JamKerjaRequest $jamKerjaRequest, JamKerja $jamKerja)
    {
        $jamKerja->update($this->jamKerjaData());

        return redirect('/konfigurasi/jam_kerja')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jam Kerja sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JamKerja $jamKerja)
    {
        $jamKerja->delete();

        return redirect('/konfigurasi/jam_kerja')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jam Kerja sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function jamKerjaData()
    {
        return [
            "spbu_id" => request('spbu_id'),
            "nama_jam_kerja" => request('nama_jam_kerja'),
            "batas_awal_in" => request('batas_awal_in'),
            "batas_akhir_in" => request('batas_akhir_in'),
            "jam_datang_in" => request('jam_datang_in'),
            "jam_pulang_out" => request('jam_pulang_out'),
            "akhir_out" => request('akhir_out'),
            "jam_istirahat" => request('jam_istirahat'),
            "masuk_istirahat" => request('masuk_istirahat'),
            "batas_telat" => request('batas_telat'),
            "urutan" => request('urutan')
        ];
    }
}
