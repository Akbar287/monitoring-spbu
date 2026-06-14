<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\FilePost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FilePostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Post $post)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $post->file_post()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_dokumen', function ($row) {
                    return $row->nama_dokumen;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('file.post.show', [$row->post()->first()->post_id, $row->file_post_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'gambar'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')]],
            'createUrl' => route('file.post.create', $post),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Post $post)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')], ['label' => 'File Post', 'url' => route('file.post.index', $post)]],
            'backUrl' => route('file.post.index', $post),
            'method' => 'post',
            'action' => route('file.post.store', $post),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = 'default.png';
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/post', $foto);
        }

        $filePost = $post->file_post()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->first();
        if (is_null($filePost)) {
            $filePost = $post->file_post()->create($this->filePostData($foto, $filenameWithExt));
        }

        return redirect('/artikel/post/' . $post->post_id . '/file/' . $filePost->file_post_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Post sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post, FilePost $filePost)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')], ['label' => 'File Post', 'url' => route('file.post.index', $post)]],
            'backUrl' => route('file.post.index', $post),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $filePost->nama_dokumen],
                ['label' => 'Nama File', 'value' => $filePost->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $filePost->tanggal_upload],
            ],
            'image' => asset('storage/post/' . $filePost->nama_file),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('file.post.edit', [$post, $filePost]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('file.post.download', [$post, $filePost]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('file.post.delete', [$post, $filePost]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post, FilePost $filePost)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')], ['label' => 'File Post', 'url' => route('file.post.index', $post)]],
            'backUrl' => route('file.post.show', [$post, $filePost]),
            'method' => 'put',
            'action' => route('file.post.update', [$post, $filePost]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $filePost->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post, FilePost $filePost)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $filePost->nama_file;
        $filenameWithExt = $filePost->nama_dokumen;

        if ($request->hasFile('file')) {
            if ($filePost->nama_file != 'default.png') {
                Storage::delete("public/post/" . $filePost->nama_file);
            }

            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/post', $foto);
        }

        $filePost->update($this->filePostData($foto, $filenameWithExt));

        return redirect('/artikel/post/' . $post->post_id . '/file/' . $filePost->file_post_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Post sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, FilePost $filePost)
    {
        $filePost->delete();

        return redirect('/artikel/post/' . $post->post_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Post sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function filePostData($nama_file, $nama_dokumen)
    {
        return [
            'nama_file' => $nama_file,
            'nama_dokumen' => $nama_dokumen,
        ];
    }

    public function download(Post $post, FilePost $filePost)
    {
        return Storage::download('public/post/' . $filePost->nama_file);
    }
}
