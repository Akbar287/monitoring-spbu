<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
            [
                'id' => 'ringkasan',
                'title' => 'Jumlah Konten',
                'icon' => 'newspaper',
                'color' => 'primary',
                'badge' => 'Item',
                'col' => 'col-lg-4 col-12',
                'type' => 'bar',
                'data' => [
                    'labels' => ['Post', 'Kategori', 'Tag'],
                    'data' => [
                        DB::table('post')->whereNull('deleted_at')->count(),
                        DB::table('kategori')->count(),
                        DB::table('tag')->count(),
                    ],
                ],
                'opts' => ['horizontal' => true],
            ],
            [
                'id' => 'status',
                'title' => 'Status Publikasi Post',
                'icon' => 'toggle-on',
                'color' => 'success',
                'col' => 'col-lg-3 col-12',
                'type' => 'doughnut',
                'data' => ['labels' => ['Aktif', 'Nonaktif'], 'data' => [$postAktif, $postNonaktif]],
            ],
            [
                'id' => 'bulanan',
                'title' => 'Post Diterbitkan per Bulan',
                'icon' => 'chart-line',
                'color' => 'info',
                'badge' => 'Post',
                'col' => 'col-lg-5 col-12',
                'type' => 'line',
                'data' => Charts::monthly('post', 'created_at', 'COUNT(*)'),
                'opts' => ['label' => 'Post'],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Artikel',
            'description' => 'Jumlah konten, status publikasi, dan tren publikasi artikel.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
