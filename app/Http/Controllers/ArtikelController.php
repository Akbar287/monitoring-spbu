<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $postAktif = DB::table('post')->whereNull('deleted_at')->where('active', 1)->count();
        $postNonaktif = DB::table('post')->whereNull('deleted_at')->where('active', 0)->count();

        $charts = [
            'ringkasan' => [
                'labels' => ['Post', 'Kategori', 'Tag'],
                'data' => [
                    DB::table('post')->whereNull('deleted_at')->count(),
                    DB::table('kategori')->count(),
                    DB::table('tag')->count(),
                ],
            ],
            'status' => [
                'labels' => ['Aktif', 'Nonaktif'],
                'data' => [$postAktif, $postNonaktif],
            ],
            'bulanan' => Charts::monthly('post', 'created_at', 'COUNT(*)'),
        ];

        return view('article/index', compact('allMenu', 'charts'));
    }
}
