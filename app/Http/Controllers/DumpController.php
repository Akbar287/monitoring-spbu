<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\Nozzle;
use App\Models\PajakPembelianLib;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\Spbu;
use App\Models\StatusPurchase;
use App\Models\Supir;
use App\Models\TypeDokumenStok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DumpController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function uploadDataStokTaking()
    {
        $spbu = Spbu::where('nama_spbu', 'SPBU 2434520')->first();
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];
        $type_dokumen_stock = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Stok Taking')->first();

        $dombakRaw = Dombak::select('nama_dombak', 'dombak_id')->orderBy('nama_dombak', 'asc')->get()->toArray();
        $dombak = [];
        foreach ($dombakRaw as $item) {
            $dombak[$item["nama_dombak"]] = $item["dombak_id"];
        }
        unset($dombakRaw);
        // dd($dombak);
        // [
        //     "DM1-PX" => "7ba4e9f2-0e71-402f-8199-4d21f20eea48"
        //     "DM2-PL" => "8fd5fafd-5add-4688-9b0a-76a8f6e237c4"
        //     "DM3-DX" => "0e3b3f9c-92fe-452f-9c7e-8d663217315e"
        //     "DM4-BS" => "67f1255b-3b89-4164-bd0e-ea9279a1b640"
        //     "DM5-BS" => "b812ee18-7f60-4232-801f-f635bf765dcd"
        //     "DM6-PDX" => "6affaa45-f173-4605-8a84-08cee72fdf98"
        // ]


        // BioSolar 1

        $stok_inventory = Dombak::where('dombak_id', $dombak['DM4-BS'])->first()->stok_inventory_dombak()->first()->stok_inventory()->first();
        $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
            'stok_inventory_id' => $stok_inventory->stok_inventory_id,
            'jam_kerja_id' => $request->jam_kerja_id,
            'member_id' => Auth::user()->member_id,
            'dombak_id' => $dombak['DM4-BS'],
            'tanggal' => $request->tanggal,
            'stok_awal' => $awal,
            'stok_akhir' => $akhir,
            'is_confirmation' => false
        ]);

        DB::table('konversi_dokumen_stok')->insert([
            'konversi_id' => $tinggiDombak->konversi_id,
            'dokumen_stok_id' => $dokumenStok->dokumen_stok_id
        ]);

        $type_losses = JenisLosses::where('jenis_loss', 'Stok Taking')->first();
        if ($akhir - $awal < 0) {
            // Loss
            if (is_null(
                $dokumenStok->losses()->where('jenis_losses_id', $type_losses->jenis_losses_id)->where('tanggal', $request->tanggal)->where('simbol', 'kurang')->where('jumlah', ($akhir - $awal) * -1)->first()
            )) {
                $dokumenStok->losses()->create([
                    'jenis_losses_id' => $type_losses->jenis_losses_id,
                    'tanggal' => $request->tanggal,
                    'simbol' => 'kurang',
                    'jumlah' => ($akhir - $awal) * -1
                ]);
            }
        } else {
            // Profit
            if (is_null(
                $dokumenStok->losses()->where('jenis_losses_id', $type_losses->jenis_losses_id)->where('tanggal', $request->tanggal)->where('simbol', 'lebih')->where('jumlah', $akhir - $awal)->first()
            )) {
                $dokumenStok->losses()->create([
                    'jenis_losses_id' => $type_losses->jenis_losses_id,
                    'tanggal' => $request->tanggal,
                    'simbol' => 'lebih',
                    'jumlah' => $akhir - $awal
                ]);
            }
        }
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
        $pertaliteN1 = [
            [
                3685, 3333, 3447, 0, 3281, 4894, 572, 3477, 4633, 3096, 8192, 4570, 3782, 4115, 3722, 1684, 3773, 3318
            ],
            [
                2247, 0, 3614, 3525, 2093, 2998, 3607, 3584, 2136, 0, 0, 3825, 5428, 3149, 3130, 4464, 2425, 3354
            ]
        ];
        $pertaliteN2 = [
            [
                5293, 4937, 4534, 0, 4019, 6172, 552, 4504, 6507, 4875, 11133, 7346, 5273, 6911, 4966, 2093, 5797, 5741
            ],
            [
                3699, 0, 4390, 5168, 2793, 3878, 4373, 4404, 2691, 0, 0, 4815, 6441, 4110, 4867, 6168, 3908, 4444
            ]
        ];
        $bioSolar = [
            [
                5593, 6319, 5080, 4329, 4365, 4368, 2852, 3448, 2406, 2396, 4276, 2383, 3188, 2440, 3917, 4106, 4438, 5244
            ],
            [
                2440, 4814, 3712, 3674, 1640, 2332, 2683, 2237, 1270, 0, 0, 1539, 2996, 2538, 2270, 2187, 4700, 4157
            ]
        ];
        $pertamax = [
            [
                264.89, 252.08, 623.87, 1374.52, 595.22, 612.46, 860.77, 1546.01, 1367.49, 1550.38, 3559.59, 1514.32, 2200.45, 1761.69, 1366.51, 1580.55, 602.98, 429.90,
            ],
            [
                78.88, 779.81, 183.32, 276.56, 583.67, 695.99, 286.84, 1096.90, 756.28, 0, 0, 1234.66, 916.81, 1904.87, 551.49, 478.31, 633.46, 184.78
            ]
        ];
        $pertadex = [
            [
                19, 10, 10, 93, 13, 103, 49, 0, 0, 20, 153, 42, 58, 0, 45, 10, 0, 18
            ],
            [
                0, 19, 0, 31, 38, 37, 0, 109, 0, 0, 0, 6, 98, 7, 78, 72, 9, 0
            ]
        ];

        // BioSolar
        $dombakBs1 = Dombak::where('nama_dombak', 'DM4-BS')->first();
        // $dombakBs2 = Dombak::where('nama_dombak', 'DM5-BS')->first();

        // Nozzle
        $nozzleBs1 = Nozzle::where('nama_nozzle', 'N-3A BS')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];

        $tgl = 1;
        $bioSolarFlip = $this->flip_row_col_array($bioSolar);
        $standMeterLast = 14474241.00;
        for ($i = 0; $i < count($bioSolarFlip); $i++) {
            for ($j = 0; $j < count($bioSolarFlip[$i]); $j++) {
                $nozzleBs1->stand_meter()->create([
                    'dombak_id' => $dombakBs1->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-04-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $bioSolarFlip[$i][$j],
                    'is_confirmation' => false
                ]);

                $standMeterLast += $bioSolarFlip[$i][$j];
            }
        }

        // Pertalite
        $dombakPl = Dombak::where('nama_dombak', 'DM2-PL')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-2A PL')->first();
        $nozzleN2 = Nozzle::where('nama_nozzle', 'N-2B PL')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            // 'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];

        $tgl = 1;
        $pertaliteN1Flip = $this->flip_row_col_array($pertaliteN1);
        $standMeterLast = 8086824.00;
        for ($i = 0; $i < count($pertaliteN1Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN1Flip[$i]); $j++) {

                $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-04-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertaliteN1Flip[$i][$j],
                    'is_confirmation' => false
                ]);

                $standMeterLast += $pertaliteN1Flip[$i][$j];
            }
        }

        $tgl = 1;
        $pertaliteN2Flip = $this->flip_row_col_array($pertaliteN2);
        $standMeterLast = 9012554.00;
        for ($i = 0; $i < count($pertaliteN2Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN2Flip[$i]); $j++) {

                $nozzleN2->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-04-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertaliteN2Flip[$i][$j],
                    'is_confirmation' => false
                ]);

                $standMeterLast += $pertaliteN2Flip[$i][$j];
            }
        }

        // Pertamax
        $dombakPl = Dombak::where('nama_dombak', 'DM1-PX')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-1B PX')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];

        $tgl = 1;
        $pertamaxN1 = $this->flip_row_col_array($pertamax);
        $standMeterLast = 24813.73;
        for ($i = 0; $i < count($pertamaxN1); $i++) {
            for ($j = 0; $j < count($pertamaxN1[$i]); $j++) {

                $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-04-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertamaxN1[$i][$j],
                    'is_confirmation' => false
                ]);

                $standMeterLast += $pertamaxN1[$i][$j];
            }
        }


        // Pertamina Dex
        $dombakPl = Dombak::where('nama_dombak', 'DM6-PX')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-3B PX')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];

        $tgl = 1;
        $pertaminaDex = $this->flip_row_col_array($pertadex);
        $standMeterLast = 136143.00;
        for ($i = 0; $i < count($pertaminaDex); $i++) {
            for ($j = 0; $j < count($pertaminaDex[$i]); $j++) {

                $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-04-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertaminaDex[$i][$j],
                    'is_confirmation' => false
                ]);

                $standMeterLast += $pertaminaDex[$i][$j];
            }
        }

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
