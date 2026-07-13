<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\Penjadwalan;
use App\Models\StatusKehadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Absensi',
            'breadcrumbs' => [['label' => 'Jadwal & Absensi', 'url' => route('menu.jadwal_absensi')]],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'selectFilters' => [
                ['name' => 'status_kehadiran_id', 'label' => 'Status Kehadiran', 'value' => $statusKehadiranSelected, 'options' => array_merge([['value' => 'all', 'label' => 'Semua']], $statusKehadiran->map(fn ($x) => ['value' => $x->status_kehadiran_id, 'label' => $x->nama_kehadiran])->all())],
                ['name' => 'jam_kerja_id', 'label' => 'Jam Kerja', 'value' => $jamKerjaSelected, 'options' => array_merge([['value' => 'all', 'label' => 'Semua']], $jamKerja->map(fn ($x) => ['value' => $x->jam_kerja_id, 'label' => $x->nama_jam_kerja])->all())],
                ['name' => 'jabatan_id', 'label' => 'Jabatan', 'value' => $jabatanSelected, 'options' => array_merge([['value' => 'all', 'label' => 'Semua']], $jabatan->map(fn ($x) => ['value' => $x->jabatan_id, 'label' => $x->nama_jabatan])->all())],
            ],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Status Kehadiran', 'data' => 'status_kehadiran'],
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
                    ['label' => 'Jabatan', 'data' => 'jabatan'],
                    ['label' => 'Konfirmasi', 'data' => 'is_attended', 'raw' => true],
                    ['label' => 'Hadir', 'data' => 'is_confirm', 'raw' => true],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function show(Penjadwalan $penjadwalan)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Absensi',
            'breadcrumbs' => [
                ['label' => 'Jadwal & Absensi', 'url' => route('menu.jadwal_absensi')],
                ['label' => 'Absensi', 'url' => route('absensi.index')],
            ],
            'backUrl' => route('absensi.index'),
            'fields' => [
                ['label' => 'Status Kehadiran', 'value' => optional($penjadwalan->status_kehadiran()->first())->nama_kehadiran],
                ['label' => 'Tanggal', 'value' => Carbon::createFromFormat('Y-m-d', $penjadwalan->tanggal)->format('d F Y')],
                ['label' => 'Jam Absen Masuk', 'value' => is_null($penjadwalan->start_time) ? '' : Carbon::createFromFormat('Y-m-d H:i:s', $penjadwalan->start_time)->format('d F Y H:i:s')],
                ['label' => 'Jam Absen Keluar', 'value' => is_null($penjadwalan->finish_time) ? '' : Carbon::createFromFormat('Y-m-d H:i:s', $penjadwalan->finish_time)->format('d F Y H:i:s')],
                ['label' => 'Jam Kerja', 'value' => optional($penjadwalan->jam_kerja()->first())->nama_jam_kerja],
                ['label' => 'Nama Pegawai', 'value' => optional(optional($penjadwalan->member()->first())->ktp()->first())->nama],
                ['label' => 'Jabatan', 'value' => optional($penjadwalan->jabatan()->first())->nama_jabatan],
                ['label' => 'Konfirmasi', 'value' => $penjadwalan->is_confirm ? 'Konfirmasi' : 'Tidak Konfirmasi'],
                ['label' => 'Hadir', 'value' => $penjadwalan->is_attended ? 'Hadir' : 'Tidak Hadir'],
                ['label' => 'Deskripsi', 'value' => $penjadwalan->deskripsi],
            ],
            'actions' => array_values(array_filter([
                ['label' => 'Ubah Status', 'url' => route('absensi.edit', $penjadwalan->penjadwalan_id), 'color' => 'success', 'icon' => 'pen'],
                $penjadwalan->is_confirm
                    ? ['label' => 'Batalkan Konfirmasi', 'url' => route('absensi.unconfirm', $penjadwalan->penjadwalan_id), 'color' => 'danger', 'icon' => 'times', 'method' => 'put', 'confirm' => "Absensi akan dibatalkan konfirmasi!\n Lanjutkan?"]
                    : ['label' => 'Konfirmasi', 'url' => route('absensi.confirm', $penjadwalan->penjadwalan_id), 'color' => 'info', 'icon' => 'check', 'method' => 'put', 'confirm' => "Absensi akan dikonfirmasi!\n Lanjutkan?"],
            ])),
        ]);
    }

    public function edit(Penjadwalan $penjadwalan)
    {
        $statusKehadiran = StatusKehadiran::select('status_kehadiran_id', 'nama_kehadiran')->get();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Status Absensi',
            'breadcrumbs' => [
                ['label' => 'Jadwal & Absensi', 'url' => route('menu.jadwal_absensi')],
                ['label' => 'Absensi', 'url' => route('absensi.index')],
                ['label' => 'Detail Absensi', 'url' => route('absensi.show', $penjadwalan->penjadwalan_id)],
            ],
            'backUrl' => route('absensi.show', $penjadwalan->penjadwalan_id),
            'method' => 'put',
            'action' => route('absensi.update', $penjadwalan->penjadwalan_id),
            'fields' => [[
                'name' => 'status_kehadiran_id',
                'label' => 'Status Kehadiran',
                'type' => 'select',
                'value' => $penjadwalan->status_kehadiran_id,
                'options' => $statusKehadiran->map(fn ($x) => ['value' => $x->status_kehadiran_id, 'label' => $x->nama_kehadiran])->all(),
            ]],
        ]);
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
            'is_confirm' => 1,
            'status_kehadiran_id' => $status_penjadwalan->status_kehadiran_id
        ]);


        return redirect('/jadwal_absensi/absensi/' . $penjadwalan->penjadwalan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Konfirmasi sudah di konfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }


    public function unconfirm(Penjadwalan $penjadwalan)
    {
        $status_penjadwalan = StatusKehadiran::where('nama_kehadiran', 'Dijadwalkan')->first();
        $penjadwalan->update([
            'is_confirm' => 0,
            'status_kehadiran_id' => $status_penjadwalan->status_kehadiran_id
        ]);

        return redirect('/jadwal_absensi/absensi/' . $penjadwalan->penjadwalan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Konfirmasi sudah di batalkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
