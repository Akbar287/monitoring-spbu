<?php

namespace App\Http\Controllers;

use App\Http\Requests\KonversiRequest;
use App\Imports\KonversiImport;
use App\Models\Dombak;
use App\Models\Konversi;
use App\Models\SatuanUkurTinggi;
use App\Models\SatuanUkurVolume;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class KonversiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Dombak $dombak)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $dombak->konversi()->select('konversi.konversi_id', 'dombak.dombak_id', 'satuan_ukur_tinggi.nama_satuan as nama_satuan_tinggi', 'satuan_ukur_volume.nama_satuan as nama_satuan_volume', 'konversi.tinggi', 'konversi.volume')
                ->join('dombak', 'dombak.dombak_id', 'konversi.dombak_id')
                ->join('satuan_ukur_tinggi', 'satuan_ukur_tinggi.satuan_ukur_tinggi_id', 'konversi.satuan_ukur_tinggi_id')
                ->join('satuan_ukur_volume', 'satuan_ukur_volume.satuan_ukur_volume_id', 'konversi.satuan_ukur_volume_id')
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tinggi', function ($row) {
                    $actionBtn = $row->tinggi . ' (' . $row->nama_satuan_tinggi . ')';
                    return $actionBtn;
                })
                ->addColumn('volume', function ($row) {
                    $actionBtn = $row->volume . ' (' . $row->nama_satuan_volume . ')';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('konversi.show', [$row->dombak_id, $row->konversi_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Konversi Dombak',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')], ['label' => $dombak->nama_dombak, 'url' => route('dombak.show', $dombak)]],
            'createUrl' => route('konversi.create', $dombak),
            'headerActions' => [
                ['label' => 'Upload', 'url' => route('konversi.upload_show', $dombak), 'color' => 'info', 'icon' => 'upload'],
                ['label' => 'Hapus Semua', 'url' => route('konversi.destroy.all', $dombak), 'method' => 'delete', 'color' => 'danger', 'icon' => 'trash', 'confirm' => 'Yakin menghapus SEMUA data konversi dombak ini?'],
            ],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tinggi', 'data' => 'tinggi'],
                    ['label' => 'Volume', 'data' => 'volume'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Dombak $dombak)
    {
        $satuanUkurTinggi = SatuanUkurTinggi::all();
        $satuanUkurVolume = SatuanUkurVolume::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Konversi',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')], ['label' => 'Konversi', 'url' => route('konversi.index', $dombak)]],
            'backUrl' => route('konversi.index', $dombak),
            'method' => 'post',
            'action' => route('konversi.store', $dombak),
            'fields' => [
                ['name' => 'tinggi', 'label' => 'Tinggi', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'satuan_ukur_tinggi_id', 'label' => 'Satuan Tinggi', 'type' => 'select', 'value' => null, 'col' => 'col-md-6', 'options' => $satuanUkurTinggi->map(fn ($x) => ['value' => $x->satuan_ukur_tinggi_id, 'label' => $x->nama_satuan . ' (' . $x->singkatan . ')'])->values()->all()],
                ['name' => 'volume', 'label' => 'Volume', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'satuan_ukur_volume_id', 'label' => 'Satuan Volume', 'type' => 'select', 'value' => null, 'col' => 'col-md-6', 'options' => $satuanUkurVolume->map(fn ($x) => ['value' => $x->satuan_ukur_volume_id, 'label' => $x->nama_satuan . ' (' . $x->singkatan . ')'])->values()->all()],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KonversiRequest $konversiRequest, Dombak $dombak)
    {

        $konversi = $dombak->konversi()->where('satuan_ukur_tinggi_id', $konversiRequest->satuan_ukur_tinggi_id != null ? $konversiRequest->satuan_ukur_tinggi_id : request('satuan_ukur_tinggi_id'))->where('satuan_ukur_volume_id', $konversiRequest->satuan_ukur_volume_id != null ? $konversiRequest->satuan_ukur_volume_id : request('satuan_ukur_volume_id'))->where('tinggi', str_replace(',', '', request('tinggi')))->where('volume', str_replace(',', '', request('volume')))->first();
        if (is_null($konversi)) {
            $konversi = $dombak->konversi()->create($this->konversiData($konversiRequest->satuan_ukur_tinggi_id, $konversiRequest->satuan_ukur_volume_id));
        }

        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Konversi sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dombak $dombak, Konversi $konversi)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Konversi',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')], ['label' => 'Konversi', 'url' => route('konversi.index', $dombak)]],
            'backUrl' => route('konversi.index', $dombak),
            'fields' => [
                ['label' => 'Dombak', 'value' => $dombak->nama_dombak],
                ['label' => 'Tinggi', 'value' => $konversi->tinggi],
                ['label' => 'Satuan Tinggi', 'value' => optional(\Illuminate\Support\Facades\DB::table('satuan_ukur_tinggi')->where('satuan_ukur_tinggi_id', $konversi->satuan_ukur_tinggi_id)->first())->nama_satuan],
                ['label' => 'Volume', 'value' => $konversi->volume],
                ['label' => 'Satuan Volume', 'value' => optional(\Illuminate\Support\Facades\DB::table('satuan_ukur_volume')->where('satuan_ukur_volume_id', $konversi->satuan_ukur_volume_id)->first())->nama_satuan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('konversi.edit', [$dombak, $konversi]), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('konversi.destroy', [$dombak, $konversi]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dombak $dombak, Konversi $konversi)
    {
        $satuanUkurTinggi = SatuanUkurTinggi::all();
        $satuanUkurVolume = SatuanUkurVolume::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Konversi',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')], ['label' => 'Konversi', 'url' => route('konversi.index', $dombak)]],
            'backUrl' => route('konversi.index', $dombak),
            'method' => 'put',
            'action' => route('konversi.update', [$dombak, $konversi]),
            'fields' => [
                ['name' => 'tinggi', 'label' => 'Tinggi', 'type' => 'text', 'value' => $konversi->tinggi, 'col' => 'col-md-6'],
                ['name' => 'satuan_ukur_tinggi_id', 'label' => 'Satuan Tinggi', 'type' => 'select', 'value' => $konversi->satuan_ukur_tinggi_id, 'col' => 'col-md-6', 'options' => $satuanUkurTinggi->map(fn ($x) => ['value' => $x->satuan_ukur_tinggi_id, 'label' => $x->nama_satuan . ' (' . $x->singkatan . ')'])->values()->all()],
                ['name' => 'volume', 'label' => 'Volume', 'type' => 'text', 'value' => $konversi->volume, 'col' => 'col-md-6'],
                ['name' => 'satuan_ukur_volume_id', 'label' => 'Satuan Volume', 'type' => 'select', 'value' => $konversi->satuan_ukur_volume_id, 'col' => 'col-md-6', 'options' => $satuanUkurVolume->map(fn ($x) => ['value' => $x->satuan_ukur_volume_id, 'label' => $x->nama_satuan . ' (' . $x->singkatan . ')'])->values()->all()],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Dombak $dombak, KonversiRequest $konversiRequest, Konversi $konversi)
    {
        $konversi->update($this->konversiData($konversiRequest->satuan_ukur_tinggi_id, $konversiRequest->satuan_ukur_volume_id));

        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Konversi sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dombak $dombak, Konversi $konversi)
    {
        $konversi->delete();

        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Konversi sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function destroy_all(Dombak $dombak)
    {
        $dombak->konversi()->delete();

        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Semua Data Konversi sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function konversiData($satuan_ukur_tinggi_id = null, $satuan_ukur_volume_id = null)
    {
        return [
            'satuan_ukur_tinggi_id' => $satuan_ukur_tinggi_id != null ? $satuan_ukur_tinggi_id : request('satuan_ukur_tinggi_id'),
            'satuan_ukur_volume_id' => $satuan_ukur_volume_id != null ? $satuan_ukur_volume_id : request('satuan_ukur_volume_id'),
            'tinggi' => str_replace(',', '', request('tinggi')),
            'volume' => str_replace(',', '', request('volume')),
        ];
    }

    public function upload_show(Dombak $dombak)
    {
        $satuanUkurTinggi = SatuanUkurTinggi::all();
        $satuanUkurVolume = SatuanUkurVolume::all();
        return Inertia::render('Form/Index', [
            'title' => 'Upload Konversi',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')], ['label' => 'Konversi', 'url' => route('konversi.index', $dombak)]],
            'backUrl' => route('konversi.index', $dombak),
            'method' => 'post',
            'action' => route('konversi.upload_store', $dombak),
            'fields' => [
                ['name' => 'satuan_ukur_tinggi_id', 'label' => 'Satuan Tinggi', 'type' => 'select', 'value' => null, 'col' => 'col-md-6', 'options' => $satuanUkurTinggi->map(fn ($x) => ['value' => $x->satuan_ukur_tinggi_id, 'label' => $x->nama_satuan . ' (' . $x->singkatan . ')'])->values()->all()],
                ['name' => 'satuan_ukur_volume_id', 'label' => 'Satuan Volume', 'type' => 'select', 'value' => null, 'col' => 'col-md-6', 'options' => $satuanUkurVolume->map(fn ($x) => ['value' => $x->satuan_ukur_volume_id, 'label' => $x->nama_satuan . ' (' . $x->singkatan . ')'])->values()->all()],
                ['name' => 'upload', 'label' => 'File Excel', 'type' => 'file', 'value' => null, 'accept' => '.xlsx,.xls,.csv', 'hint' => 'Kolom: tinggi & volume. Format: .xlsx, .xls, .csv'],
            ],
        ]);
    }

    public function upload_store(Request $request, Dombak $dombak)
    {
        $request->validate([
            'satuan_ukur_tinggi_id' => ['required'],
            'satuan_ukur_volume_id' => ['required'],
        ]);

        $path1 = 'temp/' . $request->file('upload')->hashName();
        $name = (explode('.', $path1));

        if (count($name) == 1) {
            $name[] = 'xls';
        }

        $name = (implode('.', $name));
        $request->file('upload')->storeAs('temp', $name);
        $path = storage_path('app') . '/temp/' . $name;
        $name_file[] = $name;
        $rows = Excel::toArray(new KonversiImport, $path);

        unset($rows[0][0]);

        foreach ($rows[0] as $row) {
            $dombak->konversi()->create([
                'satuan_ukur_tinggi_id' => $request->satuan_ukur_tinggi_id,
                'satuan_ukur_volume_id' => $request->satuan_ukur_volume_id,
                'tinggi' => $row[0],
                'volume' => $row[1],
            ]);

            if ($dombak->konversi()->where('satuan_ukur_tinggi_id', $request->satuan_ukur_tinggi_id)->where('satuan_ukur_volume_id', $request->satuan_ukur_volume_id)->where('tinggi', $row[0])->where('volume', $row[1])->count() > 1) {
                $dombak->konversi()->where('satuan_ukur_tinggi_id', $request->satuan_ukur_tinggi_id)->where('satuan_ukur_volume_id', $request->satuan_ukur_volume_id)->where('tinggi', $row[0])->where('volume', $row[1])->first()->delete();
            }
        }

        Storage::delete('temp/' . $name);
        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Konversi sudah diupload dan ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function api(Request $request)
    {
        $request->validate([
            'dombak_id' => ['required'],
            'tinggi' => ['required'],
        ]);

        $data = null;

        $dombak = Dombak::where('dombak_id', $request->dombak_id)->first();

        if (is_null($dombak)) {
            return response()->json([
                'status' => 404,
                'message' => 'dombak not found',
                'data' => []
            ], 404);
        }

        $data = $dombak->konversi()->select('tinggi')->addSelect('volume')->where('tinggi', $request->tinggi)->first();

        return response()->json([
            'status' => 200,
            'message' => 'converting successfully',
            'data' => [
                'tinggi' => number_format((is_null($data)) ? 0 : $data->tinggi, 6, ".", ","),
                'volume' => number_format((is_null($data)) ? 0 : $data->volume, 6, ".", ","),
            ]
        ], 200);
    }
}
