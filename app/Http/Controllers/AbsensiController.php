<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\Penjadwalan;
use App\Models\StatusKehadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statusKehadiran = StatusKehadiran::select('status_kehadiran_id', 'nama_kehadiran')->orderBy('nama_kehadiran', 'asc')->get();
        $statusKehadiranSelected = $request->status_kehadiran_id ?? 'all';
        $jamKerja = JamKerja::select('jam_kerja_id', 'nama_jam_kerja')->orderBy('nama_jam_kerja', 'asc')->get();
        $jamKerjaSelected = $request->jam_kerja_id ?? 'all';
        $jabatan = Jabatan::select('jabatan_id', 'nama_jabatan')->orderBy('nama_jabatan', 'asc')->get();
        $jabatanSelected = $request->jabatan_id ?? 'all';
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
            $data = null;
            if ($statusKehadiranSelected === 'all') {
                if ($jamKerjaSelected === 'all') {
                    if ($jabatanSelected === 'all') {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    } else {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('jabatan.jabatan_id', $jabatanSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    }
                } else {
                    if ($jabatanSelected === 'all') {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('jam_kerja.jam_kerja_id', $jamKerjaSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    } else {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('jabatan.jabatan_id', $jabatanSelected)
                            ->where('jam_kerja.jam_kerja_id', $jamKerjaSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    }
                }
            } else {
                if ($jamKerjaSelected === 'all') {
                    if ($jabatanSelected === 'all') {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('status_kehadiran.status_kehadiran_id', $statusKehadiranSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    } else {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('status_kehadiran.status_kehadiran_id', $statusKehadiranSelected)
                            ->where('jabatan.jabatan_id', $jabatanSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    }
                } else {
                    if ($jabatanSelected === 'all') {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('status_kehadiran.status_kehadiran_id', $statusKehadiranSelected)
                            ->where('jam_kerja.jam_kerja_id', $jamKerjaSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    } else {
                        $data = Penjadwalan::select('penjadwalan_id', 'tanggal', 'is_attended', 'is_confirm', 'jam_kerja.nama_jam_kerja', 'jam_kerja.urutan', 'jabatan.nama_jabatan', 'status_kehadiran.nama_kehadiran')
                            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
                            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
                            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
                            ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                            ->where('status_kehadiran.status_kehadiran_id', $statusKehadiranSelected)
                            ->where('jabatan.jabatan_id', $jabatanSelected)
                            ->where('jam_kerja.jam_kerja_id', $jamKerjaSelected)
                            ->orderBy('penjadwalan.tanggal', 'asc')
                            ->orderBy('jam_kerja.urutan', 'asc')
                            ->get();
                    }
                }
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_kehadiran', function ($row) {
                    $actionBtn = $row->nama_kehadiran ?? '-';
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn = $row->nama_jam_kerja ?? '-';
                    return $actionBtn;
                })
                ->addColumn('jabatan', function ($row) {
                    return $row->nama_jabatan ?? '-';
                })
                ->addColumn('is_attended', function ($row) {
                    return $row->is_attended ? '<div class="badge badge-success">Hadir</div>' : '<div class="badge badge-danger">Tidak Hadir</div>';
                })
                ->addColumn('is_confirm', function ($row) {
                    return $row->is_confirm ? '<div class="badge badge-success">Konfirmasi</div>' : '<div class="badge badge-danger">Tidak Konfirmasi</div>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('absensi.show', $row->penjadwalan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_attended', 'is_confirm'])
                ->make(true);
        }
        return view('jadwal_absensi/absensi/index', compact('waktuMulai', 'waktuSelesai', 'statusKehadiran', 'statusKehadiranSelected', 'jamKerja', 'jamKerjaSelected', 'jabatan', 'jabatanSelected'));
    }

    public function show(Penjadwalan $penjadwalan)
    {
        return view('jadwal_absensi/absensi/show', compact('penjadwalan'));
    }

    public function edit(Penjadwalan $penjadwalan)
    {
        $statusKehadiran = StatusKehadiran::select('status_kehadiran_id', 'nama_kehadiran')->get();
        return view('jadwal_absensi/absensi/edit', compact('penjadwalan', 'statusKehadiran'));
    }

    public function update(Request $request, Penjadwalan $penjadwalan)
    {
        $request->validate([
            'status_kehadiran_id' => ['required']
        ]);

        $penjadwalan->update([
            'status_kehadiran_id' => $request->status_kehadiran_id
        ]);

        return redirect('/jadwal_absensi/absensi/' . $penjadwalan->penjadwalan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Kehadiwan sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }


    public function confirm(Penjadwalan $penjadwalan)
    {
        $status_penjadwalan = StatusKehadiran::where('nama_kehadiran', 'Hadir')->first();
        $penjadwalan->update([
            'is_confirm' => true,
            'status_kehadiran_id' => $status_penjadwalan->status_kehadiran_id
        ]);


        return redirect('/jadwal_absensi/absensi/' . $penjadwalan->penjadwalan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Konfirmasi sudah di konfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }


    public function unconfirm(Penjadwalan $penjadwalan)
    {
        $status_penjadwalan = StatusKehadiran::where('nama_kehadiran', 'Dijadwalkan')->first();
        $penjadwalan->update([
            'is_confirm' => false,
            'status_kehadiran_id' => $status_penjadwalan->status_kehadiran_id
        ]);

        return redirect('/jadwal_absensi/absensi/' . $penjadwalan->penjadwalan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Konfirmasi sudah di batalkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
