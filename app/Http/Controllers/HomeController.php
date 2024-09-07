<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Losses;
use App\Models\Member;
use App\Models\Notifikasi;
use App\Models\Nozzle;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\StatusPurchase;
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

    public function ai(Request $request)
    {
        $request->validate([
            'type' => ['required']
        ]);

        $data = null;
        if ($request->type == 'how_many_stok_in_dombak') {
            $data = Produk::select('produk.nama_produk', 'stok_inventory.stok')->where('is_aktif', 1)->join('stok_inventory', 'stok_inventory.produk_id', 'produk.produk_id')->get();
        }
        if ($request->type == 'how_many_stok_in_pertamina') {
            $produk = Produk::where('produk.is_aktif', 1)->get();
            $data = [];

            foreach ($produk as $row) {
                $data[] = ['nama_produk' => $row->nama_produk, 'stok' => $row->detail_rencana_pembelian()->selectRaw('SUM("jumlah") as stok')->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')->where('status_purchase.nama_status', 'MS2')->where('detail_rencana_pembelian.ms2', 0)->where('detail_rencana_pembelian.delivery', 0)->first()->stok ?? 0];
            }
        }
        if ($request->type == 'how_many_sales_today') {
            $shift = JamKerja::get();
            $produk = Produk::where('is_aktif', 1)->get();
            $data = [];
            $temp = [];

            foreach ($shift as $s) {
                $t = 0;
                foreach ($produk as $p) {
                    $stand_meter = $s->stand_meter()->selectRaw('SUM("stand_meter_akhir") - SUM("stand_meter_awal") as stand_meter')->join('nozzle', 'stand_meter.nozzle_id', 'nozzle.nozzle_id')->join('produk', 'produk.produk_id', 'nozzle.produk_id')->where('nozzle.produk_id', $p->produk_id)->where('stand_meter.tanggal', Carbon::today()->format('Y-m-d'))->first();

                    $data[] = ['produk' => $p->nama_produk, 'nama_jam_kerja' => $s->nama_jam_kerja, 'penjualan' => number_format($p->harga()->where('is_default', 1)->first()->harga_jual * $stand_meter->stand_meter, 2, ',', '.')];

                    $t += $p->harga()->where('is_default', 1)->first()->harga_jual * $stand_meter->stand_meter;
                }
                $temp[] = ['nama_jam_kerja' => $s->nama_jam_kerja, 'total_penjualan' => number_format($t, 2, ",", '.')];
            }
            $data = ['details' => $data, 'summary' => $temp];
        }
        if ($request->type == 'how_many_profit_or_loss_today') {
            $profit = Losses::selectRaw('SUM("jumlah") as jumlah')->where('simbol', 'lebih')->where('tanggal', Carbon::today()->format('Y-m-d'))->first();
            $loss = Losses::selectRaw('SUM("jumlah") as jumlah')->where('simbol', 'kurang')->where('tanggal', Carbon::today()->format('Y-m-d'))->first();

            $data = ['summary' => number_format($profit->jumlah - $loss->jumlah, 2, ".", ",")];
        }

        return response()->json([
            'message' => is_null($data) ? 'No data for type' : 'data successfully',
            'status' => is_null($data) ? 'failed' : 'success',
            'data' => $data
        ], 200);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $data = [
            'member' => Member::count(),
            'produk' => Produk::where('is_aktif', 1)->count(),
            'pengiriman' => Pengiriman::where('is_done', 0)->count(),
            'dombak' => Dombak::where('is_aktif', 1)->count()
        ];
        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');

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
                    "nama" => "Jadwal & Absensi",
                    "icon" => "calendar",
                    "url" => route('menu.jadwal_absensi'),
                    "color" => "danger"
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
                    "nama" => "Totalisator",
                    "icon" => "tint",
                    "url" => route('totalisator.index'),
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
        if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Keamanan') {
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
        $delivery = Pengiriman::where('is_confirmation_admin', 1)->where('is_done', 0)->get();
        return view('home', compact('data', 'allMenu', 'pembayaran', 'delivery', 'waktuMulai', 'waktuSelesai'));
    }

    public function chart()
    {
        $jamKerja = JamKerja::orderBy('nama_jam_kerja', 'asc')->get();
        $produk = Produk::where('is_aktif', 1)->get();
        $nozzles = Nozzle::orderBy('nama_nozzle', 'asc')->get();

        // Chart Kiri
        $kamusWarna = [
            'Pertamina Dex' => 'rgba(6, 107, 45, 0.5)',
            'Pertamax' => 'rgba(0, 51, 102, 0.5)',
            'Bio Solar' => 'rgba(193, 154, 107, 0.5)',
            'Dexlite' => 'rgba(205, 213, 221, 0.5)',
            'Pertalite' => 'rgba(127, 255, 0, 0.5)',
            'Premium' => 'rgba(255, 255, 0, 0.5)',
        ];
        $label = [];
        $temp = [];
        $bg = [];
        foreach ($produk as $p) {
            $label[] = $p->nama_produk;
            $bg[] = $kamusWarna[$p->nama_produk];
            $temp[] = is_null($p->stok_inventory()) ? 0 : $p->stok_inventory()->first()->stok;
        }

        $dataset[] = ['label' => 'Stok (dalam liter)', 'data' => $temp, 'borderWidth' => 1, "backgroundColor" => $bg];

        $chart_stok = [
            "labels" => $label,
            "datasets" => $dataset,
        ];
        unset($dataset);
        unset($temp);
        unset($bg);
        unset($kamusWarna);

        // End Chart Kiri

        // Chart Kanan
        $temp_out_shift = [];
        foreach ($jamKerja as $jk) {
            $temp_diver_penjualan = [];

            foreach ($produk as $p) {
                $count = 0;
                foreach ($p->nozzle()->get() as $nozzle) {
                    $count += round(($nozzle->stand_meter()
                        ->selectRaw('SUM(stand_meter.stand_meter_akhir - stand_meter.stand_meter_awal) as selisih')
                        ->whereDate('tanggal', today())
                        // ->whereDate('tanggal', '2024-01-20')
                        ->where('jam_kerja_id', $jk->jam_kerja_id)
                        ->first()->selisih ?? 0));
                }
                $temp_diver_penjualan[] = $count;
            }

            $temp_out_shift[] = [
                "label" => $jk->nama_jam_kerja,
                "data" => $temp_diver_penjualan,
                "stack" => $jk->nama_jam_kerja == 'Shift 1' ? 'Stack 1' : ($jk->nama_jam_kerja == 'Shift 2' ? 'Stack 2' : 'Stack 3'),
                "borderWidth" => 1,
                "backgroundColor" => $jk->nama_jam_kerja == 'Shift 1' ? 'rgba(255, 0, 0, 0.5)' : ($jk->nama_jam_kerja == 'Shift 2' ? 'rgba(255, 255, 0, 0.5)' : 'rgba(0, 0, 255, 0.5)')
            ];
        }

        $chart_stok_penjualan = [
            "labels" => $label,
            "datasets" => $temp_out_shift,
        ];

        $chart_stok_split = [
            "labels" => $label,
            "datasets" => $temp_out_shift,
        ];

        unset($label);
        unset($temp_out_shift);
        unset($temp_diver_stok);
        unset($temp_loss);

        // End Chart Kanan


        // Chart Kiri Penjualan
        $res = [];
        $labelPenjualan = [];
        foreach ($nozzles as $nozzle) {
            $labelPenjualan[] = $nozzle->nama_nozzle;
            $res[] = round(($nozzle->stand_meter()
                ->selectRaw('SUM(stand_meter.stand_meter_akhir - stand_meter.stand_meter_awal) as selisih')
                ->whereDate('tanggal', today())
                // ->whereDate('tanggal', '2024-01-20')
                ->first()->selisih ?? 0) * $nozzle->produk()->first()->harga()->where('is_default', 1)->first()->harga_jual);
        }
        $datasetPenjualan[] = ['label' => 'Penjualan (dalam Rupiah)', 'data' => $res, 'borderWidth' => 1];

        $chart_penjualan = [
            "labels" => $labelPenjualan,
            "datasets" => $datasetPenjualan,
        ];

        unset($res);
        unset($datasetPenjualan);
        // End Chart Kiri Penjualan


        // Chart Kanan Penjualan
        $temp_out_shift = [];
        foreach ($jamKerja as $jk) {
            $temp_diver_penjualan = [];
            foreach ($nozzles as $nozzle) {
                $temp_diver_penjualan[] = round(($nozzle->stand_meter()
                    ->selectRaw('SUM(stand_meter.stand_meter_akhir - stand_meter.stand_meter_awal) as selisih')
                    ->whereDate('tanggal', today())
                    // ->whereDate('tanggal', '2024-01-20')
                    ->where('jam_kerja_id', $jk->jam_kerja_id)
                    ->first()->selisih ?? 0) * $nozzle->produk()->first()->harga()->where('is_default', 1)->first()->harga_jual);
            }
            $temp_out_shift[] = [
                "label" => $jk->nama_jam_kerja,
                "data" => $temp_diver_penjualan,
                "stack" => $jk->nama_jam_kerja == 'Shift 1' ? 'Stack 1' : ($jk->nama_jam_kerja == 'Shift 2' ? 'Stack 2' : 'Stack 3'),
                "borderWidth" => 1,
                "backgroundColor" => $jk->nama_jam_kerja == 'Shift 1' ? 'rgba(255, 0, 0, 0.5)' : ($jk->nama_jam_kerja == 'Shift 2' ? 'rgba(255, 255, 0, 0.5)' : 'rgba(0, 0, 255, 0.5)')
            ];
        }

        $chart_stok_penjualan = [
            "labels" => $labelPenjualan,
            "datasets" => $temp_out_shift,
        ];
        // End Chart Kanan Penjualan


        return response()->json([
            "message" => 'success',
            "data" => [
                'chart_stok' => $chart_stok,
                'chart_stok_split' => $chart_stok_split,
                'chart_penjualan' => $chart_penjualan,
                'chart_penjualan_split' => $chart_stok_penjualan,
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
