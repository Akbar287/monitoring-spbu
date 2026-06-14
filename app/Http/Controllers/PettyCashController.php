<?php

namespace App\Http\Controllers;

use App\Http\Requests\PettyCashRequest;
use App\Models\PettyCash;
use App\Models\Spbu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PettyCashController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = PettyCash::orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->jumlah, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('petty-cash.show', $row->petty_cash_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'createUrl' => route('petty-cash.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'No. Kode', 'data' => 'no_kode'],
                    ['label' => 'Jenis', 'data' => 'jenis'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
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
        $spbu = Spbu::get();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')]],
            'backUrl' => route('financial.petty-cash'),
            'method' => 'post',
            'action' => route('petty-cash.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu . ' - ' . $s->nomor_spbu])->values()->all()],
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'jenis', 'label' => 'Jenis', 'type' => 'select', 'value' => null, 'options' => [['value' => 'debit', 'label' => 'Debit'], ['value' => 'kredit', 'label' => 'Kredit']], 'col' => 'col-md-6'],
                ['name' => 'no_kode', 'label' => 'No. Kode', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'no_bukti', 'label' => 'No. Bukti', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => null],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PettyCashRequest $pettyCashRequest)
    {
        $spbu = Spbu::where('spbu_id', $pettyCashRequest->spbu_id)->first();

        $pettyCash = $spbu->petty_cash()->where('no_kode', request('no_kode'))->where('no_bukti', request('no_bukti'))->where('keterangan', request('keterangan'))->where('jumlah', str_replace(',', '', request('jumlah')))->where('jenis', request('jenis'))->where('tanggal', request('tanggal'))->first();
        if (is_null($pettyCash)) {
            $pettyCash = $spbu->petty_cash()->create($this->pettyCashData());
        }

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Petty Cash sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(PettyCash $pettyCash)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')]],
            'backUrl' => route('financial.petty-cash'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional($pettyCash->spbu()->first())->nama_spbu],
                ['label' => 'Tanggal', 'value' => $pettyCash->tanggal],
                ['label' => 'No. Kode', 'value' => $pettyCash->no_kode],
                ['label' => 'No. Bukti', 'value' => $pettyCash->no_bukti],
                ['label' => 'Jenis', 'value' => ucfirst($pettyCash->jenis ?? '')],
                ['label' => 'Jumlah', 'value' => 'Rp ' . number_format($pettyCash->jumlah, 6, '.', ',')],
                ['label' => 'Keterangan', 'value' => $pettyCash->keterangan],
            ],
            'actions' => [
                ['label' => 'Ubah', 'url' => route('petty-cash.edit', $pettyCash->petty_cash_id), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Dokumen', 'url' => route('financial.petty-cash.file', $pettyCash->petty_cash_id), 'color' => 'info', 'icon' => 'folder-open'],
            ],
            'deleteUrl' => route('petty-cash.delete', $pettyCash->petty_cash_id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PettyCash $pettyCash)
    {
        $spbu = Spbu::get();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')]],
            'backUrl' => route('petty-cash.show', $pettyCash->petty_cash_id),
            'method' => 'put',
            'action' => route('petty-cash.update', $pettyCash->petty_cash_id),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $pettyCash->spbu_id,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu . ' - ' . $s->nomor_spbu])->values()->all()],
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'value' => $pettyCash->tanggal, 'col' => 'col-md-6'],
                ['name' => 'jenis', 'label' => 'Jenis', 'type' => 'select', 'value' => $pettyCash->jenis, 'options' => [['value' => 'debit', 'label' => 'Debit'], ['value' => 'kredit', 'label' => 'Kredit']], 'col' => 'col-md-6'],
                ['name' => 'no_kode', 'label' => 'No. Kode', 'type' => 'text', 'value' => $pettyCash->no_kode, 'col' => 'col-md-6'],
                ['name' => 'no_bukti', 'label' => 'No. Bukti', 'type' => 'text', 'value' => $pettyCash->no_bukti, 'col' => 'col-md-6'],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => $pettyCash->jumlah],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $pettyCash->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PettyCashRequest $pettyCashRequest, PettyCash $pettyCash)
    {
        $pettyCash->update($this->pettyCashData());

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Petty Cash sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PettyCash $pettyCash)
    {
        $pettyCash->delete();

        return redirect('/financial/petty-cash')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Petty Cash sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pettyCashData()
    {
        return [
            'no_kode' => request('no_kode'),
            'no_bukti' => request('no_bukti'),
            'keterangan' => request('keterangan'),
            'jumlah' => str_replace(',', '', request('jumlah')),
            'jenis' => request('jenis'),
            'tanggal' => request('tanggal'),
        ];
    }
}
