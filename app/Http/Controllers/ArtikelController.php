<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Kategori",
                "icon" => "filter",
                "url" => route('kategori.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Tag",
                "icon" => "flask",
                "url" => route('tag.index'),
                "color" => "info"
            ],
            [
                "nama" => "Post",
                "icon" => "tint",
                "url" => route('post.index'),
                "color" => "success"
            ]
        ];
        return view('article/index', compact('allMenu'));
    }
}
