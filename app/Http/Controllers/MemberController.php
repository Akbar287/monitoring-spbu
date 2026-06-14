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
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Route;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Member::select('member.member_id')->addSelect('member.avatar')->addSelect('ktp.nama')->addSelect('ktp.verified')->addSelect('userlogin.username')->join('ktp', 'ktp.ktp_id', 'member.ktp_id')->join('userlogin', 'userlogin.member_id', 'member.member_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($row) {
                    $actionBtn = '<img class="img img-thumbnail" style="width: 80;height: 80px;" src="' . asset('/storage/profile_img/' . ($row->avatar != null || $row->avatar != '' ? $row->avatar : 'default.png')) . '" />';
                    return $actionBtn;
                })
                ->addColumn('verified', function ($row) {
                    $actionBtn = $row->verified == 1 ? 'Terverifikasi' : 'Tidak Verifikasi';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('member.show', $row->member_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'avatar'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Pegawai',
            'cardTitle' => 'List Pegawai',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('member.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Gambar', 'data' => 'avatar', 'orderable' => false, 'searchable' => false, 'raw' => true],
                    ['label' => 'Nama', 'data' => 'nama'],
                    ['label' => 'Username', 'data' => 'username'],
                    ['label' => 'Verifikasi', 'data' => 'verified'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statusMember = StatusMember::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Pegawai',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Pegawai', 'url' => route('member.index')]],
            'backUrl' => route('member.index'),
            'method' => 'post',
            'action' => route('member.store'),
            'fields' => [
                ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'value' => null, 'options' => [['value' => 'pria', 'label' => 'Pria'], ['value' => 'wanita', 'label' => 'Wanita']], 'col' => 'col-md-6'],
                ['name' => 'bergabung_sejak', 'label' => 'Bergabung Sejak', 'type' => 'date', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'value' => null],
                ['name' => 'no_hp', 'label' => 'Nomor HP', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'no_wa', 'label' => 'Nomor Whatsapp', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'status_member_id', 'label' => 'Status Member', 'type' => 'select', 'value' => null,
                    'options' => $statusMember->map(fn ($s) => ['value' => $s->status_member_id, 'label' => $s->nama_status])->values()->all()],
                ['name' => 'username', 'label' => 'Username', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'verified', 'label' => 'Verifikasi', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Terverifikasi'], ['value' => 'false', 'label' => 'Tidak Terverifikasi']], 'col' => 'col-md-6'],
                ['name' => 'is_aktif', 'label' => 'Status Akun', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktif'], ['value' => 'false', 'label' => 'Tidak Aktif']], 'col' => 'col-md-6'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberRequest $memberRequest)
    {
        $ktp = Ktp::where('nik', request('nik'),)->where('nama', request('nama'))->where('tempat_lahir', request('tempat_lahir'))->where('tanggal_lahir', request('tanggal_lahir'))->where('jenis_kelamin', request('jenis_kelamin'))->where('verified', request('verified') ? 1 : 0)->first();
        if (is_null($ktp)) {
            $ktp = Ktp::create($this->ktpData());
        }

        $member = $ktp->member()->where('status_member_id', request('status_member_id'))->where('email', request('email'))->where('no_hp', request('no_hp'))->where('no_wa', request('no_wa'))->where('avatar', request('avatar') != null ? request('avatar') : 'default.png')->where('bergabung_sejak', request('bergabung_sejak'))->first();
        if (is_null($member)) {
            $member = $ktp->member()->create($this->memberData());
        }

        $userlogin = $member->user_login()->where('username', request('username'))->where('is_aktif', request('is_aktif') != null ? 1 : 0)->first();
        if (is_null($userlogin)) {
            $userlogin = $member->user_login()->create($this->userloginData());
        }

        if (is_null($member)) {
            $member = $userlogin->member()->first();
            return redirect('/master/member/' . $userlogin->member()->first()->member_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Member sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            return redirect('/master/member/' . $member->member_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Member sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $ktp = $member->ktp()->first();
        $login = $member->user_login()->first();

        return Inertia::render('Show/Index', [
            'title' => 'Detail Pegawai',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Pegawai', 'url' => route('member.index')]],
            'backUrl' => route('member.index'),
            'fields' => [
                ['label' => 'NIK', 'value' => optional($ktp)->nik],
                ['label' => 'Nama', 'value' => optional($ktp)->nama],
                ['label' => 'Tempat, Tanggal lahir', 'value' => optional($ktp)->tempat_lahir . ', ' . optional($ktp)->tanggal_lahir],
                ['label' => 'Jenis Kelamin', 'value' => ucfirst(optional($ktp)->jenis_kelamin ?? '')],
                ['label' => 'Email', 'value' => $member->email],
                ['label' => 'Nomor HP', 'value' => $member->no_hp],
                ['label' => 'Nomor Whatsapp', 'value' => $member->no_wa],
                ['label' => 'Bergabung Sejak', 'value' => $member->bergabung_sejak],
                ['label' => 'Status Member', 'value' => optional($member->status_member()->first())->nama_status],
                ['label' => 'Username', 'value' => optional($login)->username],
                ['label' => 'Verifikasi', 'value' => filter_var(optional($ktp)->verified, FILTER_VALIDATE_BOOLEAN) ? 'Terverifikasi' : 'Tidak Terverifikasi'],
                ['label' => 'Aktif', 'value' => filter_var(optional($login)->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
            ],
            'image' => $member->avatar ? asset('storage/profile_img/' . $member->avatar) : null,
            'actions' => [
                ['label' => 'Ubah', 'url' => route('member.edit', $member->member_id), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Jabatan', 'url' => route('member.jabatan.index', $member->member_id), 'color' => 'info', 'icon' => 'sitemap'],
                ['label' => 'Reset Password', 'method' => 'put', 'url' => route('member.reset_pw', $member->member_id), 'color' => 'warning', 'icon' => 'key', 'confirm' => 'Password member ini akan direset. Lanjutkan?'],
            ],
            'deleteUrl' => route('member.destroy', $member->member_id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $statusMember = StatusMember::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Pegawai',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Pegawai', 'url' => route('member.index')]],
            'backUrl' => route('member.show', $member->member_id),
            'method' => 'put',
            'action' => route('member.update', $member->member_id),
            'fields' => [
                ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'value' => optional($member->ktp()->first())->nik, 'col' => 'col-md-6'],
                ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'value' => optional($member->ktp()->first())->nama, 'col' => 'col-md-6'],
                ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'value' => optional($member->ktp()->first())->tempat_lahir, 'col' => 'col-md-6'],
                ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'value' => optional($member->ktp()->first())->tanggal_lahir, 'col' => 'col-md-6'],
                ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'value' => optional($member->ktp()->first())->jenis_kelamin, 'options' => [['value' => 'pria', 'label' => 'Pria'], ['value' => 'wanita', 'label' => 'Wanita']], 'col' => 'col-md-6'],
                ['name' => 'bergabung_sejak', 'label' => 'Bergabung Sejak', 'type' => 'date', 'value' => $member->bergabung_sejak, 'col' => 'col-md-6'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'value' => $member->email],
                ['name' => 'no_hp', 'label' => 'Nomor HP', 'type' => 'text', 'value' => $member->no_hp, 'col' => 'col-md-6'],
                ['name' => 'no_wa', 'label' => 'Nomor Whatsapp', 'type' => 'text', 'value' => $member->no_wa, 'col' => 'col-md-6'],
                ['name' => 'status_member_id', 'label' => 'Status Member', 'type' => 'select', 'value' => optional($member->status_member()->first())->status_member_id,
                    'options' => $statusMember->map(fn ($s) => ['value' => $s->status_member_id, 'label' => $s->nama_status])->values()->all()],
                ['name' => 'username', 'label' => 'Username', 'type' => 'text', 'value' => optional($member->user_login()->first())->username, 'col' => 'col-md-6'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'value' => null, 'col' => 'col-md-6', 'hint' => 'Kosongkan jika password tidak diubah.'],
                ['name' => 'verified', 'label' => 'Verifikasi', 'type' => 'select', 'value' => filter_var(optional($member->ktp()->first())->verified, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Terverifikasi'], ['value' => 'false', 'label' => 'Tidak Terverifikasi']], 'col' => 'col-md-6'],
                ['name' => 'is_aktif', 'label' => 'Status Akun', 'type' => 'select', 'value' => filter_var(optional($member->user_login()->first())->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktif'], ['value' => 'false', 'label' => 'Tidak Aktif']], 'col' => 'col-md-6'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $memberRequest, Member $member)
    {
        $member->update($this->memberData());
        $member->ktp()->update($this->ktpData());
        $member->user_login()->update($this->userloginData($memberRequest->password != null || $memberRequest->password != '' ? Hash::create($memberRequest->password) : $member->user_login()->first()->password));

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
            'verified' => request('verified') ? 1 : 0,
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
            'is_aktif' => request('is_aktif') != null ? (request('is_aktif') ? 1 : 0) : 0,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function member_jabatan_index(Request $request, Member $member)
    {
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Jabatan Pegawai',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Pegawai', 'url' => route('member.index')], ['label' => 'Detail', 'url' => route('member.show', $member)]],
            'createUrl' => route('member.jabatan.create', $member),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id Jabatan', 'data' => 'jabatan_id'],
                    ['label' => 'Nama Divisi', 'data' => 'nama_divisi'],
                    ['label' => 'Nama Level', 'data' => 'nama_level'],
                    ['label' => 'Nama Jabatan', 'data' => 'nama_jabatan'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function member_jabatan_create(Request $request, Member $member)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Jabatan::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_divisi', function ($row) {
                    if (is_null($row->level()->first())) {
                        return 'No Level';
                    } else {
                        if (is_null($row->level()->first()->divisi()->first())) {
                            return 'No Level';
                        } else {
                            return $row->level()->first()->divisi()->first()->nama_divisi ?? 'Tidak ada Data';
                        }
                    }
                })
                ->addColumn('nama_level', function ($row) {
                    return $row->level()->first()->nama_level ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = $row->member()->where('member.member_id', Route::getCurrentRoute()->originalParameters()['member'])->count() > 0 ? 'Sudah'  : '<form action="' . url('/master/member/' . Route::getCurrentRoute()->originalParameters()['member'] . '/jabatan/' . $row->jabatan_id) . '" method="POST">' . csrf_field() . '<button class="edit btn btn-success btn-sm" title="Pilih" type="submit">Pilih</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Tambah Jabatan Pegawai',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Pegawai', 'url' => route('member.index')], ['label' => 'Detail', 'url' => route('member.show', $member)]],
            'headerActions' => [['label' => 'Kembali', 'url' => route('member.jabatan.index', $member), 'color' => 'light', 'icon' => 'arrow-left']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Divisi', 'data' => 'nama_divisi'],
                    ['label' => 'Nama Level', 'data' => 'nama_level'],
                    ['label' => 'Nama Jabatan', 'data' => 'nama_jabatan'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
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
        return redirect('/master/member/' . $member->member_id . '/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Relasi Jabatan Member sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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
