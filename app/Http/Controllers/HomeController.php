<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Member;
use App\Models\Notifikasi;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\StatusPurchase;
use App\Models\TypeDokumenStok;
use App\Models\UserLogin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [
            'member' => Member::count(),
            'produk' => Produk::where('is_aktif', true)->count(),
            'pengiriman' => Pengiriman::where('is_done', false)->count(),
            'dombak' => Dombak::where('is_aktif', true)->count()
        ];

        if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab') {
            $allMenu = [
                [
                    "nama" => "Master Data",
                    "icon" => "th",
                    "url" => route('menu.master'),
                    "color" => "primary"
                ],
                [
                    "nama" => "Procurement",
                    "icon" => "filter",
                    "url" => route('menu.procurement'),
                    "color" => "secondary"
                ],
                [
                    "nama" => "Manajemen Persediaan",
                    "icon" => "tint",
                    "url" => route('menu.stok'),
                    "color" => "success"
                ],
                [
                    "nama" => "Tera",
                    "icon" => "upload",
                    "url" => route('menu.tera'),
                    "color" => "danger"
                ],
                [
                    "nama" => "Setoran",
                    "icon" => "percent",
                    "url" => route('menu.setoran'),
                    "color" => "warning"
                ],
                [
                    "nama" => "Financial",
                    "icon" => "file",
                    "url" => route('menu.financial'),
                    "color" => "info"
                ],
                [
                    "nama" => "Konfigurasi",
                    "icon" => "wrench",
                    "url" => route('menu.konfigurasi'),
                    "color" => "primary"
                ],
            ];
        }

        if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator') {
            $allMenu = [
                [
                    "nama" => "Procurement",
                    "icon" => "filter",
                    "url" => route('menu.procurement'),
                    "color" => "primary"
                ],
                [
                    "nama" => "Manajemen Persediaan",
                    "icon" => "flask",
                    "url" => route('menu.stok'),
                    "color" => "info"
                ],
                [
                    "nama" => "Stand Meter",
                    "icon" => "tint",
                    "url" => route('menu.stand-meter'),
                    "color" => "success"
                ],
                [
                    "nama" => "Setoran",
                    "icon" => "percent",
                    "url" => route('menu.setoran'),
                    "color" => "danger"
                ],
                [
                    "nama" => "Kehadiran",
                    "icon" => "calendar",
                    "url" => route('menu.kehadiran'),
                    "color" => "warning"
                ]
            ];
        }

        if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur') {
            $allMenu = [
                [
                    "nama" => "Procurement",
                    "icon" => "filter",
                    "url" => route('menu.procurement'),
                    "color" => "primary"
                ],
                [
                    "nama" => "Stok Taking",
                    "icon" => "flask",
                    "url" => route('menu.stok'),
                    "color" => "info"
                ],
                [
                    "nama" => "Stand Meter",
                    "icon" => "tint",
                    "url" => route('menu.stand-meter'),
                    "color" => "success"
                ],
                [
                    "nama" => "Setoran",
                    "icon" => "percent",
                    "url" => route('menu.setoran'),
                    "color" => "danger"
                ],
                [
                    "nama" => "Kehadiran",
                    "icon" => "calendar",
                    "url" => route('menu.kehadiran'),
                    "color" => "warning"
                ]
            ];
        }


        $pembayaran = StatusPurchase::where('nama_status', 'Pembayaran')->first()->rencana_pembelian()->get();
        $delivery = Pengiriman::where('is_confirmation_admin', true)->where('is_done', false)->get();
        return view('home', compact('data', 'allMenu', 'pembayaran', 'delivery'));
    }

    public function chart()
    {
        // Chart Kiri
        $produk = Produk::where('is_aktif', true)->get();

        $label = [];
        $temp = [];
        $temp_out_shift = [];
        foreach ($produk as $p) {
            $label[] = $p->nama_produk;
            $temp[] = is_null($p->stok_inventory()) ? 0 : $p->stok_inventory()->first()->stok;
        }
        // End Chart Kiri

        // Chart Kanan
        $jamKerja = JamKerja::all();

        foreach ($jamKerja as $jk) {
            $temp_diver_stok = [];
            $temp_loss = [];

            foreach ($produk as $p) {
                $loss = $p->stok_inventory()->first()->dokumen_stok()->where('jam_kerja_id', $jk->jam_kerja_id)->where('tanggal', date('Y-m-d'))->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Loss')->first()->type_dokumen_stok_id)->get();

                $lossTempCalculation = 0;
                foreach ($loss as $s) {
                    if ($s->losses()->first()->simbol == 'lebih') {
                        $lossTempCalculation = $lossTempCalculation + $s->losses()->first()->jumlah;
                    } else {
                        $lossTempCalculation = $lossTempCalculation - $s->losses()->first()->jumlah;
                    }
                }

                // $goodIssueSales = $p->stok_inventory()->first()->dokumen_stok()->where('jam_kerja_id', $jk->jam_kerja_id)->where('tanggal', date('Y-m-d'))->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Issue Sales')->first()->type_dokumen_stok_id)->get();

                // $goodIssueSalesTempCalculation = 0;
                // foreach ($goodIssueSales as $s) {
                //     if ($s->stok_awal - $s->stok_akhir > 0) {
                //         $goodIssueSalesTempCalculation = $s->stok_awal - $s->stok_akhir;
                //     } else {
                //         $goodIssueSalesTempCalculation = $s->stok_akhir - $s->stok_awal;
                //     }
                // }

                $goodIssueSales = $p->penjualan()
                    ->where('penjualan.jam_kerja_id', $jk->jam_kerja_id)
                    ->where('tanggal', date('Y-m-d'))
                    ->first()->total_penjualan ?? 0;

                $temp_diver_stok[] = $goodIssueSales;
                $temp_loss[] = $lossTempCalculation;
            }

            $temp_out_shift[] = [
                "label" => $jk->nama_jam_kerja,
                "data" => $temp_diver_stok,
                "borderWidth" => 1
            ];

            // End Chart Kanan
        }

        $dataset[] = ['label' => 'Stok (dalam liter)', 'data' => $temp, 'borderWidth' => 1];
        $chart_stok = [
            "labels" => $label,
            "datasets" => $dataset,
        ];

        $chart_stok_split = [
            "labels" => $label,
            "datasets" => $temp_out_shift,
        ];

        return response()->json([
            "message" => 'success',
            "data" => [
                'chart_stok' => $chart_stok,
                'chart_stok_split' => $chart_stok_split
            ],
            "status" => true
        ], 200);
    }

    public function profil()
    {
        return view('auth/profil');
    }

    public function profil_edit()
    {
        return view('auth/profil_edit');
    }

    public function profil_store(Request $request)
    {
        if (is_null($request->username)) {
            $request->validate([
                'username' => ['required'],
            ]);
        }

        if (Auth::user()->username == $request->username) {
            $request->validate([
                'nik' => ['required'],
                'nama' => ['required'],
                'tempat_lahir' => ['required'],
                'tanggal_lahir' => ['required'],
                'jenis_kelamin' => ['required'],
                'email' => ['required'],
                'no_hp' => ['required'],
                'no_wa' => ['required'],
            ]);
        } else {
            $request->validate([
                'nik' => ['required', 'size:16'],
                'nama' => ['required'],
                'tempat_lahir' => ['required'],
                'tanggal_lahir' => ['required'],
                'jenis_kelamin' => ['required'],
                'email' => ['required'],
                'no_hp' => ['required'],
                'no_wa' => ['required'],
                'username' => ['required', 'unique:userlogin,username'],
            ]);
        }

        Auth::user()->member()->first()->ktp()->first()->update([
            'nik' => $request->nik,
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        Auth::user()->member()->first()->update([
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'no_wa' => $request->no_wa,
        ]);

        UserLogin::where('user_login_id', Auth::user()->user_login_id)->first()->update([
            'username' => $request->username
        ]);

        return redirect('/profil')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Profil sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function profil_edit_password()
    {
        return view('auth/profil_password');
    }

    public function profil_store_password(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'min:6|required_with:konfirmasi_password_baru|same:konfirmasi_password_baru',
            'konfirmasi_password_baru' => 'required|min:6',
        ]);

        if (Hash::check($request->password_lama, Auth::user()->password)) {
            if ($request->password_baru == $request->konfirmasi_password_baru) {
                UserLogin::where('user_login_id', Auth::user()->user_login_id)->first()->update([
                    'password' => $request->password_baru
                ]);

                return redirect('/profil')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Profil sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            } else {
                return redirect('/profil/password')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Konfirmasi Password Salah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            }
        } else {
            return redirect('/profil/password')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Password Lama Salah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    public function notifikasi(Request $request)
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

        if ($request->ajax()) {
            $data = Notifikasi::orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('status', function ($row) {
                    $actionBtn = $row->is_read ? 'Dibaca' : 'Belum Dibaca';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.show', $row->notifikasi_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('auth/notifikasi', compact('waktuMulai', 'waktuSelesai'));
    }
}
