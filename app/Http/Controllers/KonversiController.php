<?php

namespace App\Http\Controllers;

use App\Http\Requests\KonversiRequest;
use App\Imports\KonversiImport;
use App\Models\Dombak;
use App\Models\Konversi;
use App\Models\SatuanUkurTinggi;
use App\Models\SatuanUkurVolume;
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
        if ($request->ajax()) {
            $data = $dombak->konversi()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tinggi', function ($row) {
                    $actionBtn = $row->tinggi . ' (' . $row->satuan_ukur_tinggi()->first()->nama_satuan . ')';
                    return $actionBtn;
                })
                ->addColumn('volume', function ($row) {
                    $actionBtn = $row->volume . ' (' . $row->satuan_ukur_volume()->first()->nama_satuan . ')';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('konversi.show', [$row->dombak()->first()->dombak_id, $row->konversi_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/konversi/index', compact('dombak'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Dombak $dombak)
    {
        $satuanUkurTinggi = SatuanUkurTinggi::all();
        $satuanUkurVolume = SatuanUkurVolume::all();
        return view('master/konversi/create', compact('dombak', 'satuanUkurTinggi', 'satuanUkurVolume'));
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

        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi/' . $konversi->konversi_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Konversi sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dombak $dombak, Konversi $konversi)
    {
        return view('master/konversi/show', compact('dombak', 'konversi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dombak $dombak, Konversi $konversi)
    {
        $satuanUkurTinggi = SatuanUkurTinggi::all();
        $satuanUkurVolume = SatuanUkurVolume::all();
        return view('master/konversi/edit', compact('dombak', 'konversi', 'satuanUkurTinggi', 'satuanUkurVolume'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Dombak $dombak, KonversiRequest $konversiRequest, Konversi $konversi)
    {
        $konversi->update($this->konversiData($konversiRequest->satuan_ukur_tinggi_id, $konversiRequest->satuan_ukur_volume_id));

        return redirect('/master/dombak/' . $dombak->dombak_id . '/konversi/' . $konversi->konversi_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Konversi sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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
        return view('master/konversi/upload_show', compact('dombak', 'satuanUkurTinggi', 'satuanUkurVolume'));
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
            $konversi = new Konversi();
            $konversi->satuan_ukur_tinggi_id = $request->satuan_ukur_tinggi_id;
            $konversi->satuan_ukur_volume_id = $request->satuan_ukur_volume_id;
            $konversi->dombak_id = $dombak->dombak_id;
            $konversi->tinggi = $row[0];
            $konversi->volume = $row[1];
            $konversi->save();
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
                'tinggi' => number_format((is_null($data)) ? 0 : $data->tinggi, 2, ".", ","),
                'volume' => number_format((is_null($data)) ? 0 : $data->volume, 2, ".", ","),
            ]
        ], 200);
    }
}
