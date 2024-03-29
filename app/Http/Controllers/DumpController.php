<?php

namespace App\Http\Controllers;

use App\Models\PajakPembelianLib;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\Spbu;
use App\Models\StatusPurchase;
use App\Models\Supir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DumpController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function generatePembelian()
    {
        $loop = true;
        $iter = 1;
        $pembelianId = 'A-' . date('Ymd') . '-' . $iter;
        do {
            $pembelianId = 'A-' . date('Ymd') . '-' . $iter;
            $res =  RencanaPembelian::where('kode_pembelian',  $pembelianId)->count();

            if ($res == 0) {
                $loop = false;
            } else {
                $iter += 1;
            }
        } while ($loop);

        return $pembelianId;
    }

    public function uploadDataPembelian()
    {
        $statusPurchase = StatusPurchase::where('nama_status', 'MS2')->first();
        $spbu = Spbu::where('nama_spbu', 'SPBU 2434520')->first();
        $id = $this->generatePembelian();

        $rekening = [
            'no_rekening' => '1234567890',
            'nama_rekening' => 'PT. MGSJ',
        ];

        $data = [
            [
                'tgl_pembelian' => '2023-12-31',
                'tgl_pengiriman' => '2023-12-31',
                'kode_pembelian' => '8103158840', // No LO Juga
                'no_cek_bg' => 'online', // Strip jika non-online
                'pembayaran' => [
                    'net_price' => 66405517,
                    'ppn' => 7586207,
                    'ppbkb' => 3448276,
                    'pph' => 172414,
                    'total' => 77612414
                ],
                'produk' => [
                    [
                        'produk_id' => 'Pertalite',
                        'rit' => 1,
                        'kuantitas' => 8000,
                        'file_lo' => [
                            'nama_dokumen' => '',
                            'nama_file' => '',
                        ]
                    ]
                ],
                'pengiriman' => [
                    'no_do' => 'DO-00000',
                    'no_pol' => 'BE 8927 AUC',
                    'supir' => [
                        'Didik',
                        'eko'
                    ],

                ]
            ],
            [
                'tgl_pembelian' => '2024-01-01',
                'tgl_pengiriman' => '2024-01-01',
                'kode_pembelian' => '8103192385', // No LO Juga
                'no_cek_bg' => 'online', // Strip jika non-online
                'pembayaran' => [
                    'net_price' => 45216552,
                    'ppn' => 5158620,
                    'ppbkb' => 2344828,
                    'pph' => 117241,
                    'total' => 52837241
                ],
                'produk' => [
                    [
                        'produk_id' => 'Bio Solar',
                        'rit' => 1,
                        'kuantitas' => 8000,
                        'file_lo' => [
                            'nama_dokumen' => '',
                            'nama_file' => '',
                        ]
                    ]
                ],
                'pengiriman' => [
                    'no_do' => 'DO-00000',
                    'no_pol' => 'BE 9211 AU',
                    'supir' => [
                        'PONIMIN',
                        'PUJI'
                    ],

                ]
            ],
            2 => [
                'tgl_pembelian' => '2024-01-01',
                'tgl_pengiriman' => '2024-01-01',
                'kode_pembelian' => '1803192383', // No LO Juga
                'no_cek_bg' => 'online', // Strip jika non-online
                'pembayaran' => [
                    'net_price' => 66405517,
                    'ppn' => 7586207,
                    'ppbkb' => 3448276,
                    'pph' => 172414,
                    'total' => 77612414
                ],
                'produk' => [
                    [
                        'produk_id' => 'Pertalite',
                        'rit' => 1,
                        'kuantitas' => 8000,
                        'file_lo' => [
                            'nama_dokumen' => '',
                            'nama_file' => '',
                        ]
                    ]
                ],
                'pengiriman' => [
                    'no_do' => 'DO-00000',
                    'no_pol' => 'BE 9211 AU',
                    'supir' => [
                        'PONIMIN',
                        'PUJI'
                    ],

                ]
            ],
            3 => [
                'tgl_pembelian' => '2024-01-02',
                'tgl_pengiriman' => '2024-01-02',
                'kode_pembelian' => '1803192383', // No LO Juga
                'no_cek_bg' => 'online', // Strip jika non-online
                'pembayaran' => [
                    'net_price' => 111622069,
                    'ppn' => 12744827,
                    'ppbkb' => 5793104,
                    'pph' => 289655,
                    'total' => 130449655
                ],
                'produk' => [
                    [
                        'produk_id' => 'Pertalite',
                        'rit' => 1,
                        'kuantitas' => 8000,
                        'file_lo' => [
                            'nama_dokumen' => '',
                            'nama_file' => '',
                        ]
                    ]
                ],
                'pengiriman' => [
                    'no_do' => 'DO-00000',
                    'no_pol' => 'BE 9211 AU',
                    'supir' => [
                        'PONIMIN',
                        'PUJI'
                    ],

                ]
            ],
        ];

        foreach ($data as $d) {
            $rencanaPembelian = $statusPurchase->rencana_pembelian()->create([
                'status_purchase_id' => $statusPurchase->status_purchase_id,
                'spbu_id' => $spbu->spbu_id,
                'member_id' => Auth::user()->member_id,
                'tanggal_pembelian' => $d['tgl_pembelian'] ?? $d['tgl_pengiriman'],
                'pembelian_id' => $d['kode_pembelian'],
                'kode_pembelian' => $d['kode_pembelian'],
                'grand_total' => $d['pembayaran']['net_price'],
                'keterangan' => '',
                'is_confirmation' => true,
                'is_denied' => false,
                'keterangan_denied' => false
            ]);

            // Pembayaran
            $pembayaran = $rencanaPembelian->pembayaran()->create([
                'rencana_pembelian_id' => $rencanaPembelian->rencana_pembelian_id,
                'member_id' => Auth::user()->member_id,
                'no_cek_bg' => $d['no_cek_bg'],
                'no_rekening' => $rekening['no_rekening'],
                'nama_rekening' => $rekening['nama_rekening'],
                'total_setoran' => $d['pembayaran']['total'],
                'is_confirmation' => true,
                'is_confirmation_top' => true,
                'tanggal' => $d['tgl_pembelian'] ?? $d['tgl_pengiriman'],
            ]);

            // Pajak
            $lib = PajakPembelianLib::where('is_aktif', true)->first();
            $pajakPembelian = $rencanaPembelian->pajak_pembelian()->create([
                'pajak_pembelian_lib_id' => $lib->pajak_pembelian_lib_id,
                'net' => $d['pembayaran']['net_price'],
                'ppn' => $d['pembayaran']['ppn'],
                'ppbkb' => $d['pembayaran']['ppbkb'],
                'pph' => $d['pembayaran']['pph'],
                'total_amount' => $d['pembayaran']['total'],
            ]);

            // Pengiriman
            $pengiriman = Pengiriman::create([
                'member_id' => Auth::user()->member_id,
                'tanggal' =>  $d['tgl_pengiriman'] ?? $d['tgl_pembelian'],
                'no_do' => $d['pengiriman']['no_do'],
                'no_pol' => $d['pengiriman']['no_pol'],
                'is_confirmation' => true,
                'is_done' => true,
                'ms2' => true,
            ]);

            foreach ($d['pengiriman']['supir'] as $supir) {
                Supir::create([
                    'pengiriman_id' => $pengiriman->pengiriman_id,
                    'nama_supir' => $supir
                ]);
            }

            // Detail produk
            foreach ($d['produk'] as $produk) {
                $db = Produk::where('nama_produk', $produk['produk_id'])->first();
                $detailRencanaPembelian = $rencanaPembelian->detail_rencana_pembelian()->create([
                    'produk_id' => $produk['produk_id'],
                    'jumlah' => $produk['kuantitas'],
                    'satuan_jumnlah' => 'liter',
                    'rit' => $produk['rit'],
                    'harga' => $db->harga()->where('is_default', true)->first()->harga_beli,
                    'total_harga' => $db->harga()->where('is_default', true)->first()->harga_beli * $produk['kuantitas'],
                    'is_confirmation' => true,
                    'ms2' => true,
                    'delivery' => true
                ]);

                $fileLo = $pengiriman->file_lo()->create([
                    'produk_id' => $produk['produk_id'],
                    'jumlah' => $produk['kuantitas'],
                    'satuan_jumlah' => 'liter',
                    'no_faktur' => $d['kode_pembelian'],
                    'no_lo' => $d['kode_pembelian'],
                    'nama_dokumen' => $produk['file_lo']['nama_dokumen'],
                    'nama_file' => $produk['file_lo']['nama_file'],
                    'tanggal_upload' => $d['tgl_pengiriman'] ?? $d['tgl_pembelian']
                ]);

                DB::table('detail_rencana_file')->insert([
                    'detail_rencana_pembelian_id' => $detailRencanaPembelian->detail_rencana_pembelian_id,
                    'file_lo_id' => $fileLo->file_lo_id
                ]);
            }
        }
    }

    public function uploadDataTotalisator()
    {
        // Totalisator
        // $pertaliteN1 = [
        //     [
        //         2294, 0, 0, 1858, 2581, 1830, 1431, 2017, 1706, 1784, 1574, 1855, 1518, 1724, 1622, 1490, 1641, 1158, 1515, 944, 2090, 486, 1649, 1748, 2400, 1453, 1921, 1854, 1534, 1427
        //     ],
        //     [
        //         3000, 0, 0, 2517, 555, 1336, 542, 1276, 951, 932, 1217, 1002, 1423, 1098, 1085, 1004, 1071, 1289, 1427, 1024, 1700, 1432, 1104, 1517, 1574, 2773, 944, 2004, 1584, 1494
        //     ],
        //     [
        //         0, 0, 0, 0, 0, 1287, 880, 2084, 3321, 1912, 1567, 0, 0, 0, 0, 0, 0, 0, 1100, 0, 70, 1986, 1064, 0, 0, 0, 0, 0, 1864, 0
        //     ]
        // ];
        // $pertaliteN2 = [
        //     [
        //         4565, 0, 0, 3760, 3421, 3927, 2612, 3000, 3229, 3513, 2661, 2937, 3637, 3274, 2842, 2736, 3537, 3116, 3200, 1755, 3310, 1456, 3521, 2999, 3782, 2813, 2855, 3566, 3521, 3011
        //     ],
        //     [
        //         4428, 0, 0, 4143, 925, 2337, 1570, 2254, 1447, 1275, 1821, 2055, 2055, 1895, 2298, 2461, 978, 2197, 1687, 1569, 4019, 3517, 2251, 2532, 2833, 2427, 1121, 3195, 2308, 2509
        //     ],
        //     [
        //         0, 0, 0, 0, 0, 1683, 1100, 4319, 4320, 2339, 2699, 0, 0, 0, 0, 0, 0, 0, 1100, 0, 280, 3889, 2096, 0, 0, 0, 0, 0, 2641, 0
        //     ]
        // ];
        // $bioSolar = [
        //     [
        //         1446, 4516, 4725, 0, 4251, 3406, 3149, 4994, 3959, 3519, 4187, 0, 0, 0, 0, 3953, 0, 11, 0, 2280, 2497, 2463, 2097, 1477, 2052, 5136, 4311, 4004, 3778, 5576, 4139
        //     ],
        //     [
        //         2052, 3881, 473, 0, 2183, 1478, 0, 3170, 3833, 3859, 0, 0, 0, 0, 0, 46, 0, 0, 0, 0, 0, 0, 0, 29, 0, 3956, 1207, 2804, 4743, 4876, 2187
        //     ],
        //     [
        //         0, 2910, 0, 0, 0, 0, 22, 1376, 3238, 0, 3406, 0, 1740, 12936, 0, 16001, 7496, 1605, 10074, 2363, 7916, 2127, 10921, 990, 0, 0, 0, 0, 0, 22, 10204
        //     ]
        // ];
        // $pertamax = [
        //     [
        //         0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        //     ],
        //     [
        //         0, 0, 63, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        //     ],
        //     [
        //         0, 0, 0, 3.312, 0, 0, 0, 0, 0, 0, 0, 0, 2.751, 1.641, 0, 5.130, 3.165, 1.020, 1.530, 3.369, 2.881, 4.806, 0, 0, 0, 0, 0, 0, 0, 0, 0
        //     ]
        // ];
        // $pertadex = [
        //     [
        //         0, 0, 0, 0, 0, 0, 0, 0, 39, 0, 23, 0, 61, 0, 7, 0, 6, 0, 12, 0, 44, 0, 0, 23, 0, 0, 13, 0, 0, 0, 19
        //     ],
        //     [
        //         0, 0, 0, 0, 0, 0, 0, 52, 24, 0, 0, 0, 0, 13, 0, 0, 26, 53, 0, 0, 3, 0, 0, 27, 0, 0, 8, 26, 0, 0, 0
        //     ],
        //     [
        //         0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        //     ]
        // ];

        // BioSolar
        // $dombakBs1 = Dombak::where('nama_dombak', 'DM4-BS')->first();
        // // $dombakBs2 = Dombak::where('nama_dombak', 'DM5-BS')->first();

        // // Nozzle
        // $nozzleN1 = Nozzle::where('nama_nozzle', 'N-3A BS')->first();

        // // Jadwal Kerja
        // $jadwalKerja = [
        //     'd61ddc4b-f97c-4361-935a-26718af856d7',
        //     '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
        //     'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        // ];

        // $tgl = 1;
        // $bioSolarFlip = $this->flip_row_col_array($bioSolar);
        // $standMeterLast = 13748394;
        // for ($i = 0; $i < count($bioSolarFlip); $i++) {
        //     for ($j = 0; $j < count($bioSolarFlip[$i]); $j++) {

        //         $nozzleN1->stand_meter()->create([
        //             'dombak_id' => $dombakBs1->dombak_id,
        //             'jam_kerja_id' => $jadwalKerja[$j],
        //             'tanggal' => '2024-01-' . ($tgl + $i),
        //             'stand_meter_awal' => $standMeterLast,
        //             'stand_meter_akhir' => $standMeterLast + $bioSolarFlip[$i][$j],
        //             'is_confirmation' => false
        //         ]);

        //         $standMeterLast += $bioSolarFlip[$i][$j];
        //     }
        // }


        //Pertalite
        // $dombakPl = Dombak::where('nama_dombak', 'DM2-PL')->first();

        // // Nozzle
        // $nozzleN1 = Nozzle::where('nama_nozzle', 'N-2A PL')->first();
        // $nozzleN2 = Nozzle::where('nama_nozzle', 'N-2B PL')->first();

        // // Jadwal Kerja
        // $jadwalKerja = [
        //     'd61ddc4b-f97c-4361-935a-26718af856d7',
        //     '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
        //     'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        // ];

        // $tgl = 2;
        // $pertaliteN1Flip = $this->flip_row_col_array($pertaliteN1);
        // $standMeterLast = 7701156.00;
        // for ($i = 0; $i < count($pertaliteN1Flip); $i++) {
        //     for ($j = 0; $j < count($pertaliteN1Flip[$i]); $j++) {

        //         $nozzleN1->stand_meter()->create([
        //             'dombak_id' => $dombakPl->dombak_id,
        //             'jam_kerja_id' => $jadwalKerja[$j],
        //             'tanggal' => '2024-01-' . ($tgl + $i),
        //             'stand_meter_awal' => $standMeterLast,
        //             'stand_meter_akhir' => $standMeterLast + $pertaliteN1Flip[$i][$j],
        //             'is_confirmation' => false
        //         ]);

        //         $standMeterLast += $pertaliteN1Flip[$i][$j];
        //     }
        // }

        // $tgl = 2;
        // $pertaliteN2Flip = $this->flip_row_col_array($pertaliteN2);
        // $standMeterLast = 8351384.00;
        // for ($i = 0; $i < count($pertaliteN2Flip); $i++) {
        //     for ($j = 0; $j < count($pertaliteN2Flip[$i]); $j++) {

        //         $nozzleN2->stand_meter()->create([
        //             'dombak_id' => $dombakPl->dombak_id,
        //             'jam_kerja_id' => $jadwalKerja[$j],
        //             'tanggal' => '2024-01-' . ($tgl + $i),
        //             'stand_meter_awal' => $standMeterLast,
        //             'stand_meter_akhir' => $standMeterLast + $pertaliteN2Flip[$i][$j],
        //             'is_confirmation' => false
        //         ]);

        //         $standMeterLast += $pertaliteN2Flip[$i][$j];
        //     }
        // }

        // Pertamax

        // $dombakPl = Dombak::where('nama_dombak', 'DM1-PX')->first();

        // // Nozzle
        // $nozzleN1 = Nozzle::where('nama_nozzle', 'N-1B PX')->first();

        // // Jadwal Kerja
        // $jadwalKerja = [
        //     'd61ddc4b-f97c-4361-935a-26718af856d7',
        //     '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
        //     'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        // ];

        // $tgl = 1;
        // $pertamaxN1 = $this->flip_row_col_array($pertamax);
        // $standMeterLast = 3.00;
        // for ($i = 0; $i < count($pertamaxN1); $i++) {
        //     for ($j = 0; $j < count($pertamaxN1[$i]); $j++) {

        //         $nozzleN1->stand_meter()->create([
        //             'dombak_id' => $dombakPl->dombak_id,
        //             'jam_kerja_id' => $jadwalKerja[$j],
        //             'tanggal' => '2024-01-' . ($tgl + $i),
        //             'stand_meter_awal' => $standMeterLast,
        //             'stand_meter_akhir' => $standMeterLast + $pertamaxN1[$i][$j],
        //             'is_confirmation' => false
        //         ]);

        //         $standMeterLast += $pertamaxN1[$i][$j];
        //     }
        // }


        // Pertamina Dex
        // $dombakPl = Dombak::where('nama_dombak', 'DM6-PX')->first();

        // // Nozzle
        // $nozzleN1 = Nozzle::where('nama_nozzle', 'N-3B PX')->first();

        // // Jadwal Kerja
        // $jadwalKerja = [
        //     'd61ddc4b-f97c-4361-935a-26718af856d7',
        //     '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
        //     'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        // ];

        // $tgl = 1;
        // $pertaminaDex = $this->flip_row_col_array($pertadex);
        // $standMeterLast = 134082;
        // for ($i = 0; $i < count($pertaminaDex); $i++) {
        //     for ($j = 0; $j < count($pertaminaDex[$i]); $j++) {

        //         $nozzleN1->stand_meter()->create([
        //             'dombak_id' => $dombakPl->dombak_id,
        //             'jam_kerja_id' => $jadwalKerja[$j],
        //             'tanggal' => '2024-01-' . ($tgl + $i),
        //             'stand_meter_awal' => $standMeterLast,
        //             'stand_meter_akhir' => $standMeterLast + $pertaminaDex[$i][$j],
        //             'is_confirmation' => false
        //         ]);

        //         $standMeterLast += $pertaminaDex[$i][$j];
        //     }
        // }

        return response()->json([
            'data' => [],
            'message' => 'Ok',
            'status' => 'success'
        ]);
        exit;
    }

    function flip_row_col_array($array)
    {
        $out = array();
        foreach ($array as  $rowkey => $row) {
            foreach ($row as $colkey => $col) {
                $out[$colkey][$rowkey] = $col;
            }
        }
        return $out;
    }
}
