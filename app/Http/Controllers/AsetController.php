<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsetRequest;
use App\Models\Aset;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use App\Models\Spbu;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Aset::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('aset.show', $row->aset_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('aset.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Aset', 'data' => 'nama_aset'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
                    ['label' => 'Keterangan', 'data' => 'keterangan'],
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
            'title' => 'Tambah Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')]],
            'backUrl' => route('aset.index'),
            'method' => 'post',
            'action' => route('aset.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_aset', 'label' => 'Nama Aset', 'type' => 'text', 'value' => null],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'harga', 'label' => 'Harga', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'tanggal_digunakan', 'label' => 'Tanggal Digunakan', 'type' => 'date', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'penyusutan_per_hari', 'label' => 'Penyusutan per Hari', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'is_digunakan', 'label' => 'Status Penggunaan', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Digunakan'], ['value' => 'false', 'label' => 'Tidak digunakan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AsetRequest $asetRequest)
    {
        $spbu = Spbu::find($asetRequest->spbu_id);

        $aset = $spbu->aset()->where('nama_aset', request('nama_aset'))->where('jumlah', request('jumlah'))->where('tanggal_digunakan', request('tanggal_digunakan'))->where('harga', str_replace(',', '', request('harga')))->where('penyusutan_per_hari', str_replace(',', '', request('penyusutan_per_hari')))->first();
        if (is_null($aset)) {
            $aset = $spbu->aset()->create($this->asetData());
        }

        return redirect('/master/aset')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Aset sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Aset $aset)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')]],
            'backUrl' => route('aset.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional(\Illuminate\Support\Facades\DB::table('spbu')->where('spbu_id', $aset->spbu_id)->first())->nama_spbu],
                ['label' => 'Nama Aset', 'value' => $aset->nama_aset],
                ['label' => 'Jumlah', 'value' => $aset->jumlah],
                ['label' => 'Harga', 'value' => $aset->harga],
                ['label' => 'Tanggal Digunakan', 'value' => $aset->tanggal_digunakan],
                ['label' => 'Penyusutan per Hari', 'value' => $aset->penyusutan_per_hari],
                ['label' => 'Status Penggunaan', 'value' => filter_var($aset->is_digunakan, FILTER_VALIDATE_BOOLEAN) ? 'Digunakan' : 'Tidak Digunakan'],
                ['label' => 'Keterangan', 'value' => $aset->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('aset.edit', $aset), 'color' => 'primary', 'icon' => 'pen'], ['label' => 'File', 'url' => route('aset.file.index', $aset), 'color' => 'info', 'icon' => 'folder-open']],
            'deleteUrl' => route('aset.destroy', $aset),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aset $aset)
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')]],
            'backUrl' => route('aset.index'),
            'method' => 'put',
            'action' => route('aset.update', $aset),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $aset->spbu_id, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_aset', 'label' => 'Nama Aset', 'type' => 'text', 'value' => $aset->nama_aset],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => $aset->jumlah, 'col' => 'col-md-6'],
                ['name' => 'harga', 'label' => 'Harga', 'type' => 'text', 'value' => $aset->harga, 'col' => 'col-md-6'],
                ['name' => 'tanggal_digunakan', 'label' => 'Tanggal Digunakan', 'type' => 'date', 'value' => $aset->tanggal_digunakan, 'col' => 'col-md-6'],
                ['name' => 'penyusutan_per_hari', 'label' => 'Penyusutan per Hari', 'type' => 'text', 'value' => $aset->penyusutan_per_hari, 'col' => 'col-md-6'],
                ['name' => 'is_digunakan', 'label' => 'Status Penggunaan', 'type' => 'select', 'value' => filter_var($aset->is_digunakan, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Digunakan'], ['value' => 'false', 'label' => 'Tidak digunakan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $aset->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AsetRequest $asetRequest, Aset $aset)
    {
        $aset->update($this->asetData());

        return redirect('/master/aset')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Aset sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Aset $aset)
    {
        $aset->delete();

        return redirect('/master/aset')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Aset sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function asetData()
    {
        return [
            'nama_aset' => request('nama_aset'),
            'jumlah' => request('jumlah'),
            'tanggal_digunakan' => request('tanggal_digunakan'),
            'harga' => str_replace(',', '', request('harga')),
            'penyusutan_per_hari' => str_replace(',', '', request('penyusutan_per_hari')),
            'is_digunakan' => request('is_digunakan') ? 1 : 0,
            'keterangan' => request('keterangan'),
        ];
    }
}
