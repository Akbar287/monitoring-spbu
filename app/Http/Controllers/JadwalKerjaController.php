<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\Member;
use App\Models\Nozzle;
use App\Models\Penjadwalan;
use App\Models\StatusKehadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalKerjaController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }
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
    public function destroy(Request $request)
    {
        $request->validate([
            'member_id' => ['required'],
            'title' => ['required'],
            'date' => ['required'],
        ]);

        $date = explode('T', $request->date, -1)['0'];
        $jam_kerja = JamKerja::where('nama_jam_kerja', request('title'))->first();
        $penjadwalan = $jam_kerja->penjadwalan()->where('member_id', request('member_id'))->where('tanggal', $date)->first();

        $penjadwalan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Employee has been scheduled',
            'schedule' => $this->getSchedule(request('member_id'))
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cek(Request $request)
    {
        $request->validate([
            'member_id' => ['required']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Employee has been scheduled',
            'schedule' => $this->getSchedule(request('member_id'))
        ], 200);
    }

    public function jadwal(Request $request)
    {
        $request->validate([
            'member_id' => ['required'],
            'jabatan' => ['required'],
            'tanggal' => ['required'],
            'jam_kerja' => ['required'],
            'nozzle' => ['required'],
        ]);


        $statusKehadiran = StatusKehadiran::select('status_kehadiran_id')->where('nama_kehadiran', 'Dijadwalkan')->first();
        $jabatan = Jabatan::select('jabatan_id')->where('nama_jabatan', request('jabatan'))->first();
        $nozzle = Nozzle::select('nozzle_id')->get();

        for ($i = 0; $i < count($request->tanggal); $i++) {
            $penjadwalan = $statusKehadiran->penjadwalan()->create([
                'jabatan_id' => $jabatan->jabatan_id,
                'member_id' => request('member_id'),
                'jam_kerja_id' => $request->jam_kerja[$i],
                'tanggal' => $request->tanggal[$i],
                'deskripsi' => null,
                'is_attended' => false,
                'is_confirm' => false,
                'attended' => null
            ]);

            if ($request->nozzle[$i] == 'all') {
                for ($o = 0; $o < count($nozzle); $o) {
                    DB::table('penjadwalan_nozzle')->insert([
                        'penjadwalan_id' => $penjadwalan->penjadwalan_id,
                        'nozzle_id' => $nozzle->nozzle_id
                    ]);
                }
            } else {
                DB::table('penjadwalan_nozzle')->insert([
                    'penjadwalan_id' => $penjadwalan->penjadwalan_id,
                    'nozzle_id' => $request->nozzle[$i]
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'success to create schedule',
            'schedule' => $this->getSchedule(request('member_id'))
        ], 201);
        exit;
    }

    public function getSchedule($memberId)
    {
        $data = Penjadwalan::where('member_id', $memberId)->whereBetween('tanggal', [Carbon::now()->firstOfMonth(), Carbon::now()->endOfMonth()])->get();

        $temp = [];
        foreach ($data as $schedule) {
            $temp[] = [
                'title' => $schedule->jam_kerja()->first()->nama_jam_kerja,
                'start' => $schedule->tanggal . 'T' . $schedule->jam_kerja()->first()->jam_datang_in,
                'end' => $schedule->tanggal . 'T' . $schedule->jam_kerja()->first()->jam_pulang_out,
            ];
        }
        return $temp;
    }
}
