<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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

        if ($request->ajax() && ! $request->inertia()) {
            $data = StatusSetoran::where('nama_status', 'Penitipan Setoran')->first()->penjualan()->whereBetween('penjualan.tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
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
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.penitipan.show', $row->penjualan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Penitipan Setoran',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')]],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
                    ['label' => 'Stand Meter', 'data' => 'stand_meter'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function show_open_penjualan(Penjualan $penjualan)
    {
        $who = \App\Support\SetoranShow::levelInfo();

        $actions = [];
        if ($who['level'] === 'Penanggung Jawab') {
            $actions[] = ['label' => 'Kembali ke Opening', 'color' => 'info', 'icon' => 'arrow-left', 'method' => 'put',
                'url' => route('setoran.open.update.status_from', $penjualan->penjualan_id),
                'confirm' => "Penitipan Setoran akan di kembalikan ke Opening Setoran!\n Lanjutkan?"];
            $actions[] = ['label' => 'Lanjut Ke Closing', 'color' => 'success', 'icon' => 'arrow-right', 'method' => 'put',
                'url' => route('setoran.penitipan.update_closing_to', $penjualan->penjualan_id),
                'confirm' => "Penitipan Setoran akan di lanjutkan ke Closing!\n Lanjutkan?"];
        }

        return Inertia::render('Setoran/Show', [
            'title' => 'Penitipan Setoran',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')], ['label' => 'Penitipan Setoran', 'url' => route('setoran.penitipan')]],
            'backUrl' => route('setoran.penitipan'),
            'info' => \App\Support\SetoranShow::info($penjualan),
            'pj' => \App\Support\SetoranShow::pj($penjualan),
            'standMeters' => \App\Support\SetoranShow::standMeters($penjualan),
            'actions' => $actions,
        ]);
    }

    public function update_closing_to(Request $request, Penjualan $penjualan)
    {
        $status = StatusSetoran::where(['nama_status' => 'Closing Penjualan'])->first();
        $penjualan->update(['status_setoran_id' => $status->status_setoran_id]);

        return redirect('/setoran/closing-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function update_closing_from(Request $request, Penjualan $penjualan)
    {
        $status = StatusSetoran::where(['nama_status' => 'Penitipan Setoran'])->first();
        $penjualan->update(['status_setoran_id' => $status->status_setoran_id]);

        return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penitipan Setoran Sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
