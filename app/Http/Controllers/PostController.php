<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Kategori;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')]],
            'createUrl' => route('post.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Nama Judul', 'data' => 'title'],
                    ['label' => 'Status', 'data' => 'status'],
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
        $kategori = Kategori::get();
        $tag = Tag::get();

        return Inertia::render('Form/Index', [
            'title' => 'Tambah Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')]],
            'backUrl' => route('post.index'),
            'method' => 'post',
            'action' => route('post.store'),
            'fields' => $this->postFields($tag, $kategori, null),
        ]);
    }

    /**
     * Descriptor field form Post (create: $post null, edit: nilai dari $post).
     */
    private function postFields($tag, $kategori, ?Post $post): array
    {
        return [
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'value' => $post?->title],
            ['name' => 'content', 'label' => 'Konten', 'type' => 'textarea', 'rows' => 6, 'value' => $post?->content],
            ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'multiselect', 'col' => 'col-md-6',
                'value' => $post ? $post->kategori()->pluck('kategori.kategori_id')->map(fn ($v) => (string) $v)->all() : [],
                'options' => $kategori->map(fn ($x) => ['value' => $x->kategori_id, 'label' => $x->nama_kategori])->values()->all()],
            ['name' => 'tag', 'label' => 'Tag', 'type' => 'multiselect', 'col' => 'col-md-6',
                'value' => $post ? $post->tag()->pluck('tag.tag_id')->map(fn ($v) => (string) $v)->all() : [],
                'options' => $tag->map(fn ($x) => ['value' => $x->tag_id, 'label' => $x->nama_tag])->values()->all()],
            ['name' => 'active', 'label' => 'Status', 'type' => 'select',
                'value' => $post ? (filter_var($post->active, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
        ];
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

        // syncWithoutDetaching is idempotent: it skips pivot rows that already
        // exist, so re-submitting or duplicate selections won't violate the
        // (tag_id, post_id) / (kategori_id, post_id) unique constraints.
        $post->tag()->syncWithoutDetaching(array_unique($postRequest->tag));
        $post->kategori()->syncWithoutDetaching(array_unique($postRequest->kategori));

        return redirect('/artikel/post/' . $post->post_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Post sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')]],
            'backUrl' => route('post.index'),
            'fields' => [
                ['label' => 'Judul', 'value' => $post->title],
                ['label' => 'Tanggal', 'value' => $post->created_at->format('d F Y')],
                ['label' => 'Status', 'value' => filter_var($post->active, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Kategori', 'value' => $post->kategori()->pluck('nama_kategori')->implode(', ')],
                ['label' => 'Tag', 'value' => $post->tag()->pluck('nama_tag')->map(fn ($t) => '#' . $t)->implode(' ')],
                ['label' => 'Konten', 'value' => $post->content],
            ],
            'actions' => [
                ['label' => 'Ubah', 'url' => route('post.edit', $post), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'File', 'url' => route('file.post.index', $post), 'color' => 'info', 'icon' => 'folder-open'],
            ],
            'deleteUrl' => route('post.delete', $post),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $kategori = Kategori::get();
        $tag = Tag::get();

        return Inertia::render('Form/Index', [
            'title' => 'Ubah Post',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Post', 'url' => route('post.index')]],
            'backUrl' => route('post.show', $post),
            'method' => 'put',
            'action' => route('post.update', $post),
            'fields' => $this->postFields($tag, $kategori, $post),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $postRequest, Post $post)
    {
        $post->update($this->postData());

        // sync detaches removed items and attaches new ones in one step,
        // without ever duplicating an existing pivot row.
        $post->tag()->sync(array_unique($postRequest->tag));
        $post->kategori()->sync(array_unique($postRequest->kategori));

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
