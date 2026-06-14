<?php

namespace App\Http\Controllers;

use App\Models\DetailRencanaPembelian;
use App\Models\FileLo;
use App\Models\Pengiriman;
use App\Models\RencanaPembelian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PengirimanController extends Controller
{

    public function index(Request $request)
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
            $data = Pengiriman::where('is_done', 0)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('no_pol', function ($row) {
                    $actionBtn = $row->no_pol;
                    return $actionBtn;
                })
                ->addColumn('no_lo', function ($row) {
                    $actionBtn = $row->no_lo;
                    return $actionBtn;
                })
                ->addColumn('is_done', function ($row) {
                    return $row->is_done ? 'Selesai' : 'Belum Selesai';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengiriman.show', $row->pengiriman_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation', 'status_selesai'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Pengiriman',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'No Pol', 'data' => 'no_pol'],
                    ['label' => 'No Do', 'data' => 'no_do'],
                    ['label' => 'Selesai', 'data' => 'is_done'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function show(Request $request, RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $available = $pengiriman->file_lo()->select('detail_rencana_pembelian_id')->join('detail_rencana_file', 'detail_rencana_file.file_lo_id', 'file_lo.file_lo_id')->get()->toArray();
        $temp = $pengiriman->ms2()->first()->detail_rencana_pembelian_ms2()->where('is_delivery', 1)->whereNotIn('detail_rencana_pembelian_id', $available)->select('detail_rencana_pembelian_id')->get()->toArray();

        if ($request->ajax() && ! $request->inertia()) {
            $data = $pengiriman->file_lo()->select('file_lo.file_lo_id')->addSelect('file_lo.pengiriman_id')->addSelect('file_lo.jumlah')->addSelect('file_lo.satuan_jumlah')->addSelect('produk.nama_produk')->addSelect('file_lo.no_lo')->addSelect('file_lo.no_faktur')->join('produk', 'produk.produk_id', 'file_lo.produk_id')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->jumlah, 6, ".", ",") . ' ' . $row->satuan_jumlah;
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengiriman.file_lo.show', [$row->pengiriman_id, $row->file_lo_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation', 'status_selesai'])
                ->make(true);
        }
        $user = auth()->user()->member()->first();
        $isPJ = optional(optional(optional($user)->jabatan()->first())->level()->first())->nama_level === 'Penanggung Jawab';
        $isPenerima = optional(optional($user)->ktp()->first())->nama === optional(optional($pengiriman->member()->first())->ktp()->first())->nama;

        $actions = [
            ['label' => 'Ubah Catatan', 'url' => route('pengiriman.ubah_catatan', $pengiriman->pengiriman_id), 'color' => 'info', 'icon' => 'pen'],
        ];
        $notice = null;
        if (count($temp) > 0) {
            $notice = ['text' => 'Masih ada produk yang belum memiliki File LO. Lengkapi dahulu sebelum konfirmasi.', 'color' => 'warning'];
            if ($isPJ || $isPenerima) {
                $actions[] = ['label' => 'Tambah File LO', 'url' => route('pengiriman.file_lo_create', $pengiriman->pengiriman_id), 'color' => 'success', 'icon' => 'plus'];
            }
        } else {
            if ($isPJ && ! $pengiriman->is_done) {
                $actions[] = $pengiriman->is_confirmation_admin
                    ? ['label' => 'Batalkan Konfirmasi Admin', 'method' => 'put', 'url' => route('pengiriman.admin_down', $pengiriman->pengiriman_id), 'color' => 'danger', 'icon' => 'times', 'confirm' => 'Konfirmasi akan dibatalkan?']
                    : ['label' => 'Konfirmasi Admin', 'method' => 'put', 'url' => route('pengiriman.admin_top', $pengiriman->pengiriman_id), 'color' => 'success', 'icon' => 'check', 'confirm' => 'Pengiriman akan dikonfirmasi?'];
            }
            if (! $pengiriman->is_done) {
                $actions[] = $pengiriman->is_confirmation_top
                    ? ['label' => 'Batalkan Konfirmasi Top', 'method' => 'put', 'url' => route('pengiriman.top_down', $pengiriman->pengiriman_id), 'color' => 'danger', 'icon' => 'times', 'confirm' => 'Konfirmasi akan dibatalkan ?']
                    : ['label' => 'Konfirmasi Top Manajemen', 'method' => 'put', 'url' => route('pengiriman.top_top', $pengiriman->pengiriman_id), 'color' => 'success', 'icon' => 'check', 'confirm' => 'Pengiriman akan dikonfirmasi ?'];
            }
            if ($pengiriman->is_confirmation_admin && $pengiriman->is_confirmation_top && ! $pengiriman->is_done) {
                $actions[] = ['label' => 'Tandai Selesai', 'method' => 'put', 'url' => route('pengiriman.done', $pengiriman->pengiriman_id), 'color' => 'info', 'icon' => 'flag-checkered', 'confirm' => "Pengiriman akan diselesaikan dan dilanjukan ke fase Penerimaan BBM!\n Lanjutkan?"];
            }
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Pengiriman',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')]],
            'backUrl' => route('pengiriman.index'),
            'notice' => $notice,
            'fields' => [
                ['label' => 'Yang Menerima', 'value' => optional(optional($pengiriman->member()->first())->ktp()->first())->nama],
                ['label' => 'Tanggal', 'value' => $pengiriman->tanggal],
                ['label' => 'No DO', 'value' => $pengiriman->no_do],
                ['label' => 'No Pol', 'value' => $pengiriman->no_pol],
                ['label' => 'Catatan', 'value' => $pengiriman->catatan],
                ['label' => 'Konfirmasi Admin', 'value' => $pengiriman->is_confirmation_admin ? 'Terkonfirmasi' : 'Belum Terkonfirmasi'],
                ['label' => 'Konfirmasi Top Manajemen', 'value' => $pengiriman->is_confirmation_top ? 'Terkonfirmasi' : 'Belum Terkonfirmasi'],
                ['label' => 'Status', 'value' => $pengiriman->is_done ? 'Selesai' : 'Dalam Proses'],
            ],
            'table' => [
                'title' => 'File LO',
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'No. Faktur', 'data' => 'no_faktur'],
                    ['label' => 'No. LO', 'data' => 'no_lo'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Stok', 'data' => 'jumlah'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
            'actions' => $actions,
        ]);
    }

    public function confirmation(RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => 1
        ]);

        return redirect('/procurement/delivery')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function done(RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_done' => 1,
        ]);

        // foreach ($pengiriman->file_lo()->get() as $p) {
        //     $p->detail_rencana_pembelian()->first()->update([
        //         'delivery' => false
        //     ]);
        // }

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function show_file_lo(Pengiriman $pengiriman, FileLo $file_lo)
    {
        $detail = $file_lo->detail_rencana_pembelian()->first();
        $rencana = $detail ? $detail->rencana_pembelian()->first() : null;

        return Inertia::render('Show/Index', [
            'title' => 'Detail File LO',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')], ['label' => 'Pengiriman', 'url' => route('pengiriman.show', $pengiriman->pengiriman_id)]],
            'backUrl' => route('pengiriman.show', $pengiriman->pengiriman_id),
            'fields' => [
                ['label' => 'No LO', 'value' => $file_lo->no_lo],
                ['label' => 'No Faktur', 'value' => $file_lo->no_faktur],
                ['label' => 'Nama Dokumen', 'value' => $file_lo->nama_dokumen],
                ['label' => 'Tanggal Upload', 'value' => $file_lo->tanggal_upload],
                ['label' => 'Produk', 'value' => optional($file_lo->produk()->first())->nama_produk],
                ['label' => 'Jumlah / Quantity', 'value' => number_format($file_lo->jumlah, 6, '.', ',') . ' ' . $file_lo->satuan_jumlah],
                ['label' => 'Harga', 'value' => $detail ? 'Rp ' . number_format($detail->harga, 6, '.', ',') : null],
                ['label' => 'Id Rencana Pembelian', 'value' => optional($rencana)->rencana_pembelian_id],
                ['label' => 'Grand Total', 'value' => $rencana ? 'Rp ' . number_format($rencana->grand_total, 6, '.', ',') : null],
            ],
            'image' => asset('storage/file_lo/' . $file_lo->nama_file),
            'table' => [
                'title' => 'Lampiran File LO',
                'url' => route('pengiriman.file_lo_show.lampiran.index', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]),
                'columns' => [
                    ['label' => 'Nama File', 'data' => 'nama_file'],
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Tanggal Upload', 'data' => 'tanggal_upload'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
            'actions' => [
                ['label' => 'Tambah Lampiran', 'url' => route('pengiriman.file_lo_show.lampiran.create', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]), 'color' => 'primary', 'icon' => 'plus'],
            ],
            'deleteUrl' => route('pengiriman.file_lo.delete', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]),
        ]);
    }

    public function file_lo_create(Request $request, Pengiriman $pengiriman)
    {
        $available = $pengiriman->file_lo()->select('detail_rencana_pembelian_id')->join('detail_rencana_file', 'detail_rencana_file.file_lo_id', 'file_lo.file_lo_id')->get()->toArray();
        $temp = $pengiriman->ms2()->first()->detail_rencana_pembelian_ms2()->where('is_delivery', 1)->whereNotIn('detail_rencana_pembelian_id', $available)->select('detail_rencana_pembelian_id')->get()->toArray();
        $detail_rencana_pembelian = DetailRencanaPembelian::whereIn('detail_rencana_pembelian_id', $temp)->get();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File LO',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')], ['label' => 'Pengiriman', 'url' => route('pengiriman.show', $pengiriman->pengiriman_id)]],
            'backUrl' => route('pengiriman.show', $pengiriman->pengiriman_id),
            'method' => 'post',
            'action' => route('pengiriman.file_lo_store', $pengiriman->pengiriman_id),
            'fields' => [
                ['name' => 'detail_rencana_pembelian_id', 'label' => 'Produk', 'type' => 'select', 'value' => null,
                    'options' => $detail_rencana_pembelian->map(fn ($s) => [
                        'value' => $s->detail_rencana_pembelian_id,
                        'label' => optional($s->produk()->first())->nama_produk . ' ' . number_format($s->jumlah, 2, '.', ',') . ' ' . $s->satuan_jumlah . ' (' . optional($s->rencana_pembelian()->first())->kode_pembelian . ')',
                    ])->values()->all()],
                ['name' => 'no_lo', 'label' => 'No LO', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'no_faktur', 'label' => 'No Faktur', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'gambar', 'label' => 'File LO', 'type' => 'file', 'value' => null, 'accept' => '.jpg,.jpeg,.png,.bmp', 'hint' => 'Gambar maks 5MB (jpg/png/bmp).'],
            ],
        ]);
    }

    public function file_lo_store(Request $request, Pengiriman $pengiriman)
    {
        $request->validate([
            'detail_rencana_pembelian_id' => ['required'],
            'no_faktur' => ['required'],
            'no_lo' => ['required'],
            'gambar' => 'required|max:5120|mimes:jpg,png,jpeg,bmp|image'
        ]);

        $foto = 'default.png';
        $filenameWithExt = null;
        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_lo', $foto);
        }

        $detail_rencana_pembelian = DetailRencanaPembelian::where('detail_rencana_pembelian_id', $request->detail_rencana_pembelian_id)->first();

        $detail_rencana_pembelian->update([
            'ms2' => 0
        ]);

        $file_lo = $pengiriman->file_lo()->where('produk_id', $detail_rencana_pembelian->produk_id)->where('jumlah', $detail_rencana_pembelian->jumlah)->where('satuan_jumlah', $detail_rencana_pembelian->satuan_jumlah)->where('no_faktur', request('no_faktur'))->where('no_lo', request('no_lo'))->where('nama_dokumen', $filenameWithExt)->where('nama_file', $foto)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($file_lo)) {
            $file_lo = $pengiriman->file_lo()->create([
                'produk_id' => $detail_rencana_pembelian->produk_id,
                'jumlah' => $detail_rencana_pembelian->jumlah,
                'satuan_jumlah' => $detail_rencana_pembelian->satuan_jumlah,
                'no_faktur' => request('no_faktur'),
                'no_lo' => request('no_lo'),
                'nama_dokumen' => $filenameWithExt,
                'nama_file' => $foto,
                'tanggal_upload' => date('Y-m-d'),
            ]);

            DB::table('detail_rencana_file')->insert([
                'detail_rencana_pembelian_id' => request('detail_rencana_pembelian_id'),
                'file_lo_id' => $file_lo->file_lo_id
            ]);
        }

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id . '/file_lo_show/' . $file_lo->file_lo_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah Di simpan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function delete_file_lo(Pengiriman $pengiriman, FileLo $file_lo)
    {
        DB::table('detail_rencana_file')->where('file_lo_id', $file_lo->file_lo_id)->delete();

        if ($file_lo->nama_file != 'default.png') {
            Storage::delete("public/file_lo/" . $file_lo->nama_file);
        }

        $file_lo->delete();

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function admin_top(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => 1
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah dikonfirmasi oleh Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function admin_down(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => 0
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah di batalkan konfirmasi oleh Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function top_top(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_top' => 1
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah dikonfirmasi oleh Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function top_down(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_top' => 0
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah dibatalkan konfirmasi oleh Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function ubah_catatan(Pengiriman $pengiriman)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Catatan Pengiriman',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')]],
            'backUrl' => route('pengiriman.show', $pengiriman->pengiriman_id),
            'method' => 'put',
            'action' => route('pengiriman.update_catatan', $pengiriman->pengiriman_id),
            'fields' => [
                ['name' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea', 'rows' => 5, 'value' => $pengiriman->catatan],
            ],
        ]);
    }

    public function update_catatan(Request $request, Pengiriman $pengiriman)
    {
        $request->validate([
            'catatan' => ['required']
        ]);

        $pengiriman->update([
            'catatan' => trim($request->catatan)
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Catatan Pengiriman sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
