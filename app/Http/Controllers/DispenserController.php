<?php

namespace App\Http\Controllers;

use App\Http\Requests\DispenserRequest;
use App\Models\Dispenser;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use App\Models\Payung;
use Illuminate\Http\Request;

class DispenserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Dispenser::select('dispenser.*')->addSelect('payung.nama_payung')->join('payung', 'payung.payung_id', 'dispenser.payung_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = (int)$row->is_aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('dispenser.show', $row->dispenser_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Dispenser',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('dispenser.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Dispenser', 'data' => 'nama_dispenser'],
                    ['label' => 'Nama Payung', 'data' => 'nama_payung'],
                    ['label' => 'Aktif', 'data' => 'is_aktif'],
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
        $payung = Payung::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Dispenser',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dispenser', 'url' => route('dispenser.index')]],
            'backUrl' => route('dispenser.index'),
            'method' => 'post',
            'action' => route('dispenser.store'),
            'fields' => [
                ['name' => 'payung_id', 'label' => 'Payung', 'type' => 'select', 'value' => null, 'options' => $payung->map(fn ($x) => ['value' => $x->payung_id, 'label' => $x->nama_payung])->values()->all()],
                ['name' => 'nama_dispenser', 'label' => 'Nama Dispenser', 'type' => 'text', 'value' => null],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DispenserRequest $dispenserRequest)
    {
        $payung = Payung::find($dispenserRequest->payung_id);
        $dispenser = $payung->dispenser()->where('nama_dispenser', request('nama_dispenser'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') ? 1 : 0)->first();
        if (is_null($dispenser)) {
            $dispenser = $payung->dispenser()->create($this->dispenserData());
        }

        return redirect('/master/dispenser')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dispenser sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dispenser $dispenser)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Dispenser',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dispenser', 'url' => route('dispenser.index')]],
            'backUrl' => route('dispenser.index'),
            'fields' => [
                ['label' => 'Payung', 'value' => optional(\Illuminate\Support\Facades\DB::table('payung')->where('payung_id', $dispenser->payung_id)->first())->nama_payung],
                ['label' => 'Nama Dispenser', 'value' => $dispenser->nama_dispenser],
                ['label' => 'Status', 'value' => filter_var($dispenser->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $dispenser->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('dispenser.edit', $dispenser), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('dispenser.destroy', $dispenser),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dispenser $dispenser)
    {
        $payung = Payung::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Dispenser',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dispenser', 'url' => route('dispenser.index')]],
            'backUrl' => route('dispenser.index'),
            'method' => 'put',
            'action' => route('dispenser.update', $dispenser),
            'fields' => [
                ['name' => 'payung_id', 'label' => 'Payung', 'type' => 'select', 'value' => $dispenser->payung_id, 'options' => $payung->map(fn ($x) => ['value' => $x->payung_id, 'label' => $x->nama_payung])->values()->all()],
                ['name' => 'nama_dispenser', 'label' => 'Nama Dispenser', 'type' => 'text', 'value' => $dispenser->nama_dispenser],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($dispenser->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $dispenser->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DispenserRequest $dispenserRequest, Dispenser $dispenser)
    {
        $dispenser->update($this->dispenserData());

        return redirect('/master/dispenser')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dispenser sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dispenser $dispenser)
    {
        $dispenser->delete();

        return redirect('/master/dispenser')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dispenser sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function dispenserData()
    {
        return [
            'nama_dispenser' => request('nama_dispenser'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') ? 1 : 0,
        ];
    }
}
