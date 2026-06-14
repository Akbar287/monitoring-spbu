<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class BukuBesarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
return Inertia::render('Show/Index', [
            'title' => 'Buku Besar',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'backUrl' => route('menu.financial'),
            'notice' => ['text' => 'Fitur laporan Buku Besar masih dalam pengembangan.', 'color' => 'info'],
            'fields' => [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('other/buku_besar/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('other/buku_besar/show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('other/buku_besar/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
