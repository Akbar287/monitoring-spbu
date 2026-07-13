<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $artikel = Post::where('active', 1)->paginate(8);
        return Inertia::render('Welcome/Index', $this->indexProps(
            $artikel,
            'Portal Informasi SPBU',
            'Portal Informasi SPBU',
            'Pengumuman dan informasi resmi sistem monitoring SPBU.'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function kategori(Kategori $kategori)
    {
        $artikel = $kategori->post()->paginate(8);
        return Inertia::render('Welcome/Index', $this->indexProps(
            $artikel,
            'Kategori Artikel',
            'Kategori Artikel',
            'Kumpulan artikel dan pengumuman dalam kategori pilihan Anda.',
            [
                'icon' => 'fa-folder',
                'label' => 'Kategori',
                'value' => $kategori->nama_kategori,
                'detail' => $kategori->detail,
            ]
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function tag(Tag $tag)
    {
        $artikel = $tag->post()->paginate(8);
        return Inertia::render('Welcome/Index', $this->indexProps(
            $artikel,
            'Artikel Berdasarkan Tag',
            'Artikel Berdasarkan Tag',
            'Jelajahi artikel yang berkaitan dengan topik yang Anda minati.',
            [
                'icon' => 'fa-hashtag',
                'label' => 'Tag',
                'value' => '#' . $tag->nama_tag,
            ]
        ));
    }

    public function user(Member $member)
    {
        $artikel = $member->post()->paginate(8);
        return Inertia::render('Welcome/Index', $this->indexProps(
            $artikel,
            'Artikel Penulis',
            'Artikel Penulis',
            'Semua artikel dan pengumuman yang ditulis oleh penulis ini.',
            [
                'icon' => 'fa-user-pen',
                'label' => 'Penulis',
                'value' => optional($member->ktp()->first())->nama ?? 'Anonim',
            ]
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return Inertia::render('Welcome/Show', [
            'title' => $post->title,
            'previousUrl' => url()->previous(),
            'post' => [
                'title' => $post->title,
                'content' => $post->content,
                'author' => optional(optional($post->member()->first())->ktp()->first())->nama ?? 'Anonim',
                'authorUrl' => route('welcome.user', $post->member_id),
                'date' => $post->created_at->format('d F Y'),
                'categories' => $post->kategori()->get()->map(fn ($item) => [
                    'label' => $item->nama_kategori,
                    'url' => route('welcome.kategori', $item->kategori_id),
                ])->values()->all(),
                'tags' => $post->tag()->get()->map(fn ($item) => [
                    'label' => $item->nama_tag,
                    'url' => route('welcome.tag', $item->tag_id),
                ])->values()->all(),
            ],
        ]);
    }

    private function indexProps(LengthAwarePaginator $artikel, string $title, string $heroTitle, string $heroSubtitle, ?array $heading = null): array
    {
        $pagination = $artikel->toArray();

        return [
            'title' => $title,
            'heroTitle' => $heroTitle,
            'heroSubtitle' => $heroSubtitle,
            'heading' => $heading,
            'articles' => $artikel->getCollection()->map(fn ($post) => $this->article($post))->values()->all(),
            'pagination' => [
                'links' => $pagination['links'] ?? [],
            ],
        ];
    }

    private function article(Post $post): array
    {
        return [
            'id' => $post->post_id,
            'title' => $post->title,
            'author' => optional(optional($post->member()->first())->ktp()->first())->nama ?? 'Anonim',
            'date' => $post->created_at->format('d M Y'),
            'excerpt' => Str::limit(strip_tags($post->content), 120),
            'url' => route('welcome.show', $post->post_id),
        ];
    }
}
