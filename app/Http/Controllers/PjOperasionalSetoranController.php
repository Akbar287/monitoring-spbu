<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Member;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PjOperasionalSetoranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Penjualan $penjualan)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $penjualan->pj_operasional_setoran()->distinct('jabatan_id')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_jabatan', function ($row) {
                    $actionBtn = $row->jabatan()->first()->nama_jabatan;
                    return $actionBtn;
                })
                ->addColumn('nama', function ($row) use ($penjualan) {
                    $names = $penjualan->pj_operasional_setoran()
                        ->select('ktp.nama')
                        ->join('member', 'member.member_id', 'pj_operasional_setoran.member_id')
                        ->join('ktp', 'ktp.ktp_id', 'member.ktp_id')
                        ->where('jabatan_id', $row->jabatan_id)
                        ->orderBy('ktp.nama')
                        ->pluck('ktp.nama')
                        ->all();

                    return count($names) === 0 ? '-' : implode(', ', $names);
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.open.pj.show', [$row->penjualan_id, $row->jabatan_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'PJ Operasional Setoran',
            'breadcrumbs' => [
                ['label' => 'Setoran', 'url' => route('menu.setoran')],
                ['label' => 'Open Penjualan', 'url' => route('setoran.open')],
                ['label' => 'Detail Penjualan', 'url' => route('setoran.open.show', $penjualan->penjualan_id)],
            ],
            'createUrl' => route('setoran.open.pj.create', $penjualan->penjualan_id),
            'headerActions' => [
                ['label' => 'Kembali', 'url' => route('setoran.open.show', $penjualan->penjualan_id), 'color' => 'light', 'icon' => 'arrow-left'],
            ],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Jabatan', 'data' => 'nama_jabatan'],
                    ['label' => 'Member', 'data' => 'nama'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penjualan $penjualan)
    {
        $jabatan = Jabatan::select('jabatan_id', 'nama_jabatan')->orderBy('nama_jabatan', 'asc')->get();
        $member = Member::select('member.member_id', 'ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->orderBy('ktp.nama')->get();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah PJ Operasional Setoran',
            'breadcrumbs' => [
                ['label' => 'Setoran', 'url' => route('menu.setoran')],
                ['label' => 'Open Penjualan', 'url' => route('setoran.open')],
                ['label' => 'Detail Penjualan', 'url' => route('setoran.open.show', $penjualan->penjualan_id)],
                ['label' => 'PJ Operasional', 'url' => route('setoran.open.pj', $penjualan->penjualan_id)],
            ],
            'backUrl' => route('setoran.open.pj', $penjualan->penjualan_id),
            'action' => route('setoran.open.pj.store', $penjualan->penjualan_id),
            'method' => 'post',
            'fields' => [
                [
                    'name' => 'jabatan_id',
                    'label' => 'Jabatan',
                    'type' => 'select',
                    'options' => $jabatan->map(fn ($item) => ['value' => $item->jabatan_id, 'label' => $item->nama_jabatan])->values()->all(),
                ],
                [
                    'name' => 'member_id',
                    'label' => 'Member',
                    'type' => 'multiselect',
                    'value' => [],
                    'options' => $member->map(fn ($item) => ['value' => $item->member_id, 'label' => $item->nama])->values()->all(),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Penjualan $penjualan)
    {
        $request->validate([
            'jabatan_id' => ['required'],
            'member_id' => ['required'],
        ]);

        if (count($request->member_id) > 0) {
        }
        foreach ($request->member_id as $member) {
            $pj = $penjualan->pj_operasional_setoran()->where('jabatan_id', $request->jabatan_id)->where('member_id', $member)->first();
            if (is_null($pj)) {
                $pj = $penjualan->pj_operasional_setoran()->create([
                    'jabatan_id' => $request->jabatan_id,
                    'member_id' => $member
                ]);
            }

            if ($penjualan->pj_operasional_setoran()->where('jabatan_id', $request->jabatan_id)->where('member_id', $member)->count() > 1) {
                $penjualan->pj_operasional_setoran()->where('jabatan_id', $request->jabatan_id)->where('member_id', $member)->first()->delete();
            }
        }

        return redirect()->route('setoran.open.pj.show', [$penjualan->penjualan_id, $request->jabatan_id])->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> PJ Operasional sudah di tambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        exit;
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan, Jabatan $jabatan)
    {
        $members = $penjualan->pj_operasional_setoran()
            ->select('ktp.nama')
            ->join('member', 'member.member_id', 'pj_operasional_setoran.member_id')
            ->join('ktp', 'ktp.ktp_id', 'member.ktp_id')
            ->where('jabatan_id', $jabatan->jabatan_id)
            ->orderBy('ktp.nama')
            ->get();

        return Inertia::render('Show/Index', [
            'title' => 'Detail PJ Operasional Setoran',
            'breadcrumbs' => [
                ['label' => 'Setoran', 'url' => route('menu.setoran')],
                ['label' => 'Open Penjualan', 'url' => route('setoran.open')],
                ['label' => 'Detail Penjualan', 'url' => route('setoran.open.show', $penjualan->penjualan_id)],
                ['label' => 'PJ Operasional', 'url' => route('setoran.open.pj', $penjualan->penjualan_id)],
            ],
            'backUrl' => route('setoran.open.pj', $penjualan->penjualan_id),
            'fields' => [
                ['label' => 'Jabatan', 'value' => $jabatan->nama_jabatan],
                ['label' => 'Tanggal Penjualan', 'value' => $penjualan->tanggal],
            ],
            'tables' => [[
                'title' => 'Member',
                'columns' => ['Nama'],
                'rows' => $members->map(fn ($item) => [$item->nama])->values()->all(),
            ]],
            'deleteUrl' => route('setoran.open.pj.delete', [$penjualan->penjualan_id, $jabatan->jabatan_id]),
            'deleteConfirm' => "PJ Operasional akan dihapus!\n Lanjutkan?",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjualan $penjualan, Jabatan $jabatan)
    {
        $penjualan->pj_operasional_setoran()->where('jabatan_id', $jabatan->jabatan_id)->delete();

        return redirect()->route('setoran.open.pj', $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> PJ Operasional sudah di hapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        exit;
    }

    public function api(Request $request)
    {
        $request->validate([
            'jabatan_id' => ['required']
        ]);

        $jabatan = Jabatan::where('jabatan_id', request('jabatan_id'))->first();

        if (!is_null($jabatan)) {
            $member = $jabatan->member()->select('member.member_id')->get()->toArray();

            return response()->json([
                'data' => (count($member) > 0) ? array_map(function ($item) {
                    return strval($item["member_id"]);
                }, $member) : [],
                'message' => 'Member has been catched',
                'status' => 'success'
            ], 200);
            exit;
        } else {
            return response()->json([
                'data' => [],
                'message' => 'Jabatan not found',
                'status' => 'error'
            ], 200);
            exit;
        }
    }
}
