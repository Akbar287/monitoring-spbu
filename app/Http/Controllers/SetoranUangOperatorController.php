<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Penarikan;
use App\Models\Penjualan;
use App\Models\StatusSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SetoranUangOperatorController extends Controller
{
    public function index_penjualan(Request $request, Penjualan $penjualan)
    {
        $statusSetoran = StatusSetoran::select('status_setoran_id', 'nama_status')->get();
        $statusSetoranSelected = $request->status_setoran_id ?? StatusSetoran::where('nama_status', 'Setoran Ke Bank')->first()->status_setoran_id;
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
            if ($statusSetoranSelected == 'all') {
                $data = Penjualan::whereBetween('penjualan.tanggal', [$waktuMulai, $waktuSelesai])->get();
            } else {
                $data = StatusSetoran::where('status_setoran_id', $statusSetoranSelected)->first()->penjualan()->whereBetween('penjualan.tanggal', [$waktuMulai, $waktuSelesai])->get();
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return $row->status_setoran()->first()->nama_status == 'Selesai' ? '<div class="badge badge-success">Selesai</div>' : '<div class="badge badge-primary">' . $row->status_setoran()->first()->nama_status . '</div>';
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn =  $row->jam_kerja()->first()->nama_jam_kerja;
                    return $actionBtn;
                })
                ->addColumn('stand_meter', function ($row) {
                    $actionBtn = array_column($row->detail_penjualan()->select('nozzle.nama_nozzle')->join('stand_meter', 'stand_meter.stand_meter_id', 'detail_penjualan.stand_meter_id')->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->orderBy('nozzle.nama_nozzle', 'asc')->get()->toArray(), 'nama_nozzle');
                    return count($actionBtn) == 0 ? '-' : (count($actionBtn) > 1 ? implode(', ', $actionBtn) : $actionBtn['0']);
                })
                ->addColumn('total_uang_penjualan', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_penjualan, 0, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.setoran_bank.show_setoran_bank_penjualan', $row->penjualan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('setoran/setor_bank/index_open_penjualan', compact('waktuMulai', 'waktuSelesai', 'penjualan', 'statusSetoran', 'statusSetoranSelected'));
    }

    /**
     * Display the specified resource.
     */
    public function show_penjualan(Penjualan $penjualan)
    {
        return view('setoran/setor_bank/show_open_penjualan', compact('penjualan'));
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
                    $actionBtn = '<a href="' . route('setoran.setoran_bank.bank.show', [$row->penjualan()->first()->penjualan_id, $row->penarikan_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('setoran/setor_bank/index', compact('penjualan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penjualan $penjualan)
    {
        $member = Member::select('member.member_id')->addSelect('ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->orderBy('ktp.nama')->get();
        return view('setoran/setor_bank/create', compact('penjualan', 'member'));
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

        $penitipan = $penjualan->penarikan()->where('tanggal', request('tanggal'))->where('member_id', request('member_id'))->where('total_uang', str_replace(',', '', request('total_uang')))->where('nama_bank', request('nama_bank'))->where('no_referensi', request('no_referensi'))->where('metode_transfer', request('metode_transfer'))->where('is_accepted', false)->first();

        if (is_null($penitipan)) {
            $penitipan = $penjualan->penarikan()->create($this->penitipanData());
        }

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan, Penarikan $penitipan)
    {
        return view('setoran/setor_bank/show', compact('penjualan', 'penitipan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penjualan $penjualan, Penarikan $penitipan)
    {
        $member = Member::select('member.member_id')->addSelect('ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->get();
        return view('setoran/setor_bank/edit', compact('penjualan', 'penitipan', 'member'));
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

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjualan $penjualan, Penarikan $penitipan)
    {
        $penitipan->delete();

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function update_status_top_0x002(Penjualan $penjualan)
    {
        $penjualan->update([
            'is_accepted_top' =>  false
        ]);

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Sukses. Top Manajemen telah Membatalkan Konfirmasi Setoran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function update_status_top_0x003(Penjualan $penjualan)
    {
        $penjualan->update([
            'is_accepted_top' => true
        ]);
        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Sukses. Top Manajemen telah Mengonfirmasi Setoran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function update_status_admin_0x002(Penjualan $penjualan)
    {
        $penjualan->update([
            'is_accepted_admin' =>  false
        ]);

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Sukses. Admin telah Membatalkan Konfirmasi Setoran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function update_status_admin_0x003(Penjualan $penjualan)
    {
        $penjualan->update([
            'is_accepted_admin' =>  true
        ]);

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Sukses. Admin telah Mengonfirmasi Setoran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function check(Penjualan $penjualan)
    {
        $status_setoran = StatusSetoran::where('nama_status', 'Selesai')->first();

        $penjualan->update([
            'status_setoran_id' => $status_setoran->status_setoran_id
        ]);

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Selesai.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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

    public function update_confirmation_0x002(Request $request, Penjualan $penjualan, Penarikan $penitipan)
    {
        $penitipan->update(['is_accepted' => false]);
        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Konfirmasi Data Bank Sudah dibatalkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function update_confirmation_0x003(Request $request, Penjualan $penjualan, Penarikan $penitipan)
    {
        $penitipan->update(['is_accepted' => true]);
        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Bank Sudah di Konfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
