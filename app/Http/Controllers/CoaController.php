<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoaRequest;
use App\Models\Coa;
use App\Support\Akun;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use ReflectionClass;
use Yajra\DataTables\DataTables;

class CoaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Coa::orderBy('kode_akun', 'asc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tipe', function ($row) {
                    return ucwords(str_replace('_', ' ', $row->tipe_akun)) . ($row->is_header ? ' (Header)' : '');
                })
                ->addColumn('saldo_normal_label', function ($row) {
                    return ucfirst($row->saldo_normal);
                })
                ->addColumn('is_aktif', function ($row) {
                    return $row->is_aktif ? 'Aktif' : 'Tidak Aktif';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('coa.show', $row->coa_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return Inertia::render('Table/Index', [
            'title' => 'Chart of Accounts (COA)',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'createUrl' => route('coa.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Kode Akun', 'data' => 'kode_akun'],
                    ['label' => 'Nama Akun', 'data' => 'nama_akun'],
                    ['label' => 'Tipe', 'data' => 'tipe'],
                    ['label' => 'Saldo Normal', 'data' => 'saldo_normal_label'],
                    ['label' => 'Status', 'data' => 'is_aktif'],
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
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Akun',
            'breadcrumbs' => $this->breadcrumbs(),
            'backUrl' => route('coa.index'),
            'method' => 'post',
            'action' => route('coa.store'),
            'fields' => $this->fields(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CoaRequest $coaRequest)
    {
        Coa::create($this->coaData());

        return redirect(route('coa.index'))->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Akun sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coa $coa)
    {
        $dipakaiPosting = in_array($coa->kode_akun, $this->kodeAkunBaku());

        return Inertia::render('Show/Index', [
            'title' => 'Detail Akun',
            'breadcrumbs' => $this->breadcrumbs(),
            'backUrl' => route('coa.index'),
            'notice' => $dipakaiPosting ? ['text' => 'Akun ini dirujuk oleh posting jurnal otomatis. Kode akun tidak boleh diubah dan akun tidak boleh dihapus.', 'color' => 'warning'] : null,
            'fields' => [
                ['label' => 'Kode Akun', 'value' => $coa->kode_akun],
                ['label' => 'Nama Akun', 'value' => $coa->nama_akun],
                ['label' => 'Akun Induk', 'value' => $coa->parent ? $coa->parent->kode_akun . ' - ' . $coa->parent->nama_akun : '-'],
                ['label' => 'Tipe Akun', 'value' => ucwords(str_replace('_', ' ', $coa->tipe_akun))],
                ['label' => 'Saldo Normal', 'value' => ucfirst($coa->saldo_normal)],
                ['label' => 'Kategori Arus Kas', 'value' => $coa->kategori_arus_kas ? ucfirst($coa->kategori_arus_kas) : '-'],
                ['label' => 'Akun Kas/Bank', 'value' => $coa->is_kas ? 'Ya' : 'Tidak'],
                ['label' => 'Akun Header', 'value' => $coa->is_header ? 'Ya' : 'Tidak'],
                ['label' => 'Status', 'value' => $coa->is_aktif ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $coa->keterangan ?? '-'],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('coa.edit', $coa), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => $dipakaiPosting ? null : route('coa.destroy', $coa),
            'deleteConfirm' => 'Yakin ingin menghapus akun ini? Akun yang sudah dipakai jurnal tidak bisa dihapus.',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coa $coa)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Akun',
            'breadcrumbs' => $this->breadcrumbs(),
            'backUrl' => route('coa.index'),
            'method' => 'put',
            'action' => route('coa.update', $coa),
            'fields' => $this->fields($coa),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CoaRequest $coaRequest, Coa $coa)
    {
        $data = $this->coaData();
        if (in_array($coa->kode_akun, $this->kodeAkunBaku())) {
            // akun baku dirujuk posting otomatis lewat kode-nya
            unset($data['kode_akun']);
        }
        $coa->update($data);

        return redirect(route('coa.index'))->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Akun sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coa $coa)
    {
        if (in_array($coa->kode_akun, $this->kodeAkunBaku())) {
            return redirect(route('coa.index'))->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Akun ini dirujuk posting jurnal otomatis dan tidak boleh dihapus. Nonaktifkan saja jika tidak dipakai.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }

        try {
            $coa->delete();
        } catch (QueryException $e) {
            return redirect(route('coa.index'))->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Akun sudah dipakai jurnal atau menjadi induk akun lain, tidak bisa dihapus. Nonaktifkan saja.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }

        return redirect(route('coa.index'))->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Akun sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    private function breadcrumbs(): array
    {
        return [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'COA', 'url' => route('coa.index')]];
    }

    private function fields(?Coa $coa = null): array
    {
        $boolOptions = [['value' => 'true', 'label' => 'Ya'], ['value' => 'false', 'label' => 'Tidak']];
        $parents = Coa::where('is_header', true)
            ->when($coa, fn ($q) => $q->where('coa_id', '!=', $coa->coa_id))
            ->orderBy('kode_akun')
            ->get()
            ->map(fn ($p) => ['value' => $p->coa_id, 'label' => $p->kode_akun . ' - ' . $p->nama_akun])
            ->values()->all();

        return [
            ['name' => 'kode_akun', 'label' => 'Kode Akun', 'type' => 'text', 'value' => $coa?->kode_akun, 'col' => 'col-md-4'],
            ['name' => 'nama_akun', 'label' => 'Nama Akun', 'type' => 'text', 'value' => $coa?->nama_akun, 'col' => 'col-md-8'],
            ['name' => 'parent_coa_id', 'label' => 'Akun Induk', 'type' => 'select', 'value' => $coa?->parent_coa_id, 'options' => $parents, 'hint' => 'Kosongkan jika tanpa induk.', 'col' => 'col-md-6'],
            ['name' => 'tipe_akun', 'label' => 'Tipe Akun', 'type' => 'select', 'value' => $coa?->tipe_akun, 'options' => collect(Coa::TIPE_AKUN)->map(fn ($t) => ['value' => $t, 'label' => ucwords(str_replace('_', ' ', $t))])->values()->all(), 'col' => 'col-md-6'],
            ['name' => 'saldo_normal', 'label' => 'Saldo Normal', 'type' => 'select', 'value' => $coa?->saldo_normal, 'options' => [['value' => 'debit', 'label' => 'Debit'], ['value' => 'kredit', 'label' => 'Kredit']], 'col' => 'col-md-4'],
            ['name' => 'kategori_arus_kas', 'label' => 'Kategori Arus Kas', 'type' => 'select', 'value' => $coa?->kategori_arus_kas, 'options' => [['value' => 'operasi', 'label' => 'Operasi'], ['value' => 'investasi', 'label' => 'Investasi'], ['value' => 'pendanaan', 'label' => 'Pendanaan']], 'hint' => 'Kosongkan jika akun tidak masuk laporan arus kas.', 'col' => 'col-md-4'],
            ['name' => 'is_kas', 'label' => 'Akun Kas/Bank', 'type' => 'select', 'value' => $coa ? ($coa->is_kas ? 'true' : 'false') : 'false', 'options' => $boolOptions, 'col' => 'col-md-4'],
            ['name' => 'is_header', 'label' => 'Akun Header (kelompok, tidak menerima posting)', 'type' => 'select', 'value' => $coa ? ($coa->is_header ? 'true' : 'false') : 'false', 'options' => $boolOptions, 'col' => 'col-md-6'],
            ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => $coa ? ($coa->is_aktif ? 'true' : 'false') : 'true', 'options' => [['value' => 'true', 'label' => 'Aktif'], ['value' => 'false', 'label' => 'Tidak Aktif']], 'col' => 'col-md-6'],
            ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $coa?->keterangan],
        ];
    }

    private function coaData(): array
    {
        return [
            'kode_akun' => request('kode_akun'),
            'nama_akun' => request('nama_akun'),
            'parent_coa_id' => request('parent_coa_id') ?: null,
            'tipe_akun' => request('tipe_akun'),
            'saldo_normal' => request('saldo_normal'),
            'kategori_arus_kas' => request('kategori_arus_kas') ?: null,
            'is_kas' => filter_var(request('is_kas'), FILTER_VALIDATE_BOOLEAN),
            'is_header' => filter_var(request('is_header'), FILTER_VALIDATE_BOOLEAN),
            'is_aktif' => filter_var(request('is_aktif'), FILTER_VALIDATE_BOOLEAN),
            'keterangan' => request('keterangan'),
        ];
    }

    private function kodeAkunBaku(): array
    {
        return array_values((new ReflectionClass(Akun::class))->getConstants());
    }
}
