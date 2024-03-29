<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LabaRugiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('financial/laba_rugi/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('financial/laba_rugi/create');
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
        return view('financial/laba_rugi/show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('financial/laba_rugi/edit');
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
