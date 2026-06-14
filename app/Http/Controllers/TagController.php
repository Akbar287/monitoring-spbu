<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Tag::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('tag.show', $row->tag_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Tag',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')]],
            'createUrl' => route('tag.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Tag', 'data' => 'nama_tag'],
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
            'title' => 'Tambah Tag',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Tag', 'url' => route('tag.index')]],
            'backUrl' => route('tag.index'),
            'method' => 'post',
            'action' => route('tag.store'),
            'fields' => [
                ['name' => 'nama_tag', 'label' => 'Nama Tag', 'type' => 'text', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagRequest $tagRequest)
    {
        $tag = Tag::where('nama_tag', request('nama_tag'))->first();
        if (is_null($tag)) {
            $tag = Tag::create($this->tagData());
        }

        return redirect('/artikel/tag')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Tag sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Tag',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Tag', 'url' => route('tag.index')]],
            'backUrl' => route('tag.index'),
            'fields' => [
                ['label' => 'Nama Tag', 'value' => $tag->nama_tag],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('tag.edit', $tag), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('tag.delete', $tag),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Tag',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Tag', 'url' => route('tag.index')]],
            'backUrl' => route('tag.index'),
            'method' => 'put',
            'action' => route('tag.update', $tag),
            'fields' => [
                ['name' => 'nama_tag', 'label' => 'Nama Tag', 'type' => 'text', 'value' => $tag->nama_tag],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TagRequest $tagRequest, Tag $tag)
    {
        $tag->update($this->tagData());

        return redirect('/artikel/tag')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Tag sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect('/artikel/tag/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Tag sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function tagData()
    {
        return [
            'nama_tag' => request('nama_tag')
        ];
    }
}
