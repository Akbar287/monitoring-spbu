<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Kategori;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Post::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return $row->created_at->format('d F Y');
                })
                ->addColumn('status', function ($row) {
                    return $row->active ? 'Aktif' : 'Tidak Aktif';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('post.show', $row->post_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('article/post/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = Kategori::get();
        $tag = Tag::get();
        return view('article/post/create', compact('tag', 'kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $postRequest)
    {
        $post = Post::where('member_id', Auth::user()->member_id)->where('title', request('title'))->where('content', request('content'))->where('active', request('active') == 'true')->first();
        if (is_null($post)) {
            $post = Post::create($this->postData());
        }

        if (count($postRequest->tag) > 0) {
            foreach ($postRequest->tag as $t) {
                DB::table('tag_post')->insert([
                    'post_id' => $post->post_id,
                    'tag_id' => $t
                ]);
            }
        }
        if (count($postRequest->kategori) > 0) {
            foreach ($postRequest->kategori as $k) {
                DB::table('kategori_post')->insert([
                    'post_id' => $post->post_id,
                    'kategori_id' => $k
                ]);
            }
        }

        return redirect('/artikel/post/' . $post->post_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Post sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('article/post/show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $kategori = Kategori::get();
        $tag = Tag::get();
        return view('article/post/edit', compact('tag', 'kategori', 'post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $postRequest, Post $post)
    {
        $post->update($this->postData());
        DB::table('tag_post')->where('post_id', $post->post_id)->delete();
        if (count($postRequest->tag) > 0) {
            foreach ($postRequest->tag as $t) {
                DB::table('tag_post')->insert([
                    'post_id' => $post->post_id,
                    'tag_id' => $t
                ]);
            }
        }
        DB::table('kategori_post')->where('post_id', $post->post_id)->delete();
        if (count($postRequest->kategori) > 0) {
            foreach ($postRequest->kategori as $k) {
                DB::table('kategori_post')->insert([
                    'post_id' => $post->post_id,
                    'kategori_id' => $k
                ]);
            }
        }

        return redirect('/artikel/post/' . $post->post_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Post sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        DB::table('tag_post')->where('post_id', $post->post_id)->delete();
        DB::table('kategori_post')->where('post_id', $post->post_id)->delete();
        $post->delete();

        return redirect('/artikel/post')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Post sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function postData()
    {
        return [
            'member_id' => Auth::user()->member_id,
            'title' => request('title'),
            'content' => request('content'),
            'active' => request('active') == 'true',
        ];
    }
}
