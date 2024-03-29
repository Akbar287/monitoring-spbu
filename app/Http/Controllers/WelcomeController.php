<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Post;
use App\Models\Tag;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $artikel = Post::paginate(8);
        return view('welcome.index', compact('artikel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function kategori(Kategori $kategori)
    {
        $artikel = $kategori->post()->paginate(8);
        return view('welcome.kategori', compact('kategori', 'artikel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function tag(Tag $tag)
    {
        $artikel = $tag->post()->paginate(8);
        return view('welcome.tag', compact('tag', 'artikel'));
    }

    public function user(Member $member)
    {
        $artikel = $member->post()->paginate(8);
        return view('welcome.user', compact('member', 'artikel'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('welcome.show', compact('post'));
    }
}
