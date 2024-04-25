<?php

namespace App\Http\Controllers;

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
        if ($request->ajax()) {
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
        return view('article/file_post/index', compact('post'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Post $post)
    {
        return view('article/file_post/create', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post)
    {
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
        return view('article/file_post/show', compact('post', 'filePost'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post, FilePost $filePost)
    {
        return view('article/file_post/edit', compact('post', 'filePost'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post, FilePost $filePost)
    {
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
