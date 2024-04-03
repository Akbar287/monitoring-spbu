<?php

namespace App\Http\Controllers;

use App\Models\JamKerja;
use App\Models\Member;
use App\Models\Nozzle;
use Illuminate\Http\Request;

class JadwalKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employee = Member::get();
        $jamKerja = JamKerja::orderBy('nama_jam_kerja', 'asc')->get();
        $nozzle = Nozzle::select('nozzle_id', 'nama_nozzle')->orderBy('nama_nozzle', 'asc')->get();
        return view('jadwal_absensi/penjadwalan/index', compact('employee', 'jamKerja', 'nozzle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
    public function cek(Request $request)
    {
        $request->validate([
            'user' => ['required']
        ]);
    }

    public function jadwal(Request $request)
    {
        dd($request);

        $request->validate([
            'member_id' => ['required'],
            'jabatan' => ['required'],
            'tanggal' => ['required'],
            'jam_kerja' => ['required'],
            'nozzle' => ['required'],
        ]);
    }
}
