<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Penarikan;
use App\Models\Penjualan;
use App\Models\StatusSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PenitipanSetoranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index_open_penjualan(Request $request)
    {
        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');

        if ($request->ajax()) {
            $data = StatusSetoran::where('nama_status', 'Penitipan Setoran')->first()->penjualan()->whereBetween('penjualan.tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('produk', function ($row) {
                    $actionBtn =  $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('dispenser', function ($row) {
                    $actionBtn =  $row->dispenser()->first()->nama_dispenser;
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn =  $row->jam_kerja()->first()->nama_jam_kerja;
                    return $actionBtn;
                })
                ->addColumn('total_penjualan', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_penjualan, 0, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.penitipan.show', $row->penjualan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('setoran/penitipan_setoran/index_open_penjualan', compact('waktuMulai', 'waktuSelesai'));
    }

    public function show_open_penjualan(Penjualan $penjualan)
    {
        $terkonfirm = $penjualan->penarikan()->where('is_accepted', false)->count() == 0;
        return view('setoran/penitipan_setoran/show_open_penjualan', compact('penjualan', 'terkonfirm'));
    }

    public function update_closing(Request $request, Penjualan $penjualan)
    {
        $status = StatusSetoran::where(['nama_status' => 'Closing Penjualan'])->first();
        $penjualan->update(['status_setoran_id' => $status->status_setoran_id]);

        return redirect('/setoran/penitipan-setoran')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function index(Request $request, Penjualan $penjualan)
    {
        if ($request->ajax()) {
            $data = $penjualan->penarikan()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn =  Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('nama_bank', function ($row) {
                    $actionBtn =  $row->nama_bank;
                    return $actionBtn;
                })
                ->addColumn('no_ref', function ($row) {
                    return $row->no_referensi;
                })
                ->addColumn('metode_transfer', function ($row) {
                    $actionBtn =  $row->metode_transfer;
                    return $actionBtn;
                })
                ->addColumn('total_uang', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_uang, 0, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('status', function ($row) {
                    $actionBtn = $row->is_accepted ? '<div class="badge badge-success">Terkonfirmasi</div>' : '<div class="badge badge-danger">Tidak Konfirmasi</div>';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.penitipan.show_penitipan', [$row->penjualan()->first()->penjualan_id, $row->penarikan_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('setoran/penitipan_setoran/index', compact('penjualan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penjualan $penjualan)
    {
        $member = Member::select('member.member_id')->addSelect('ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->get();
        return view('setoran/penitipan_setoran/create', compact('penjualan', 'member'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Penjualan $penjualan)
    {
        $request->validate([
            'tanggal' => ['required'],
            'member_id' => ['required'],
            'total_uang' => ['required'],
            'nama_bank' => ['required'],
            'no_referensi' => ['required'],
            'metode_transfer' => ['required'],
        ]);

        $penitipan = $penjualan->penarikan()->create($this->penitipanData());

        return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id . '/penitipan/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan, Penarikan $penitipan)
    {
        return view('setoran/penitipan_setoran/show', compact('penjualan', 'penitipan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penjualan $penjualan, Penarikan $penitipan)
    {
        $member = Member::select('member.member_id')->addSelect('ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->get();
        return view('setoran/penitipan_setoran/edit', compact('penjualan', 'penitipan', 'member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penjualan $penjualan, Penarikan $penitipan)
    {
        $request->validate([
            'tanggal' => ['required'],
            'member_id' => ['required'],
            'total_uang' => ['required'],
            'nama_bank' => ['required'],
            'no_referensi' => ['required'],
            'metode_transfer' => ['required'],
        ]);

        $penitipan->update($this->penitipanData());

        return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id . '/penitipan/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function update_confirmation(Request $request, Penjualan $penjualan, Penarikan $penitipan)
    {
        if ($penitipan->is_accepted) {
            $penitipan->update(['is_accepted' => false]);
            return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id . '/penitipan/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Konfirmasi Data Penitipan Setoran Sudah dibatalkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            $penitipan->update(['is_accepted' => true]);
            return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id . '/penitipan/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah di Konfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjualan $penjualan, Penarikan $penitipan)
    {
        $penitipan->delete();

        return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id . '/penitipan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function penitipanData()
    {
        return [
            'tanggal' => request('tanggal'),
            'member_id' => request('member_id'),
            'total_uang' => str_replace(',', '', request('total_uang')),
            'nama_bank' => request('nama_bank'),
            'no_referensi' => request('no_referensi'),
            'metode_transfer' => request('metode_transfer'),
            'is_accepted' => false
        ];
    }
}
