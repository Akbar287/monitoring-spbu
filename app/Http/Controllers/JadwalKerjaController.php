<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Imports\JadwalKerjaImport;
use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\Member;
use App\Models\Nozzle;
use App\Models\Penjadwalan;
use App\Models\StatusKehadiran;
use App\Models\UserLogin;
use App\Models\ValidateBarcode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

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
        return Inertia::render('JadwalKerja/Index', [
            'title' => 'Penjadwalan Pegawai',
            'breadcrumbs' => [['label' => 'Jadwal & Absensi', 'url' => route('menu.jadwal_absensi')]],
            'employees' => $employee->map(fn ($u) => [
                'id' => $u->member_id,
                'nama' => optional($u->ktp()->first())->nama,
                'jabatan' => $u->jabatan()->select('nama_jabatan')->pluck('nama_jabatan')->all(),
            ])->values()->all(),
            'jamKerjaOptions' => $jamKerja->map(fn ($s) => ['value' => $s->jam_kerja_id, 'label' => $s->nama_jam_kerja])->values()->all(),
            'nozzleOptions' => $nozzle->map(fn ($s) => ['value' => $s->nozzle_id, 'label' => $s->nama_nozzle])->values()->all(),
            'api' => [
                'cek' => route('jadwal_kerja.cek_api'),
                'store' => route('jadwal_kerja.jadwal_api'),
                'delete' => route('jadwal_kerja.destroy_api'),
            ],
            'links' => [
                'qr' => route('jadwal-kerja.qr'),
                'upload' => route('jadwal-kerja.upload_file'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function template()
    {
        return response()->json([
            'status' => 'no template found',
            'message' => 'Hubungi Programmer Untuk Meminta file Template',
            'data' => []
        ], 200);
        exit;
    }

    public function upload_file(Request $request)
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
        $member = Member::select('member.member_id', 'ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->orderBy('ktp.nama', 'asc')->get();
        $jamKerja = JamKerja::select('jam_kerja_id', 'nama_jam_kerja')->orderBy('nama_jam_kerja', 'asc')->get();
        $memberSelected = 'all';
        $jamKerjaSelected = 'all';

        return Inertia::render('JadwalKerja/Upload', [
            'title' => 'Upload Jadwal Kerja',
            'breadcrumbs' => [
                ['label' => 'Jadwal & Absensi', 'url' => route('menu.jadwal_absensi')],
                ['label' => 'Penjadwalan', 'url' => route('jadwal-kerja.index')],
            ],
            'deleteAction' => route('jadwal-kerja.upload_file.delete'),
            'uploadAction' => route('jadwal-kerja.upload_file.store'),
            'templateUrl' => route('jadwal-kerja.upload_file.template'),
            'backUrl' => route('jadwal-kerja.index'),
            'filters' => [
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'member' => $memberSelected,
                'jam_kerja' => $jamKerjaSelected,
            ],
            'members' => $member->map(fn ($item) => ['value' => $item->member_id, 'label' => $item->nama])->values()->all(),
            'jamKerja' => $jamKerja->map(fn ($item) => ['value' => $item->jam_kerja_id, 'label' => $item->nama_jam_kerja])->values()->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function upload_file_store(Request $request)
    {
        $request->validate([
            'file' => ['required']
        ]);

        $path1 = 'temp/' . $request->file('file')->hashName();
        $name = (explode('.', $path1));

        if (count($name) == 1) {
            $name[] = 'xls';
        }

        $name = (implode('.', $name));
        $request->file('file')->storeAs('temp', $name);
        $path = storage_path('app') . '/temp/' . $name;

        $rows = Excel::toArray(new JadwalKerjaImport, $path);

        unset($rows[0][0]);
        $rows = $rows['0'];

        // Inisiasi
        $statusKehadiran = StatusKehadiran::select('status_kehadiran_id')->where('nama_kehadiran', 'Dijadwalkan')->first();

        $jabatanDb = Jabatan::select('jabatan_id', 'nama_jabatan')->get()->toArray();
        $jabatan = [];
        foreach ($jabatanDb as $item) {
            $jabatan[$item['nama_jabatan']] = $item['jabatan_id'];
        }
        unset($jabatanDb);

        $memberDB = UserLogin::select('member_id', 'username')->get()->toArray();
        $member = [];
        foreach ($memberDB as $item) {
            $member[$item['username']] = $item['member_id'];
        }
        unset($memberDB);

        $nozzleDB = Nozzle::select('nozzle_id', 'nama_nozzle')->get()->toArray();
        $nozzle = [];
        foreach ($nozzleDB as $item) {
            $nozzle[$item['nama_nozzle']] = $item['nozzle_id'];
        }
        unset($nozzleDB);

        $jamKerjaDB = JamKerja::select('jam_kerja_id', 'nama_jam_kerja')->get()->toArray();
        $jamKerja = [];
        foreach ($jamKerjaDB as $item) {
            $jamKerja[$item['nama_jam_kerja']] = $item['jam_kerja_id'];
        }
        unset($jamKerjaDB);
        $loop = 1;
        // End Inisiasi

        // Each Every Row in Excel
        foreach ($rows as $r) {
            if (!is_null($r['0']) || !is_null($r['1']) || !is_null($r['2']) || !is_null($r['3']) || !is_null($r['4'])) {
                $tgl = gmdate('Y-m-d', ($r['2'] - 25569) * 86400);
                $penjadwalan = $statusKehadiran->penjadwalan()->where('jabatan_id', $jabatan[$r['1']])->where('member_id', $member[$r['0']])->where('jam_kerja_id', $jamKerja[$r['3']])->where('tanggal', $tgl)->first();

                if (is_null($penjadwalan)) {
                    $penjadwalan = $statusKehadiran->penjadwalan()->create([
                        'jabatan_id' => $jabatan[$r['1']],
                        'member_id' => $member[$r['0']],
                        'jam_kerja_id' => $jamKerja[$r['3']],
                        'tanggal' => $tgl,
                        'deskripsi' => null,
                        'is_attended' => 0,
                        'is_confirm' => 0,
                        'attended' => null
                    ]);
                    $loop += 1;

                    if ($r['4'] == 'all') {
                        foreach ($nozzle as $n) {
                            DB::table('penjadwalan_nozzle')->insert([
                                'penjadwalan_id' => $penjadwalan->penjadwalan_id,
                                'nozzle_id' => $n
                            ]);
                        }
                    } else {
                        DB::table('penjadwalan_nozzle')->insert([
                            'penjadwalan_id' => $penjadwalan->penjadwalan_id,
                            'nozzle_id' => $nozzle[$r['4']]
                        ]);
                    }
                }
            }
        }
        unset($statusKehadiran);
        unset($tgl);
        unset($jabatan);
        unset($nozzle);
        unset($member);
        unset($jamKerja);
        unset($penjadwalan);

        // Delete File excel in Server
        Storage::delete('temp/' . $name);

        return redirect('/jadwal_absensi/jadwal/upload/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Jadwal Kerja Sudah Di upload. ' . $loop . ' baris dieksekusi<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function qrcode(Request $request)
    {
        $member = Member::select('member.member_id', 'ktp.nama')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->orderBy('ktp.nama', 'asc')->get();
        $statusKehadiran = StatusKehadiran::orderBy('nama_kehadiran', 'asc')->get();
        $tanggal = request('tanggal') ?? Carbon::now()->format('Y-m-d');
        $memberSelected = request('member') ?? 'all';
        $statusKehadiranSelected = request('status_kehadiran_id') ?? 'all';
        $this->checkAndCreateQrCode();

        if ($statusKehadiranSelected === 'all') {
            if ($memberSelected === 'all') {
                $data = Penjadwalan::select('random_string_barcode.is_active', 'random_string_barcode.barcode', 'random_string_barcode.finish', 'penjadwalan.tanggal', 'jabatan.nama_jabatan', 'jam_kerja.nama_jam_kerja', 'ktp.nama')->join('member', 'member.member_id', 'penjadwalan.member_id')->join('ktp', 'member.ktp_id', 'ktp.ktp_id')->join('random_string_barcode', 'random_string_barcode.penjadwalan_id', 'penjadwalan.penjadwalan_id')->join('jam_kerja', 'penjadwalan.jam_kerja_id', 'jam_kerja.jam_kerja_id')->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')->where('random_string_barcode.is_active', '1')->orderBy('tanggal', 'desc')->where('tanggal', $tanggal)->get();
            } else {
                $data = Penjadwalan::select('random_string_barcode.is_active', 'random_string_barcode.barcode', 'random_string_barcode.finish', 'penjadwalan.tanggal', 'jabatan.nama_jabatan', 'jam_kerja.nama_jam_kerja', 'ktp.nama')->join('member', 'member.member_id', 'penjadwalan.member_id')->join('ktp', 'member.ktp_id', 'ktp.ktp_id')->join('random_string_barcode', 'random_string_barcode.penjadwalan_id', 'penjadwalan.penjadwalan_id')->join('jam_kerja', 'penjadwalan.jam_kerja_id', 'jam_kerja.jam_kerja_id')->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')->where('random_string_barcode.is_active', '1')->orderBy('tanggal', 'desc')->where('tanggal', $tanggal)->where('member_id', $memberSelected)->get();
            }
        } else {
            if ($memberSelected === 'all') {
                $data = StatusKehadiran::where('status_kehadiran_id', $statusKehadiranSelected)->first()->penjadwalan()->select('random_string_barcode.is_active', 'random_string_barcode.barcode', 'random_string_barcode.finish', 'penjadwalan.tanggal', 'jabatan.nama_jabatan', 'jam_kerja.nama_jam_kerja', 'ktp.nama')->join('member', 'member.member_id', 'penjadwalan.member_id')->join('ktp', 'member.ktp_id', 'ktp.ktp_id')->join('random_string_barcode', 'random_string_barcode.penjadwalan_id', 'penjadwalan.penjadwalan_id')->join('jam_kerja', 'penjadwalan.jam_kerja_id', 'jam_kerja.jam_kerja_id')->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')->where('random_string_barcode.is_active', '1')->orderBy('tanggal', 'desc')->where('tanggal', $tanggal)->get();
            } else {
                $data = StatusKehadiran::where('status_kehadiran_id', $statusKehadiranSelected)->first()->penjadwalan()->select('random_string_barcode.is_active', 'random_string_barcode.barcode', 'random_string_barcode.finish', 'penjadwalan.tanggal', 'jabatan.nama_jabatan', 'jam_kerja.nama_jam_kerja', 'ktp.nama')->join('member', 'member.member_id', 'penjadwalan.member_id')->join('ktp', 'member.ktp_id', 'ktp.ktp_id')->join('random_string_barcode', 'random_string_barcode.penjadwalan_id', 'penjadwalan.penjadwalan_id')->join('jam_kerja', 'penjadwalan.jam_kerja_id', 'jam_kerja.jam_kerja_id')->join('jabatan', 'jabatan.jabatan_id', 'penjadwalan.jabatan_id')->where('random_string_barcode.is_active', '1')->where('member_id', $memberSelected)->orderBy('tanggal', 'desc')->where('tanggal', $tanggal)->get();
            }
        }
        return Inertia::render('JadwalKerja/Qr', [
            'title' => 'QR Code',
            'breadcrumbs' => [
                ['label' => 'Jadwal & Absensi', 'url' => route('menu.jadwal_absensi')],
                ['label' => 'Penjadwalan', 'url' => route('jadwal-kerja.index')],
            ],
            'backUrl' => route('jadwal-kerja.index'),
            'filters' => [
                'tanggal' => $tanggal,
                'member' => $memberSelected,
                'status_kehadiran_id' => $statusKehadiranSelected,
            ],
            'members' => $member->map(fn ($item) => ['value' => $item->member_id, 'label' => $item->nama])->values()->all(),
            'statuses' => $statusKehadiran->map(fn ($item) => ['value' => $item->status_kehadiran_id, 'label' => $item->nama_kehadiran])->values()->all(),
            'items' => $data->map(fn ($item) => [
                'barcode' => $item->barcode,
                'nama' => $item->nama,
                'qr' => (string) \QrCode::size(150)->generate($item->barcode),
                'tanggalJam' => Carbon::createFromFormat('Y-m-d', $item->tanggal)->format('d F Y') . ' (' . $item->nama_jam_kerja . ')',
                'jabatan' => $item->nama_jabatan,
                'finish' => Carbon::createFromFormat('Y-m-d H:i:s', $item->finish)->diffForHumans(),
            ])->values()->all(),
        ]);
    }

    public function checkAndCreateQrCode()
    {
        $penjadwalanDijadwalkan = StatusKehadiran::where('nama_kehadiran', 'Dijadwalkan')->first()->penjadwalan()->get();
        $validateBarcode = ValidateBarcode::where('is_active', 1)->first();

        foreach ($penjadwalanDijadwalkan as $dj) {
            if ($dj->random_string_barcode()->count() == 0) {
                // create
                // Instance a Hash to Barcode
                $hash = hash('sha512', 'BARBIE_SPBU2434520_' . $dj->tanggal . '_' . $dj->member_id . '_' . $dj->jabatan_id . Str::random(30));

                // Create Start dan Finish
                $start = Carbon::now();
                $finish = Carbon::now()->addSeconds($validateBarcode->is_expired);

                if ($dj->random_string_barcode()->where('barcode', $hash)->where('validate_barcode_id', $validateBarcode->validate_barcode_id)->where('start', $start->format('Y-m-d H:i:s'))->where('finish', $finish->format('Y-m-d H:i:s'))->where('is_done', 0)->where('is_active', 1)->count() == 0) {
                    $dj->random_string_barcode()->create([
                        'barcode' => $hash,
                        'validate_barcode_id' => $validateBarcode->validate_barcode_id,
                        'start' => $start->format('Y-m-d H:i:s'),
                        'finish' => $finish->format('Y-m-d H:i:s'),
                        'is_done' => 0,
                        'is_active' => 1,
                    ]);
                }
            } else {
                if ($dj->random_string_barcode()->where('is_active', 1)->where('finish', '>', Carbon::now()->format('Y-m-d H:i:s'))->count() == 0) {
                    // update all data to is_active is 0
                    foreach ($dj->random_string_barcode()->where('is_active', 1)->get() as $up) {
                        $up->update(['is_active' => 0]);
                    }
                    // create

                    // Instance a Hash to Barcode
                    $hash = hash('sha512', 'BARBIE_SPBU2434520_' . $dj->tanggal . '_' . $dj->member_id . '_' . $dj->jabatan_id . Str::random(30));

                    // Create Start dan Finish
                    $start = Carbon::now();
                    $finish = Carbon::now()->addSeconds($validateBarcode->is_expired);

                    if ($dj->random_string_barcode()->where('barcode', $hash)->where('validate_barcode_id', $validateBarcode->validate_barcode_id)->where('start', $start->format('Y-m-d H:i:s'))->where('finish', $finish->format('Y-m-d H:i:s'))->where('is_done', 0)->where('is_active', 1)->count() == 0) {
                        $dj->random_string_barcode()->create([
                            'barcode' => $hash,
                            'validate_barcode_id' => $validateBarcode->validate_barcode_id,
                            'start' => $start->format('Y-m-d H:i:s'),
                            'finish' => $finish->format('Y-m-d H:i:s'),
                            'is_done' => 0,
                            'is_active' => 1,
                        ]);
                    }
                } else {
                    if ($dj->random_string_barcode()->count() == 0) {
                        dd($dj->random_string_barcode()->count());
                    }
                    return;
                }
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'waktu_mulai' => ['required'],
            'waktu_selesai' => ['required'],
            'jam_kerja' => ['required'],
            'member' => ['required'],
        ]);

        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');

        if ($request->jam_kerja == 'all') {
            if ($request->member == 'all') {
                Penjadwalan::whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->delete();
            } else {
                $member = Member::where('member_id', $request->member)->first();
                if (!is_null($member)) {
                    $member->penjadwalan()->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->delete();
                }
            }
        } else {
            if ($request->member == 'all') {
                $jamKerja = JamKerja::where('jam_kerja_id', $request->jam_kerja)->first();
                if (!is_null($jamKerja)) {
                    $jamKerja->penjadwalan()->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->delete();
                }
            } else {
                $member = Member::where('member_id', $request->member)->first();
                $jamKerja = JamKerja::where('jam_kerja_id', $request->jam_kerja)->first();

                $jamKerja->penjadwalan()->where('member_id', $member->member_id)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->delete();
            }
        }

        return redirect('/jadwal_absensi/jadwal/upload/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Jadwal Kerja Sudah Di hapus. <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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

        if (!is_null($penjadwalan)) {
            $penjadwalan->delete();
        }

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


        // Create Barcode
        $statusKehadiran = StatusKehadiran::select('status_kehadiran_id')->where('nama_kehadiran', 'Dijadwalkan')->first();
        $jabatan = Jabatan::select('jabatan_id')->where('nama_jabatan', request('jabatan'))->first();
        $nozzle = Nozzle::select('nozzle_id')->get();

        for ($i = 0; $i < count($request->tanggal); $i++) {
            $penjadwalan = $statusKehadiran->penjadwalan()->where('jabatan_id', $jabatan->jabatan_id)->where('member_id', request('member_id'))->where('jam_kerja_id', $request->jam_kerja[$i])->where('tanggal', $request->tanggal[$i])->first();
            if (is_null($penjadwalan)) {
                $penjadwalan = $statusKehadiran->penjadwalan()->create([
                    'jabatan_id' => $jabatan->jabatan_id,
                    'member_id' => request('member_id'),
                    'jam_kerja_id' => $request->jam_kerja[$i],
                    'tanggal' => $request->tanggal[$i],
                    'deskripsi' => null,
                    'is_attended' => 0,
                    'is_confirm' => 0,
                    'attended' => null
                ]);

                // Create Start dan Finish
                $hash = hash('sha512', 'BARBIE_SPBU2434520_' . $penjadwalan->tanggal . '_' . $penjadwalan->member_id . '_' . $penjadwalan->jabatan_id . Str::random(30));
                $validateBarcode = ValidateBarcode::where('is_active', 1)->first();
                $start = Carbon::now();
                $finish = Carbon::now()->addSeconds($validateBarcode->is_expired);

                if ($penjadwalan->random_string_barcode()->where('barcode', $hash)->where('validate_barcode_id', $validateBarcode->validate_barcode_id)->where('start', $start->format('Y-m-d H:i:s'))->where('finish', $finish->format('Y-m-d H:i:s'))->where('is_done', 0)->where('is_active', 1)->count() == 0) {
                    $penjadwalan->random_string_barcode()->create([
                        'barcode' => $hash,
                        'validate_barcode_id' => $validateBarcode->validate_barcode_id,
                        'start' => $start->format('Y-m-d H:i:s'),
                        'finish' => $finish->format('Y-m-d H:i:s'),
                        'is_done' => 0,
                        'is_active' => 1,
                    ]);
                }
                // End Barcode

                // Relation Nozzle
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
                // End Relation Nozzle
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
    function transpose($array_one)
    {
        $array_two = [];
        foreach ($array_one as $key => $item) {
            foreach ($item as $subkey => $subitem) {
                $array_two[$subkey][$key] = $subitem;
            }
        }
        return $array_two;
    }
}
