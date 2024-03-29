<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Jabatan;
use App\Models\Ktp;
use App\Models\Member;
use App\Models\StatusMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Route;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Member::select('member.member_id')->addSelect('member.avatar')->addSelect('ktp.nama')->addSelect('ktp.verified')->addSelect('userlogin.username')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->join('userlogin', 'userlogin.member_id', 'member.member_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($row) {
                    $actionBtn = '<img class="img img-thumbnail" style="width: 80;height: 80px;" src="' . asset('/storage/profile_img/' . ($row->avatar != null || $row->avatar != '' ? $row->avatar : 'default.png')) . '" />';
                    return $actionBtn;
                })
                ->addColumn('verified', function ($row) {
                    $actionBtn = $row->verified == true ? 'Terverifikasi' : 'Tidak Verifikasi';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('member.show', $row->member_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'avatar'])
                ->make(true);
        }
        return view('master/member/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statusMember = StatusMember::all();
        return view('master/member/create', compact('statusMember'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberRequest $memberRequest)
    {
        $ktp = Ktp::create($this->ktpData());
        $member = $ktp->member()->create($this->memberData());
        $userlogin = $member->user_login()->create($this->userloginData());

        return redirect('/master/member/' . $member->member_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Member sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        return view('master/member/show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $statusMember = StatusMember::all();
        return view('master/member/edit', compact('member', 'statusMember'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $memberRequest, Member $member)
    {
        $member->update($this->memberData());
        $member->ktp()->update($this->ktpData());
        $member->user_login()->update($this->userloginData($memberRequest->password != null || $memberRequest->password != '' ? $memberRequest->password : $member->user_login()->first()->password));

        return redirect('/master/member/' . $member->member_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Member sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        $member->user_login()->delete();
        $member->ktp()->delete();
        $member->delete();

        return redirect('/master/member')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Member sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function ktpData()
    {
        return [
            'nik' => request('nik'),
            'nama' => request('nama'),
            'tempat_lahir' => request('tempat_lahir'),
            'tanggal_lahir' => request('tanggal_lahir'),
            'jenis_kelamin' => request('jenis_kelamin'),
            'verified' => request('verified'),
        ];
    }
    public function memberData()
    {
        return [
            'status_member_id' => request('status_member_id'),
            'email' => request('email'),
            'no_hp' => request('no_hp'),
            'no_wa' => request('no_wa'),
            'avatar' => request('avatar') != null ? request('avatar') : 'default.png',
            'bergabung_sejak' => request('bergabung_sejak')
        ];
    }
    public function userloginData($password = null)
    {
        return [
            'username' => request('username'),
            'password' => $password != null ? $password : request('password'),
            'is_aktif' => request('is_aktif') != null ? request('is_aktif') : false,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function member_jabatan_index(Request $request, Member $member)
    {
        if ($request->ajax()) {
            $data = Member::select('member.member_id')->addSelect('jabatan_member.jabatan_id')->addSelect('jabatan.nama_jabatan')->join('jabatan_member', 'jabatan_member.member_id', 'member.member_id')->join('jabatan', 'jabatan.jabatan_id', 'jabatan_member.jabatan_id')->where('member.member_id', $member->member_id)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_divisi', function ($row) {
                    return Jabatan::find($row->jabatan_id)->level()->first()->divisi()->first()->nama_divisi;
                })
                ->addColumn('nama_level', function ($row) {
                    return Jabatan::find($row->jabatan_id)->level()->first()->nama_level;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<form action="' . route('member.jabatan.delete', [$row->member_id, $row->jabatan_id]) . '" method="POST"><input type="hidden" name="_method" value="delete">' . csrf_field() . '<button class="edit btn btn-danger btn-sm">Hapus</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/member_jabatan/index', compact('member'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function member_jabatan_create(Request $request, Member $member)
    {
        if ($request->ajax()) {
            $data = Jabatan::select('jabatan.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_divisi', function ($row) {
                    return $row->level()->first()->divisi()->first()->nama_divisi;
                })
                ->addColumn('nama_level', function ($row) {
                    return $row->level()->first()->nama_level;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = $row->member()->where('member.member_id', Route::getCurrentRoute()->originalParameters()['member'])->count() > 0 ? 'Sudah'  : '<form action="' . url('/master/member/' . Route::getCurrentRoute()->originalParameters()['member'] . '/jabatan/' . $row->jabatan_id) . '" method="POST">' . csrf_field() . '<button class="edit btn btn-success btn-sm" title="Pilih" type="submit">Pilih</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/member_jabatan/create', compact('member'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function member_jabatan_store(Member $member, Jabatan $jabatan)
    {
        $cek = DB::table('jabatan_member')->where('member_id', $member->member_id)->where('jabatan_id', $jabatan->jabatan_id)->first();

        if (is_null($cek)) {
            DB::table('jabatan_member')->insert([
                'member_id' => $member->member_id,
                'jabatan_id' => $jabatan->jabatan_id,
            ]);
            return redirect('/master/member/' . $member->member_id . '/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Relasi Jabatan Member sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
        return redirect('/master/member/' . $member->member_id . '/jabatan')->with('msg', '<div class="alert alert-info alert-dismissible fade show" role="alert"><strong>Tidak ada Perubahan!</strong> Data Relasi Jabatan Member sebelumnya sudah ditambahkan ke Payung.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function reset_pw(Member $member)
    {
        $member->user_login()->first()->update([
            'password' => Hash::make('12345678')
        ]);

        return redirect('/master/member/' . $member->member_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Password Pegawai sudah di reset.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function member_jabatan_destroy(Member $member, Jabatan $jabatan)
    {
        DB::table('jabatan_member')->where('member_id', $member->member_id)->where('jabatan_id', $jabatan->jabatan_id)->delete();

        return redirect('/master/member/' . $member->member_id . '/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Relasi Jabatan Member sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
