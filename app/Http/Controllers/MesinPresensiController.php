<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MesinPresensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('absensi/mesin/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('absensi/mesin/create');
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
        return view('absensi/mesin/show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('absensi/mesin/edit');
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
