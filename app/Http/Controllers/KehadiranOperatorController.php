<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Penjadwalan;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class KehadiranOperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');
        $ongoing = Auth::user()->member()->first()->penjadwalan()->whereNotNull('start_time')->whereNull('finish_time')->first();

        if ($request->ajax() && ! $request->inertia()) {
            $data = Penjadwalan::select('penjadwalan.penjadwalan_id', 'penjadwalan.tanggal', 'jam_kerja.nama_jam_kerja', 'nozzle.nama_nozzle', 'jam_kerja.urutan', 'penjadwalan.deskripsi', 'status_kehadiran.nama_kehadiran')
                ->join('jam_kerja', 'penjadwalan.jam_kerja_id', 'jam_kerja.jam_kerja_id')
                ->join('status_kehadiran', 'penjadwalan.status_kehadiran_id', 'status_kehadiran.status_kehadiran_id')
                ->leftJoin('penjadwalan_nozzle', 'penjadwalan_nozzle.penjadwalan_id', 'penjadwalan.penjadwalan_id')
                ->leftJoin('nozzle', 'penjadwalan_nozzle.nozzle_id', 'nozzle.nozzle_id')
                ->whereBetween('penjadwalan.tanggal', [$waktuMulai, $waktuSelesai])
                ->where('member_id', Auth::user()->member_id)
                ->where('is_attended', 0)
                ->where('is_confirm', 0)
                ->orderBy('penjadwalan.tanggal', 'desc')
                ->orderBy('jam_kerja.urutan', 'asc')
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('status', function ($row) {
                    return $row->nama_kehadiran;
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->nama_jam_kerja;
                })
                ->addColumn('nozzle', function ($row) {
                    return $row->nama_nozzle ?? 'semua nozzle';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('kehadiran.show', $row->penjadwalan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return Inertia::render('Kehadiran/Index', [
            'title' => 'Kehadiran',
            'breadcrumbs' => [],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'ongoing' => $ongoing ? [
                'nama' => optional(optional($ongoing->member()->first())->ktp()->first())->nama,
                'jamKerja' => optional($ongoing->jam_kerja()->first())->nama_jam_kerja,
                'startTime' => $ongoing->start_time,
                'tanggal' => $ongoing->tanggal,
                'url' => route('kehadiran.show', $ongoing->penjadwalan_id),
            ] : null,
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Status', 'data' => 'status'],
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
                    ['label' => 'Nozzle', 'data' => 'nozzle'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function store(Request $request, Penjadwalan $penjadwalan)
    {
        $request->validate([
            'abs' => ['required'],
            'code' => ['required']
        ]);

        $cek = true;
        $is_double = false;
        do {
            if ($penjadwalan->is_attended) {
                return response()->json([
                    'status' => true,
                    'message' => 'attendances succesfully',
                    'data' => []
                ], 200);
                exit;
            }
            $qrcode = $penjadwalan->random_string_barcode()->where('is_active', 1)->where('is_done', 0)->first();

            if (is_null($qrcode)) {
                if (!$is_double) {
                    return response()->json([
                        'status' => false,
                        'message' => 'qrcode wrong',
                        'data' => []
                    ], 200);
                    exit;
                } else {
                    return response()->json([
                        'status' => true,
                        'message' => 'attendances succesfully',
                        'data' => []
                    ], 200);
                }
            } else {
                if ($qrcode->barcode != $request->abs) {
                    return response()->json([
                        'status' => false,
                        'message' => 'qrcode not valid',
                        'data' => []
                    ], 200);
                    exit;
                } else {
                    $qrcode->update(['is_done' => 1, 'is_active' => 0]);
                    $is_double = true;
                    $cek = false;
                    if (!$penjadwalan->is_attended) {
                        $penjadwalan->update([
                            'is_attended' => 1,
                            'kode_penjadwalan' => $request->code,
                            'start_time' => date('Y-m-d H:i:s'),
                            'attended' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        return response()->json([
                            'status' => true,
                            'message' => 'attendances succesfully',
                            'data' => []
                        ], 200);
                    }

                    if ($penjadwalan->devices()->count() == 0) {
                        $userAgent = $_SERVER['HTTP_USER_AGENT'];
                        preg_match('/\((.*?)\)/', $userAgent, $matches);
                        $os = str_replace('; ', ', ', $matches[1]);
                        $penjadwalan->devices()->create([
                            'ipv4' => $this->get_client_ip(),
                            'type_devices' => $this->getTypeDevices($os),
                            'model_devices' => $this->getTypeDevices($os),
                            'os' => $os,
                            'versi_os' => '0',
                            'browser' => $this->get_browser_name($userAgent),
                            'versi_browser' => '0'
                        ]);
                    }

                    if ($penjadwalan->location()->count() == 0) {
                        $user_ip = getenv('REMOTE_ADDR');
                        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
                        $penjadwalan->location()->create([
                            'lattitude' => $geo['geoplugin_latitude'],
                            'longitude' => $geo['geoplugin_longitude'],
                            'gmt' => $this->getGmt($geo['geoplugin_timezone']),
                            'deskripsi' => null
                        ]);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'attendances succesfully',
                        'data' => []
                    ], 200);
                    exit;
                }
            }
        } while ($cek);
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjadwalan $penjadwalan)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        preg_match('/\((.*?)\)/', $userAgent, $matches);
        $osAndVersionOs = str_replace('; ', ', ', $matches[1]);
        $utility = [
            'ip' => $this->get_client_ip(),
            'typeDevice' => $this->getTypeDevices($osAndVersionOs),
            'osAndVersionOs' => $osAndVersionOs,
            'browser' => $this->get_browser_name($userAgent),
            'latitude' => $geo['geoplugin_latitude'] ?? 0,
            'longitude' => $geo['geoplugin_longitude'] ?? 0,
            'gmt' => $this->getGmt($geo['geoplugin_timezone'] ?? 0),
            'city' => $geo['geoplugin_city'] ?? 0,
        ];

        $penjadwalan = Penjadwalan::select('penjadwalan.penjadwalan_id', 'penjadwalan.tanggal', 'penjadwalan.deskripsi', 'penjadwalan.is_attended', 'penjadwalan.is_confirm', 'penjadwalan.start_time', 'penjadwalan.finish_time', 'penjadwalan.attended', 'ktp.nama', 'jabatan.nama_jabatan', 'jam_kerja.nama_jam_kerja', 'status_kehadiran.nama_kehadiran')
            ->join('member', 'member.member_id', 'penjadwalan.member_id')
            ->join('ktp', 'ktp.ktp_id', 'member.ktp_id')
            ->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')
            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjadwalan.jam_kerja_id')
            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
            ->where('penjadwalan.penjadwalan_id', $penjadwalan->penjadwalan_id)
            ->first();

        $ongoing = !is_null(Auth::user()->member()->first()->penjadwalan()->whereNotNull('start_time')->whereNull('finish_time')->first()); // jika ada nilainya true

        return view('absensi.presensi.show', compact('penjadwalan', 'utility', 'ongoing'));
    }

    public function close(Request $request, Penjadwalan $penjadwalan)
    {
        if (is_null($penjadwalan->finish_time)) {
            $penjadwalan->update([
                'finish_time' => date('Y-m-d H:i:s')
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'attendances succesfully',
            'data' => []
        ], 200);
        exit;
    }

    public function getGmt($gmt_value)
    {
        if ($gmt_value === 0) return 0;
        $timezone = new DateTimeZone($gmt_value);
        $now = new DateTime('now', $timezone);
        $offset_hours = $now->getOffset() / 3600;
        return $offset_hours;
    }

    function get_browser_name($user_agent)
    {
        if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
        elseif (strpos($user_agent, 'Edge')) return 'Edge';
        elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
        elseif (strpos($user_agent, 'Safari')) return 'Safari';
        elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
        elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';

        return 'Other';
    }

    public function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    public function getTypeDevices($os)
    {
        return !$this->isMobile() ? 'Komputer' : 'Mobile';
    }


    function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
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
    public function destroy(string $id)
    {
        //
    }
}
