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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
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


        $level = Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level;
        $isManagement = in_array($level, ['Penanggung Jawab', 'Komisaris', 'Direktur Utama', 'Direktur']);

        $pembayaranRows = [];
        $deliveryRows = [];
        if ($isManagement) {
            $pembayaran = StatusPurchase::where('nama_status', 'Pembayaran')->first()->rencana_pembelian()->get();
            $pembayaranRows = $pembayaran->map(function ($pe) {
                $produk = $pe->detail_rencana_pembelian()
                    ->select('produk.nama_produk')
                    ->join('produk', 'produk.produk_id', 'detail_rencana_pembelian.produk_id')
                    ->pluck('nama_produk')->implode(', ');

                return [
                    'tanggal' => $pe->tanggal_pembelian,
                    'produk' => $produk,
                    'total' => number_format($pe->grand_total, 2, '.', ','),
                    'url' => route('pembayaran.show', $pe->rencana_pembelian_id),
                ];
            })->values()->all();

            $delivery = Pengiriman::where('is_confirmation_admin', 1)->where('is_done', 0)->get();
            foreach ($delivery as $d) {
                foreach ($d->file_lo as $lo) {
                    $deliveryRows[] = [
                        'produk' => optional($lo->produk()->first())->nama_produk,
                        'jumlah' => number_format($lo->jumlah, 0, '.', ',') . ' ' . $lo->satuan_jumlah,
                        'tanggal' => $lo->tanggal_upload,
                        'url' => route('pengiriman.file_lo.show', [$lo->pengiriman()->first()->pengiriman_id, $lo->file_lo_id]),
                    ];
                }
            }
        }

        return Inertia::render('Home/Index', [
            'isManagement' => $isManagement,
            'nama' => optional(Auth::user()->member()->first()->ktp()->first())->nama,
            'level' => $level,
            'stats' => $data,
            'menus' => $allMenu,
            'pembayaran' => $pembayaranRows,
            'delivery' => $deliveryRows,
            'links' => [
                'chartApi' => url('/api/chart'),
                'pembayaranIndex' => route('pembayaran.index'),
                'pengirimanIndex' => route('pengiriman.index'),
            ],
        ]);
    }

    public function chart()
    {
        $today = Carbon::today();
        $jamKerja = JamKerja::orderBy('nama_jam_kerja', 'asc')->get();
        $produk = Produk::with([
            'stok_inventory',
            'harga' => function ($query) {
                $query->where('is_default', 1);
            },
        ])->where('is_aktif', 1)->orderBy('nama_produk', 'asc')->get();
        $nozzles = Nozzle::with([
            'produk.harga' => function ($query) {
                $query->where('is_default', 1);
            },
        ])->where('is_aktif', 1)
            ->whereHas('produk', function ($query) {
                $query->where('is_aktif', 1);
            })
            ->orderBy('nama_nozzle', 'asc')
            ->get();

        $productIds = $produk->pluck('produk_id')->values()->all();
        $productLabels = $produk->pluck('nama_produk')->values()->all();
        $nozzleIds = $nozzles->pluck('nozzle_id')->values()->all();
        $nozzleLabels = $nozzles->pluck('nama_nozzle')->values()->all();
        $priceByProduct = $produk->mapWithKeys(function ($row) {
            return [$row->produk_id => (float) optional($row->harga->first())->harga_jual];
        })->all();

        $salesByProduct = array_fill_keys($productIds, 0);
        $revenueByNozzle = array_fill_keys($nozzleIds, 0);
        $salesByProductShift = [];
        $revenueByNozzleShift = [];

        foreach ($jamKerja as $shift) {
            $salesByProductShift[$shift->jam_kerja_id] = array_fill_keys($productIds, 0);
            $revenueByNozzleShift[$shift->jam_kerja_id] = array_fill_keys($nozzleIds, 0);
        }

        $salesRows = DB::table('stand_meter')
            ->select('nozzle.produk_id', 'stand_meter.nozzle_id', 'stand_meter.jam_kerja_id')
            ->selectRaw('SUM(COALESCE(stand_meter.stand_meter_akhir, stand_meter.stand_meter_awal) - stand_meter.stand_meter_awal) as liter')
            ->join('nozzle', 'stand_meter.nozzle_id', '=', 'nozzle.nozzle_id')
            ->join('produk', 'produk.produk_id', '=', 'nozzle.produk_id')
            ->whereDate('stand_meter.tanggal', $today->format('Y-m-d'))
            ->where('nozzle.is_aktif', 1)
            ->where('produk.is_aktif', 1)
            ->groupBy('nozzle.produk_id', 'stand_meter.nozzle_id', 'stand_meter.jam_kerja_id')
            ->get();

        foreach ($salesRows as $row) {
            $liter = round((float) $row->liter, 2);
            $revenue = round($liter * ($priceByProduct[$row->produk_id] ?? 0));

            if (array_key_exists($row->produk_id, $salesByProduct)) {
                $salesByProduct[$row->produk_id] += $liter;
            }

            if (array_key_exists($row->nozzle_id, $revenueByNozzle)) {
                $revenueByNozzle[$row->nozzle_id] += $revenue;
            }

            if (isset($salesByProductShift[$row->jam_kerja_id]) && array_key_exists($row->produk_id, $salesByProductShift[$row->jam_kerja_id])) {
                $salesByProductShift[$row->jam_kerja_id][$row->produk_id] += $liter;
            }

            if (isset($revenueByNozzleShift[$row->jam_kerja_id]) && array_key_exists($row->nozzle_id, $revenueByNozzleShift[$row->jam_kerja_id])) {
                $revenueByNozzleShift[$row->jam_kerja_id][$row->nozzle_id] += $revenue;
            }
        }

        $stockValues = $produk->map(function ($row) {
            return round((float) optional($row->stok_inventory)->stok, 2);
        })->values()->all();
        $productSalesValues = array_map(function ($productId) use ($salesByProduct) {
            return round((float) ($salesByProduct[$productId] ?? 0), 2);
        }, $productIds);
        $nozzleRevenueValues = array_map(function ($nozzleId) use ($revenueByNozzle) {
            return round((float) ($revenueByNozzle[$nozzleId] ?? 0));
        }, $nozzleIds);

        $stockDatasets = [[
            'label' => 'Stok (liter)',
            'data' => $stockValues,
            'borderWidth' => 1,
            'backgroundColor' => array_map(function ($label, $index) {
                return $this->productColor($label, $index);
            }, $productLabels, array_keys($productLabels)),
        ]];

        $literShiftDatasets = [];
        $revenueShiftDatasets = [];
        foreach ($jamKerja->values() as $index => $shift) {
            $literShiftDatasets[] = [
                'label' => $shift->nama_jam_kerja,
                'data' => array_map(function ($productId) use ($salesByProductShift, $shift) {
                    return round((float) ($salesByProductShift[$shift->jam_kerja_id][$productId] ?? 0), 2);
                }, $productIds),
                'stack' => 'total',
                'borderWidth' => 1,
                'backgroundColor' => $this->shiftColor($index),
            ];

            $revenueShiftDatasets[] = [
                'label' => $shift->nama_jam_kerja,
                'data' => array_map(function ($nozzleId) use ($revenueByNozzleShift, $shift) {
                    return round((float) ($revenueByNozzleShift[$shift->jam_kerja_id][$nozzleId] ?? 0));
                }, $nozzleIds),
                'stack' => 'total',
                'borderWidth' => 1,
                'backgroundColor' => $this->shiftColor($index),
            ];
        }

        $shiftLabels = $jamKerja->pluck('nama_jam_kerja')->values()->all();
        $shiftRevenueTotals = array_map(function ($dataset) {
            return array_sum($dataset['data']);
        }, $revenueShiftDatasets);

        $chartStok = [
            'labels' => $productLabels,
            'datasets' => $stockDatasets,
            'meta' => $this->chartMeta($productLabels, $stockValues, 'liter', 'Total Stok', 'Stok Terbesar', 'Stok Terkecil'),
        ];

        $chartStokSplit = [
            'labels' => $productLabels,
            'datasets' => $literShiftDatasets,
            'meta' => $this->stackedChartMeta($productLabels, $literShiftDatasets, 'liter', 'Total Liter', 'Produk Terlaris', 'Shift Terbesar'),
        ];

        $chartPenjualan = [
            'labels' => $nozzleLabels,
            'datasets' => [[
                'label' => 'Penjualan (Rupiah)',
                'data' => $nozzleRevenueValues,
                'borderWidth' => 1,
            ]],
            'meta' => $this->chartMeta($nozzleLabels, $nozzleRevenueValues, 'money', 'Total Omzet', 'Nozzle Terbesar', 'Nozzle Terkecil'),
        ];

        $chartPenjualanSplit = [
            'labels' => $nozzleLabels,
            'datasets' => $revenueShiftDatasets,
            'meta' => $this->stackedChartMeta($nozzleLabels, $revenueShiftDatasets, 'money', 'Total Omzet', 'Nozzle Terbesar', 'Shift Terbesar'),
        ];

        return response()->json([
            'message' => 'success',
            'data' => [
                'summary' => [
                    'tanggal' => $today->format('Y-m-d'),
                    'generated_at' => Carbon::now()->toIso8601String(),
                    'cards' => [
                        [
                            'label' => 'Total Stok',
                            'value' => array_sum($stockValues),
                            'type' => 'liter',
                            'icon' => 'gas-pump',
                            'tone' => 'primary',
                        ],
                        [
                            'label' => 'Volume Terjual',
                            'value' => array_sum($productSalesValues),
                            'type' => 'liter',
                            'name' => $this->peak($productLabels, $productSalesValues)['label'],
                            'icon' => 'tint',
                            'tone' => 'success',
                        ],
                        [
                            'label' => 'Omzet Hari Ini',
                            'value' => array_sum($nozzleRevenueValues),
                            'type' => 'money',
                            'name' => $this->peak($nozzleLabels, $nozzleRevenueValues)['label'],
                            'icon' => 'coins',
                            'tone' => 'warning',
                        ],
                        [
                            'label' => 'Shift Terbesar',
                            'value' => $this->peak($shiftLabels, $shiftRevenueTotals)['value'],
                            'type' => 'money',
                            'name' => $this->peak($shiftLabels, $shiftRevenueTotals)['label'],
                            'icon' => 'clock',
                            'tone' => 'danger',
                        ],
                    ],
                ],
                'chart_stok' => $chartStok,
                'chart_stok_split' => $chartStokSplit,
                'chart_penjualan' => $chartPenjualan,
                'chart_penjualan_split' => $chartPenjualanSplit,
            ],
            'status' => true,
        ], 200);
    }

    private function chartMeta(array $labels, array $values, string $type, string $totalLabel, string $topLabel, string $lowLabel): array
    {
        $peak = $this->peak($labels, $values);
        $trough = $this->trough($labels, $values);

        return [
            'metrics' => [
                [
                    'label' => $totalLabel,
                    'value' => array_sum($values),
                    'type' => $type,
                ],
                [
                    'label' => $topLabel,
                    'name' => $peak['label'],
                    'value' => $peak['value'],
                    'type' => $type,
                ],
                [
                    'label' => $lowLabel,
                    'name' => $trough['label'],
                    'value' => $trough['value'],
                    'type' => $type,
                ],
            ],
        ];
    }

    private function stackedChartMeta(array $labels, array $datasets, string $type, string $totalLabel, string $topLabel, string $shiftLabel): array
    {
        $totalsByLabel = array_fill(0, count($labels), 0);
        $shiftLabels = [];
        $shiftTotals = [];

        foreach ($datasets as $dataset) {
            $shiftLabels[] = $dataset['label'];
            $shiftTotal = 0;
            foreach ($dataset['data'] as $index => $value) {
                $numericValue = (float) $value;
                $totalsByLabel[$index] = ($totalsByLabel[$index] ?? 0) + $numericValue;
                $shiftTotal += $numericValue;
            }
            $shiftTotals[] = $shiftTotal;
        }

        $peak = $this->peak($labels, $totalsByLabel);
        $shiftPeak = $this->peak($shiftLabels, $shiftTotals);

        return [
            'metrics' => [
                [
                    'label' => $totalLabel,
                    'value' => array_sum($totalsByLabel),
                    'type' => $type,
                ],
                [
                    'label' => $topLabel,
                    'name' => $peak['label'],
                    'value' => $peak['value'],
                    'type' => $type,
                ],
                [
                    'label' => $shiftLabel,
                    'name' => $shiftPeak['label'],
                    'value' => $shiftPeak['value'],
                    'type' => $type,
                ],
            ],
        ];
    }

    private function peak(array $labels, array $values): array
    {
        if (count($labels) === 0 || count($values) === 0) {
            return ['label' => '-', 'value' => 0];
        }

        $peakIndex = 0;
        foreach ($values as $index => $value) {
            if ((float) $value > (float) ($values[$peakIndex] ?? 0)) {
                $peakIndex = $index;
            }
        }

        if ((float) ($values[$peakIndex] ?? 0) <= 0) {
            return ['label' => '-', 'value' => 0];
        }

        return [
            'label' => $labels[$peakIndex] ?? '-',
            'value' => (float) ($values[$peakIndex] ?? 0),
        ];
    }

    private function trough(array $labels, array $values): array
    {
        if (count($labels) === 0 || count($values) === 0) {
            return ['label' => '-', 'value' => 0];
        }

        $hasValue = false;
        foreach ($values as $value) {
            if ((float) $value > 0) {
                $hasValue = true;
                break;
            }
        }

        if (!$hasValue) {
            return ['label' => '-', 'value' => 0];
        }

        $troughIndex = 0;
        foreach ($values as $index => $value) {
            if ((float) $value < (float) ($values[$troughIndex] ?? 0)) {
                $troughIndex = $index;
            }
        }

        return [
            'label' => $labels[$troughIndex] ?? '-',
            'value' => (float) ($values[$troughIndex] ?? 0),
        ];
    }

    private function productColor(string $name, int $index): string
    {
        $colors = [
            'Pertamina Dex' => 'rgba(6, 107, 45, 0.72)',
            'Pertamax' => 'rgba(0, 51, 102, 0.72)',
            'Bio Solar' => 'rgba(193, 154, 107, 0.72)',
            'Dexlite' => 'rgba(130, 148, 166, 0.72)',
            'Pertalite' => 'rgba(71, 195, 99, 0.72)',
            'Premium' => 'rgba(255, 164, 38, 0.72)',
        ];
        $fallback = [
            'rgba(103, 119, 239, 0.72)',
            'rgba(252, 84, 75, 0.72)',
            'rgba(255, 164, 38, 0.72)',
            'rgba(71, 195, 99, 0.72)',
            'rgba(58, 186, 244, 0.72)',
            'rgba(155, 89, 182, 0.72)',
        ];

        return $colors[$name] ?? $fallback[$index % count($fallback)];
    }

    private function shiftColor(int $index): string
    {
        $colors = [
            'rgba(103, 119, 239, 0.78)',
            'rgba(252, 84, 75, 0.78)',
            'rgba(255, 164, 38, 0.78)',
            'rgba(71, 195, 99, 0.78)',
            'rgba(58, 186, 244, 0.78)',
        ];

        return $colors[$index % count($colors)];
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

        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Notifikasi',
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'created_at'],
                    ['label' => 'Judul', 'data' => 'judul'],
                    ['label' => 'Status', 'data' => 'status'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }
}
