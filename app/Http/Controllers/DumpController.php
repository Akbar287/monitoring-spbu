<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\JenisLosses;
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

    public function help()
    {

        return response()->json([], 200);
    }

    public function uploadStokTaking()
    {
        $data = [
            [3137.5, 3152], [3133, 3137.5], [3137.5, 3114.2], [3114.2, 3114.2], [3114.2, 3111.5], [3111.5, 3114.2], [3114.2, 3139.4], [3139.4, 3114.2], [3114.2, 3107], [3107, 3125], [3125, 3137.5], [3137.5, 3072.8], [3014.8, 3041.2], [2989.2, 2989], [2989, 2980], [2956, 2957.4], [2950.4, 2925], [2925, 2929.6], [2929.6, 2935.8], [2922.8, 2935.8], [2935.8, 2929.6], [2871.6, 2851], [2830, 2820.4], [2820.4, 2833], [2809, 2806], [2806, 2795.2], [2795.2, 2795.2], [2764.2, 2756.4], [2704.4, 2695], [2695, 2695], [2695, 2699.7], [2680.7, 2672], [2672, 2662.8], [2649.8, 2658.2], [2658.2, 2649], [2649, 2653.6], [2653.6, 2656.2], [2643.2, 2631.8], [2631.8, 2624.6], [2611.6, 2603], [2603, 2607.6], [2607.6, 2603], [2590, 2610.2], [2532.2, 2514.7], [2514.7, 2481], [2443, 2463], [2463, 2453.6], [2453.6, 2448.4], [2411.4, 2419.6], [2419.6, 2416], [2416, 2419.6], [2387.6, 2376.2], [2376.2, 2369], [2369, 2376.2], [2376.2, 2379.8], [2366.8, 2369], [2369, 2359.8], [2295.8, 2276], [2276, 2224.3], [2224.3, 2276], [2235, 2229], [2213, 2205.5], [2205.5, 2210.2], [2210.2, 2210.8], [2197.8, 2189.2], [2189.2, 2189.2], [2139.2, 2139.6], [2139.6, 2136], [2136, 2136], [2136, 2139.6], [2104.6, 2068.2], [2068.2, 2084.4], [1979.4, 1966.4], [1933.4, 1935.8], [1935.8, 1916.8], [1894.8, 1890.8], [1890.8, 1894.4], [1894.4, 1887.2], [1854.2, 1862], [1849, 1839.5], [1839.5, 1839.5], [1794.5, 1804.4], [1804.4, 1794.5], [1794.5, 1782.8], [1759.8, 1772], [1772, 1772], [1772, 1772], [1772, 1772], [1772, 1772], [1772, 1772]
        ];

        $jamKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];

        $dombakId = '6affaa45-f173-4605-8a84-08cee72fdf98';
        $type_dokumen_stock = TypeDokumenStok::where('type_dokumen_stok_id', 'd1e2bcb5-c5e7-4c64-b9a2-1d451e3639ff')->first();
        $stok_inventory = Dombak::where('dombak_id', $dombakId)->first()->stok_inventory_dombak()->first()->stok_inventory()->first();
        $iter = 0;
        $tgl = 1;

        for ($i = 0; $i < count($data); $i++) {
            $dokumenStok = null;

            if ($type_dokumen_stock->type_movement == 'Stok Taking') {
                $awal = $data[$i]['0'];
                $akhir = $data[$i]['1'];

                $dokumenStok = $type_dokumen_stock->dokumen_stok()->where('stok_inventory_id', $stok_inventory->stok_inventory_id)->where('jam_kerja_id', $jamKerja[$iter])->where('member_id', Auth::user()->member_id)->where('dombak_id', $dombakId)->where('tanggal', '2024-05-' . $tgl)->where('stok_awal', $awal)->where('stok_akhir', $akhir)->where('is_confirmation', false)->first();
                if (is_null($dokumenStok)) {
                    $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
                        'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                        'jam_kerja_id' => $jamKerja[$iter],
                        'member_id' => Auth::user()->member_id,
                        'dombak_id' => $dombakId,
                        'tanggal' => '2024-05-' . $tgl,
                        'stok_awal' => $awal,
                        'stok_akhir' => $akhir,
                        'is_confirmation' => false
                    ]);
                }

                $tinggiDombak = Dombak::where('dombak_id', $dombakId)->first()->konversi()->where('volume', $akhir)->first();
                if (!is_null($tinggiDombak)) {
                    if (is_null(DB::table('konversi_dokumen_stok')->where(
                        'konversi_id',
                        $tinggiDombak->konversi_id
                    )->where('dokumen_stok_id', $dokumenStok->dokumen_stok_id)->first())) {
                        DB::table('konversi_dokumen_stok')->insert([
                            'konversi_id' => $tinggiDombak->konversi_id,
                            'dokumen_stok_id' => $dokumenStok->dokumen_stok_id
                        ]);
                    }
                }

                $type_losses = JenisLosses::where('jenis_loss', 'Stok Taking')->first();
                if ($akhir - $awal < 0) {
                    // Loss
                    if (is_null(
                        $dokumenStok->losses()->where('jenis_losses_id', $type_losses->jenis_losses_id)->where('tanggal', '2024-05-' . $tgl)->where('simbol', 'kurang')->where('jumlah', ($akhir - $awal) * -1)->first()
                    )) {
                        $dokumenStok->losses()->create([
                            'jenis_losses_id' => $type_losses->jenis_losses_id,
                            'tanggal' => '2024-05-' . $tgl,
                            'simbol' => 'kurang',
                            'jumlah' => ($akhir - $awal) * -1
                        ]);
                    }
                } else {
                    // Profit
                    if (is_null(
                        $dokumenStok->losses()->where('jenis_losses_id', $type_losses->jenis_losses_id)->where('tanggal', '2024-05-' . $tgl)->where('simbol', 'lebih')->where('jumlah', $akhir - $awal)->first()
                    )) {
                        $dokumenStok->losses()->create([
                            'jenis_losses_id' => $type_losses->jenis_losses_id,
                            'tanggal' => '2024-05-' . $tgl,
                            'simbol' => 'lebih',
                            'jumlah' => $akhir - $awal
                        ]);
                    }
                }
            }
            if ($iter == 2) {
                $iter = 0;
                $tgl++;
            } else {
                $iter++;
            }
        }


        return response()->json(['message' => 'done', 'status' => true], 200);
    }

    public function uploadDataStokTaking()
    {
        // Semua dimulai dari Februari

        // Pertamax
        $dataPertamax = [
            [
                2606.6, 2343.1, 2209.0, 2234.0, 1836.8, 1674.0, 1697.3, 1478.3, 1309.8, 1303.1, 9002.6, 9031.9, 8992.9, 8848.0, 8573.5, 8592.4, 8118.4, 7892.9, 7768.0, 7725.5, 7491.3, 7562.0, 6696.3, 6310.4, 6340.7, 6057.9, 5926.6, 5916.5, 5738.4, 5581.8, 5581.8, 5134.1, 4854.7, 4854.7, 4588.4, 4306.6, 4258.7, 3945.6, 3532.7, 3468.3, 2884.4, 2477.1, 2477.1, 1758.8, 1356.7, 1356.7, 9120.2, 8925.2, 8953.8, 8819.3, 8449.8, 8459.2, 8179.0, 7913.8, 7977.0, 7903.4, 7820.3, 7715.1, 7400.4, 7208.5, 7218.6, 6852.9, 6667.0, 6656.9, 6007.4, 5591.5, 5611.1, 5396.5, 5288.8, 5288.8, 5018.9, 4913.2, 4913.2, 4778.0, 4515.0, 4515.0, 4422.0, 12427.4, 12498.1, 12114.3, 11983.0, 12053.7, 11882.0, 11694.0, 11754.6, 11665.3, 11521.2, 11502.0, 11184.7, 11031.8
            ],
            [
                0, 0, 0, 0, 0, 0, 0, 0, 0, [936.9, 8000.0, 9237.0], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, [1356.7, 8000.0, 9492.4], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, [4345.2, 8000.0, 12538.5], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ],
            [
                247.8, 145.4, 0, 350.4, 140.7, 0, 244.2, 144.8, 0, 345.1, 122.9, 0, 194.8, 205.2, 0, 509.2, 171.9, 0, 226.9, 137.0, 0, 821.9, 338.4, 0, 289.9, 90.3, 0, 205.3, 126.4, 0, 430.4, 272.3, 0, 316.9, 238.9, 0, 371.9, 405.3, 0, 609.9, 411.1, 0, 731.5, 362.7, 0, 388.2, 179.6, 0, 154.2, 352.6, 0, 297.6, 173.0, 0, 140.3, 117.6, 0, 317.0, 141.7, 0, 64.0, 0, 0, 662.7, 368.5, 0, 260.4, 65.6, 0, 251.1, 117.7, 0, 137.5, 246.4, 0, 145.0, 189.0, 0, 312.6, 81.9, 0, 130.5, 67.4, 0, 158.2, 43.4, 0, 341.8, 180.3, 0
            ],
            [
                2358.8, 2197.7, 2209.0, 1883.6, 1696.1, 1674.0, 1453.1, 1333.6, 1309.8, 8958.0, 8879.7, 9031.9, 8798.1, 8642.8, 8573.5, 8083.2, 7946.5, 7892.9, 7541.1, 7588.5, 7491.3, 6740.1, 6357.9, 6310.4, 6050.8, 5967.6, 5926.6, 5711.2, 5612.0, 5581.8, 5151.4, 4861.8, 4854.7, 4537.8, 4349.5, 4306.6, 3886.8, 3540.3, 3532.7, 2858.4, 2473.3, 2477.1, 1745.6, 1396.1, 1356.7, 8968.5, 8940.6, 8925.2, 8799.6, 8466.7, 8449.8, 8161.6, 8006.0, 7913.8, 7660.0, 7785.8, 7820.3, 7398.1, 7258.7, 7208.5, 7154.6, 6852.9, 6667.0, 5994.2, 5638.9, 5591.5, 5350.7, 5330.9, 5288.8, 5037.7, 4901.2, 4913.2, 4775.7, 4531.6, 4515.0, 4370.0, 12233.0, 12427.4, 12185.6, 12032.4, 11983.0, 11923.2, 11814.6, 11694.0, 11596.4, 11621.9, 11521.2, 11160.2, 11004.4, 11031.8
            ],
            [
                2343.1, 2209.0, 2234.0, 1836.8, 1674.0, 1697.3, 1478.3, 1309.8, 1303.1, 9002.6, 9031.9, 8992.9, 8848.0, 8573.5, 8592.4, 8118.4, 7892.9, 7768.0, 7725.5, 7491.3, 7562.0, 6696.3, 6310.4, 6340.7, 6057.9, 5926.6, 5916.5, 5738.4, 5581.8, 5581.8, 5134.1, 4854.7, 4854.7, 4588.4, 4306.6, 4258.7, 3945.6, 3532.7, 3468.3, 2884.4, 2477.1, 2477.1, 1758.8, 1356.7, 1356.7, 9120.2, 8925.2, 8953.8, 8819.3, 8449.8, 8459.2, 8179.0, 7913.8, 7977.0, 7903.4, 7820.3, 7715.1, 7400.4, 7208.5, 7218.6, 6852.9, 6667.0, 6656.9, 6007.4, 5591.5, 5611.1, 5396.5, 5288.8, 5288.8, 5018.9, 4913.2, 4913.2, 4778.0, 4515.0, 4515.0, 4422.0, 12427.4, 12498.1, 12114.3, 11983.0, 12053.7, 11882.0, 11694.0, 11754.6, 11665.3, 11521.2, 11502, 11184.7, 11031.8, 11023.4
            ]
        ];

        // Pertalite
        $dataPertalite = [
            [
                6989.0, 8853.4, 789.2, 799.4, 7876.6, 8812.2, 8853.4, 920.0, 5579.9, 5589.9, 8090.2, 11642.7, 11670.8, 1080.0, 3386.6, 11652.1, 11043.9, 3475.6, 11718.0, 3928.8, 619.0, 573.3, 601.7, 590.4, 8812.2, 9193.3, 6371.0, 6350.4, 8832.8, 2052.1, 2036.7, 14461.4, 3227.4, 3200.7, 12092.0, 2768.0, 2759.5, 1703.0, 596.0, 596.0, 3845.1, 590.4, 601.7, 7866.0, 601.7, 8874.0, 3835.8, 579.0, 8843.1, 884.0, 5729.8, 5729.8, 607.4, 3994.0, 12289.2, 12600.0, 12812.5, 12787.0, 12767.5, 5859.0, 5829.8, 590.4, 3050.4, 3041.5, 596.0, 4873.0, 13089.6, 3209.6, 5819.8, 13969.9, 15192.0, 7962.3, 7973.0, 8874.0, 3014.9, 11230.9, 2508.2, 2946.5, 11212.2, 11174.7, 4113.8, 12344.1, 12642.5, 4645.7, 12889.0, 12889.0, 4324.6, 4324.6, 2623.5, 3528.8
            ],
            [
                8000.0, 0, 0, 16000.00, 8000.0, 0, 0, 8000.0, 0, 8000.00, 8000.00, 0, 0,  8000.0,  8000.0,  8000.0, 0,  8000.0, 0, 0, 0, 0,  8000.0,  8000.0,  8000.0,  8000.0, 0,  8000.0, 0, 0,  16000.0, 0, 0,  16000.0, 0, 0,  8000.0, 0, 0,  8000.0, 0, 0,  8000.0, 0,  8000.0,  8000.0, 0,  8000.0, 0,  8000.0, 0, 0,  8000.0,  8000.0,  8000.0,  8000.0, 0,  8000.0, 0, 0, 0,  8000.0, 0, 0,  8000.0,  8000.0, 0,  8000.0,  8000.0,  8000.0, 0, 0,  8000.0,  0,  8000.0, 0,  8000.0,  8000.0,  8000.0, 0,  8000.0,  8000.0, 0,  8000.0,  8000.0, 0, 0, 0,  8000.0,  8000.0
            ],
            [
                [2012, 4228], [2876, 4928], [0, 0], [4232, 4954], [2465, 4592], [0, 0], [2910, 4761], [1295, 2183], [0, 0], [2070, 3621], [1581, 2894], [0, 0], [4051, 6248], [2389, 3346], [0, 0], [3381, 5243], [3373, 3955], [0, 0], [2625, 4941], [1285, 2003], [0, 0], [0, 0], [3462, 4546], [0, 0], [3317, 4692], [4789, 5610], [0, 0], [1944, 3696], [2527, 4028], [0, 0], [1682, 2112], [5294, 5787], [0, 0], [2755, 4709], [4122, 4902], [0, 0], [3644, 5406], [466, 640], [0, 0], [2235, 2611], [1434, 1740], [0, 0], [455, 536], [2617, 4412], [0, 0], [5513, 7318], [1118, 2060], [0, 0], [2674, 5045], [1105, 2149], [0, 0], [1995, 3039], [1639, 2997], [0, 0], [2716, 5008], [2857, 4917], [0, 0], [2770, 5406], [2685, 3947], [0, 0], [1865, 3282], [2095, 3483], [0, 0], [723, 1708], [1778, 2024], [0, 0], [3772, 5844], [2273, 3166], [0, 0], [2654, 4131], [2657, 4577], [0, 0], [2456, 4735], [2141, 3479], [0, 0], [3172, 5328], [2891, 4709], [0, 0], [2977, 5065], [2292, 4554], [0, 0], [2897, 4881], [3079, 4640], [0, 0], [3005, 5069], [2926, 5353], [0, 0], [602, 1109], [2482, 4590], [0, 0]
            ],
            [
                8749.0, 1049.4, 789.2, 7613.4, 8819.6, 8812.2, 1182.4, 5442.0, 5579.9, 7898.9, 11615.2, 11642.7, 1371.8, 3345.0, 11386.6, 11028.1, 3715.9, 11475.6, 4152.0, 640.8, 619.0, 573.3, 593.7, 8590.4, 8803.2, 6794.3, 6371.0, 8710.4, 2277.8, 2052.1, 14242.7, 3380.4, 3227.4, 11736.7, 3068.0, 2768.0, 1709.5, 597.0, 596.0, 3750.0, 671.1, 590.4, 7610.7, 837.0, 8601.7, 4043.0, 657.8, 8579.0, 1124.1, 5630.0, 5729.8, 695.8, 3971.4, 11994.0, 12565.2, 12826.0, 12812.5, 12611.0, 6135.5, 5859.0, 682.8, 3012.4, 3050.4, 610.5, 4794.0, 12873.0, 3473.6, 5770.6, 13819.8, 15184.9, 7958.0, 7962.3, 8782.0, 3254.0, 11014.9, 2730.9, 2908.2, 10946.5, 11170.2, 4328.7, 12113.8, 12566.1, 4923.5, 12645.7, 12815.0, 4610.0, 4324.6, 2613.6, 3551.5, 11528.8
            ],
            [
                8853.4, 789.2, 799.4, 7876.6, 8812.2, 8853.4, 920.0, 5579.9, 5589.9, 8090.2, 11642.7, 11670.8, 1080.0, 3386.6, 11652.1, 11043.9, 3475.6, 11718.0, 3928.8, 619.0, 573.3, 601.7, 590.4, 8812.2, 9193.3, 6371.0, 6350.4, 8832.8, 2052.1, 2036.7, 14461.4, 3227.4, 3200.7, 12092.0, 2768.0, 2759.5, 1703.0, 596.0, 596.0, 3845.1, 590.4, 601.7, 7866.0, 601.7, 8874.0, 3835.8, 579.0, 8843.1, 884.0, 5729.8, 5729.8, 607.4, 3994.0, 12289.2, 12600.0, 12812.5, 12787.0, 12767.5, 5859.0, 5829.8, 590.4, 3050.4, 3041.5, 596.0, 4873.0, 13089.6, 3209.6, 5819.8, 13969.9, 15192.0, 7962.3, 7973.0, 8874.0, 3014.9, 11230.9, 2508.2, 2946.5, 11212.2, 11174.7, 4113.8, 12344.1, 12642.5, 4645.7, 12889, 12889, 4324.6, 4324.6, 2623.5, 3528.8, 11839
            ]
        ];

        // Bio Solar
        $dataBioSolar = [
            [
                10449.2, 3962.0, 839.6, 9229.3, 6316.1, 6436.7, 13734.7, 10953.2, 10892.3, 903.4, 854.4, 864.0, 7909.4, 5894.8, 5904.6, 3232.0, 959.1, 854.0, 7050.6, 3487.4, 888.7, 839.6, 893.6, 868.7, 8213.9, 6316.2, 6350.5, 13747.2, 11933.3, 11881.2, 1488.2, 864.2, 820.0, 9160.7, 7402.3, 7178.0, 12707.3, 9772.1, 9830.3, 7969.9, 6526.9, 6458.7, 6132.1, 4303.4, 3426.7, 2488.7, 654.9, 625.5, 8938.8, 7046.0, 7017.0, 4745.0, 2740.9, 2750.4, 10937.6, 5776.7, 1189.0, 1199.9, 6740.4, 11703.1, 19431.8, 15189.0, 10132.5, 10219.1, 5235.3, 9896.9, 9866.4, 12514.4, 8171.3, 8179.0, 2715.0, 6690.0, 6700.1, 9418.2, 5357.0, 5396.7, 1085.8, 1080.9, 9410.0, 1071.9, 11068.5, 11031.2, 5704.0, 6467.1, 6506.4, 878.6, 3566.3
            ],
            [
                null,
                null,
                [[384.3, 8000.0, 8715.2]],
                null,
                null,
                [[5410.0, 8000.0, 13267.2]],
                null,
                null,
                null,
                null,
                null,
                [[389.2, 8000.0, 8725.0]],
                null,
                null,
                [[5429.6, 8000.0, 13244.6]],
                null,
                null,
                [[379.4, 80000, 8685.8], [7364.6, 8000.0, 14879.7]],
                null,
                null,
                null,
                null,
                null,
                [[399.0, 8000.0, 8725.0]],
                null,
                null,
                [[5842.3, 8000.0, 13627.0]],
                null,
                null,
                null,
                null,
                null,
                [[389.2, 8000.0, 8695.6]],
                null,
                null,
                [[840.0, 8000.0, 9077.8], [435.7, 8000.0, 8312.7]],
                null,
                null,
                [[389.2, 8000.0, 8774.0]],
                null,
                null,
                null,
                null,
                null,
                [[3197.1, 8000.0, 11256.9]],
                null,
                null,
                [[389.2, 8000.0, 8725.0]],
                null,
                null,
                null,
                null,
                null,
                [[2546.4, 8000.0, 10669.6]],
                null,
                null,
                null,
                [[951.0, 8000.0, 9224.8]],
                [[5194.4, 8000.0, 13162.1]],
                [[215.8, 8000.0, 7972.5]],
                null,
                null,
                null,
                null,
                [[3006.0, 8000.0, 11061.2]],
                null,
                [[3861.1, 8000.0, 12120.5]],
                null,
                null,
                null,
                [[1703.5, 8000.0, 9938.0]],
                null,
                [[423.5, 8000.0, 8764.2]],
                null,
                null,
                null,
                null,
                [[399.0, 8000.0, 8764.2]],
                null,
                [[408.8, 8000.0, 12981.3], [654.0, 8000.0, 4256.3]],
                null,
                null,
                [[641.6, 8000.0, 8911.2]],
                null,
                null,
                [[428.4, 8000.0, 8793.6]],
                0
            ],
            [
                [6330.0],
                [3031.0],
                [0],
                [2775.0],
                [0],
                [495.0],
                [2928.0],
                [37.0],
                [9829.0],
                [0],
                [0],
                [1263.0],
                [2027.0],
                [0],
                [10521.0],
                [2194.0],
                [59.0],
                [10198.0],
                [3393.0],
                [2481.0],
                [0],
                [0],
                [0],
                [1018.0],
                [1856.0],
                [0],
                [358.0],
                [2004.0],
                [0],
                [10330.0],
                [513.0],
                [0],
                [0],
                [2035.0],
                [0],
                [10345.0],
                [3020.0],
                [3.0],
                [10002.0],
                [1395.0],
                [51.0],
                [192.0],
                [831.0],
                [1827.0],
                [9000.0],
                [1741.0],
                [0],
                [0],
                [2044.0],
                [0],
                [2137.0],
                [1847.0],
                [1.0],
                [0],
                [5205.0],
                [4336.0],
                [0],
                [2688.0],
                [3028.0],
                [0],
                [4556.0],
                [4880.0],
                [0],
                [5023.0],
                [3497.0],
                [0],
                [5285.0],
                [4517.0],
                [0],
                [5212.0],
                [4255.0],
                [0],
                [5328.0],
                [4015.0],
                [0],
                [4053.0],
                [0],
                [0],
                [8053.0],
                [6359.0],
                [0],
                [5558.0],
                [7191.0],
                [0],
                [5274.0],
                [5504.0],
                [0]
            ],
            [
                4119.2, 931.0, 8839.6, 6454.3, 6316.1, 13941.7, 10806.7, 10916.2, 1063.3, 903.4, 854.4, 7601.0, 5882.4, 5894.8, 3383.6, 1038.0, 900.1, 6656.0, 3657.6, 1006.4, 888.7, 839.6, 893.6, 7850.7, 6357.9, 6316.2, 13992.5, 11743.2, 11933.3, 1551.2, 975.2, 864.2, 8820.0, 7125.7, 7402.3, 12833.0, 9687.3, 9769.1, 7828.3, 6574.9, 6475.9, 6266.7, 5301.1, 2476.4, 2426.7, 747.7, 654.9, 8625.5, 6894.8, 7046.0, 4880.0, 2898.0, 2739.9, 10750.4, 5732.6, 1440.7, 1189.0, 6511.9, 11712.4, 19703.1, 14875.8, 10309.0, 10132.5, 5196.1, 9738.3, 9896.9, 12581.4, 7997.4, 8171.3, 2967.0, 6460.0, 6690.0, 9372.1, 5403.2, 5357.0, 1343.7, 1085.8, 9080.9, 1357.0, 10712.9, 11068.5, 5473.2, 6513.0, 6467.1, 1232.4, 3374.6, 3566.3
            ],
            [
                3962.0, 839.6, 9229.3, 6316.1, 6436.7, 13734.7, 10953.2, 10892.3, 903.4, 854.4, 864.0, 7909.4, 5894.8, 5904.6, 3232.0, 959.1, 854.0, 7050.6, 3487.4, 888.7, 839.6, 893.6, 868.7, 8213.9, 6316.2, 6350.5, 13747.2, 11933.3, 11881.2, 1488.2, 864.2, 820.0, 9160.7, 7402.3, 7178.0, 12707.3, 9772.1, 9830.3, 7969.9, 6526.9, 6458.7, 6132.1, 4303.4, 3426.7, 2488.7, 654.9, 625.5, 8938.8, 7046.0, 7017.0, 4745.0, 2740.9, 2750.4, 10937.6, 5776.7, 1189.0, 1199.9, 6740.4, 11703.1, 19431.8, 15189.0, 10132.5, 10219.1, 5235.3, 9896.9, 9866.4, 12514.4, 8171.3, 8179.0, 2715.0, 6690.0, 6700.1, 9418.2, 5357.0, 5396.7, 1085.8, 1080.9, 9410.0, 1071.9, 11068.5, 11031.2, 5704.0, 6467.1, 6506.4, 878.6, 3566.3, 3544.4,
            ]
        ];

        $dataPertaminaDex = [
            [
                1996.5, 1989.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1944.5, 1934.5, 1934.5, 1933.5, 1897.5, 1897.5, 1894.5, 1881.5, 1881.5, 1881.5, 1875.5, 1875.5, 1862.0, 1803.5, 1776.5, 1790.0, 1749.5, 1731.5, 1758.5, 1727.0, 1695.5, 1740.5, 1677.6, 1682.0, 1660.0, 1606.2, 1601.8, 1606.2, 1571.0, 1606.2, 1606.2, 1610.6, 1606.2, 1579.8, 1579.8, 1549.0, 1642.4, 1562.2, 1549.0, 1562.2, 1492.8, 1474.9, 1457.6, 1362.8, 1332.0, 1332.0, 1289.0, 1332.0, 1332.0, 1289.0, 1327.7, 1332.0, 1268.0, 1277.8, 1289.0, 1247.0, 1251.4, 1217.6, 1205.0, 1175.6, 1188.2, 1175.6, 1171.4, 1188.2, 1100.5, 1108.7, 1005.0, 1021.0, 1013.0, 1001.0, 1001.0, 1001.0, 974.4, 963.0, 963.0, 924.0, 963.0,
            ],
            [
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ],
            [
                7.0, 19.0, 0, 0, 0, 0, 0, 0, 0, 26.0, 10.0, 0, 1.0, 36.0, 0, 3.0, 13.0, 0, 0, 6.0, 0, 26.0, 44.0, 0, 17.0, 22.0, 0, 0, 20.0, 0, 0, 39.0, 0, 2.0, 61.0, 0, 0, 0, 0, 0, 0, 0, 12.0, 10.0, 0, 0, 0, 0, 29.0, 48.0, 0, 37.0, 73.0, 0, 33.0, 0, 0, 0, 0, 0, 0, 51.0, 0, 0, 20.0, 0, 26.0, 22.0, 0, 0, 0, 0, 10.0, 62.0, 0, 75.0, 3.0, 0, 13.0, 7.0, 0, 19.0, 8.0, 0, 9.0, 0, 0,
            ],
            [
                1989.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1944.5, 1934.5, 1934.5, 1933.5, 1897.5, 1897.5, 1894.5, 1881.5, 1881.5, 1881.5, 1875.5, 1875.5, 1849.5, 1818.0, 1803.5, 1759.5, 1768.0, 1749.5, 1731.5, 1738.5, 1727.0, 1695.5, 1701.5, 1677.6, 1680.0, 1599.0, 1606.2, 1601.8, 1606.2, 1571.0, 1606.2, 1606.2, 1610.6, 1594.2, 1569.8, 1579.8, 1549.0, 1642.4, 1562.2, 1520.0, 1514.2, 1492.8, 1437.9, 1384.6, 1362.8, 1299.0, 1332.0, 1289.0, 1332.0, 1332.0, 1289.0, 1327.7, 1281.0, 1268.0, 1277.8, 1269.0, 1247.0, 1225.4, 1195.6, 1205.0, 1175.6, 1188.2, 1175.6, 1161.4, 1126.2, 1100.5, 1033.7, 1002.0, 1021.0, 1000.0, 994.0, 1001.0, 982.0, 966.4, 963.0, 954.0, 924.0, 963.0,
            ],
            [
                1989.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1970.5, 1944.5, 1934.5, 1934.5, 1933.5, 1897.5, 1897.5, 1894.5, 1881.5, 1881.5, 1881.5, 1875.5, 1875.5, 1862.0, 1803.5, 1776.5, 1790.0, 1749.5, 1731.5, 1758.5, 1727.0, 1695.5, 1740.5, 1677.6, 1682, 1660.0, 1606.2, 1601.8, 1606.2, 1571, 1606.2, 1606.2, 1610.6, 1606.2, 1579.8, 1579.8, 1549.0, 1642.4, 1562.2, 1549.0, 1562.2, 1492.8, 1474.9, 1457.6, 1362.8, 1332.0, 1332.0, 1289.0, 1332.0, 1332.0, 1289.0, 1327.7, 1332.0, 1268.0, 1277.8, 1289.0, 1247, 1251.4, 1217.6, 1205, 1175.6, 1188.2, 1175.6, 1171.4, 1188.2, 1100.5, 1108.7, 1005.0, 1021.0, 1013.0, 1001.0, 1001.0, 1001.0, 974.4, 963, 963, 924, 963, 963,
            ]
        ];
        $dataDexlite = [];

        // Inisiasi Standar

        // Dinamis
        $dombak = Dombak::select('nama_dombak', 'dombak_id')->where('nama_dombak', 'DM1-PX')->first();
        $tgl = 1;
        $bln = '05';
        $nozzle = Nozzle::where('nama_nozzle', 'N-1B PX')->first();
        $standMeterLast = [
            91342.38
        ];

        // End Dinamis
        $dataFlip = $this->flip_row_col_array($dataPertamax);
        unset($dataPertamax);
        $jamKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];
        $data = array_chunk($dataFlip, count($jamKerja));
        unset($dataFlip);

        $type_dokumen_stock = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Stok Taking')->first();
        $type_dokumen_stock_penerimaan = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Good Receive Penerimaan')->first();
        $type_dokumen_stock_penjualan = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Good Issue Sales')->first();

        $stok_inventory = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first();
        $jenisLosses = JenisLosses::where('jenis_loss', 'Stok Taking')->first();
        $stokAwal = $stok_inventory->stok;

        dd($data);
        // loop for tanggal
        for ($i = 0; $i < count($data); $i++) {
            // loop for shift
            for ($j = 0; $j < count($jamKerja); $j++) {
                // $data[$i][$j]['0']; // Awal Stok di dombak || integer
                // $data[$i][$j]['1']; // Penerimaan || integer / array
                // $data[$i][$j]['2']; // Penjualan || integer / array
                // $data[$i][$j]['3']; // Stok Akhir Teoritis || integer
                // $data[$i][$j]['4']; // Stok Akhir Dipping || integer

                if (!is_null($data[$i][$j]['1'])) {
                    for ($k = 0; $k < count($data[$i][$j]['1']); $k++) {
                        // stok taking sebelum penerimaan
                        $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
                            'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                            'jam_kerja_id' => $jamKerja[$j],
                            'member_id' => Auth::user()->member_id,
                            'dombak_id' => $dombak->dombak_id,
                            'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i)),
                            'stok_awal' => $stokAwal,
                            'stok_akhir' => $data[$i][$j]['1'][$k]['0'],
                            'is_confirmation' => true
                        ]);
                        $stokAwal = $data[$i][$j]['1'][$k]['0'];

                        // Update Stok
                        $stok_inventory->update([
                            'stok' => $stok_inventory->stok + ($data[$i][$j]['1'][$k]['0'] - $stokAwal)
                        ]);
                        $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->update([
                            'stok' => $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->stok + ($data[$i][$j]['1'][$k]['0'] - $data[$i][$j]['0'])
                        ]);

                        // Buat Relasi Konversi ke Dokumen Stok
                        $konversi = $dombak->konversi()->where('volume', $data[$i][$j]['1'][$k]['0'])->first();

                        if (!is_null($konversi)) {
                            DB::table('konversi_dokumen_stok')->insert([
                                'konversi_id' => $konversi->konversi_id,
                                'dokumen_stok_id' => $dokumenStok->dokumen_stok_id
                            ]);
                        }
                        // End Stok Taking

                        // Penerimaan
                        $dokumenStokPenerimaan = $type_dokumen_stock_penerimaan->dokumen_stok()->create([
                            'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                            'jam_kerja_id' => $jamKerja[$j],
                            'member_id' => Auth::user()->member_id,
                            'dombak_id' => $dombak->dombak_id,
                            'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i)),
                            'stok_awal' => $stokAwal,
                            'stok_akhir' => $data[$i][$j]['1'][$k]['2'],
                            'is_confirmation' => true
                        ]);

                        if ($data[$i][$j]['1'][$k]['1'] > ($data[$i][$j]['1'][$k]['2'] - $data[$i][$j]['1'][$k]['0'])) {
                            // loss
                            $dokumenStok->losses()->create([
                                'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                                'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i) . ' H:i:s'),
                                'simbol' => 'kurang',
                                'jumlah' => $data[$i][$j]['1'][$k]['1'] - ($data[$i][$j]['1'][$k]['2'] - $data[$i][$j]['1'][$k]['0'])
                            ]);
                        } else {
                            // Profit
                            $dokumenStok->losses()->create([
                                'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                                'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i) . ' H:i:s'),
                                'simbol' => 'lebih',
                                'jumlah' => $data[$i][$j]['1'][$k]['1'] - ($data[$i][$j]['1'][$k]['2'] - $data[$i][$j]['1'][$k]['0'])
                            ]);
                        }

                        $stokAwal = $data[$i][$j]['1'][$k]['2'];

                        // Update Stok
                        $stok_inventory->update([
                            'stok' => $stok_inventory->stok + ($data[$i][$j]['1'][$k]['2'] - $data[$i][$j]['1'][$k]['0'])
                        ]);
                        $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->update([
                            'stok' => $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->stok + ($data[$i][$j]['1'][$k]['2'] - $data[$i][$j]['1'][$k]['0'])
                        ]);

                        // Buat relasi Konversi ke dokumen
                        $konversi = $dombak->konversi()->where('volume', ($data[$i][$j]['1'][$k]['2']))->first();
                        if (!is_null($konversi)) {
                            DB::table('konversi_dokumen_stok')->insert([
                                'konversi_id' => $konversi->konversi_id,
                                'dokumen_stok_id' => $dokumenStokPenerimaan->dokumen_stok_id
                            ]);
                        }
                    }
                    // end of penerimaan
                }
                // penjualan
                if (!is_null($data[$i][$j]['2'])) {
                    for ($k = 0; $k < count($data[$i][$j]['2']); $k++) {
                        $dokumenStokPenjualan = $type_dokumen_stock_penjualan->dokumen_stok()->create([
                            'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                            'jam_kerja_id' => $jamKerja[$j],
                            'member_id' => Auth::user()->member_id,
                            'dombak_id' => $dombak->dombak_id,
                            'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i)),
                            'stok_awal' => $stokAwal,
                            'stok_akhir' => $stokAwal - $data[$i][$j]['2'][$k],
                            'is_confirmation' => true
                        ]);
                        $stokAwal = $stokAwal - $data[$i][$j]['2'][$k];

                        // Update Stok
                        $stok_inventory->update([
                            'stok' => $stok_inventory->stok - $data[$i][$j]['2'][$k]
                        ]);
                        $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->update([
                            'stok' => $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->stok - $data[$i][$j]['2'][$k]
                        ]);

                        // Relasi Konversi ke dokumen stok
                        $konversi = $dombak->konversi()->where('volume', ($stokAwal - $data[$i][$j]['2'][$k]))->first();

                        if (!is_null($konversi)) {
                            DB::table('konversi_dokumen_stok')->insert([
                                'konversi_id' => $konversi->konversi_id,
                                'dokumen_stok_id' => $dokumenStokPenjualan->dokumen_stok_id
                            ]);
                        }

                        $standMeter = $nozzle[$k]->stand_meter()->create([
                            'dombak_id' => $dombak->dombak_id,
                            'jam_kerja_id' => $jamKerja[$j],
                            'tanggal' => '2024-' . $bln . '-' . ($tgl + $i),
                            'stand_meter_awal' => $standMeterLast[$k],
                            'stand_meter_akhir' => $standMeterLast[$k] + $data[$i][$j]['2'][$k],
                            'is_confirmation' => true
                        ]);
                        $standMeterLast[$k] += $data[$i][$j]['2'][$k];

                        DB::table('stand_meter_dokumen_stok')->insert([
                            'stand_meter_id' => $standMeter->stand_meter_id,
                            'dokumen_stok_id' => $dokumenStokPenjualan->dokumen_stok_id
                        ]);
                    }
                    // end of penjualan
                }

                // Stok Taking
                $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
                    'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                    'jam_kerja_id' => $jamKerja[$j],
                    'member_id' => Auth::user()->member_id,
                    'dombak_id' => $dombak->dombak_id,
                    'tanggal' => date('2024-' . $bln .  '-' . ($tgl + $i)),
                    'stok_awal' => $stokAwal,
                    'stok_akhir' => $data[$i][$j]['4'],
                    'is_confirmation' => true
                ]);

                // Update Stok
                $stok_inventory->update([
                    'stok' => $stok_inventory->stok + ($data[$i][$j]['4'] - $stokAwal)
                ]);
                $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->update([
                    'stok' => $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->stok + ($data[$i][$j]['4'] - $stokAwal)
                ]);

                // Buat Relasi Konversi ke Dokumen Stok
                $konversi = $dombak->konversi()->where('volume', $data[$i][$j]['4'])->first();

                if (!is_null($konversi)) {
                    DB::table('konversi_dokumen_stok')->insert([
                        'konversi_id' => $konversi->konversi_id,
                        'dokumen_stok_id' => $dokumenStok->dokumen_stok_id
                    ]);
                }
                // Losses
                if ($stokAwal > $data[$i][$j]['4']) {
                    // loss
                    $dokumenStok->losses()->create([
                        'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                        'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i) . ' H:i:s'),
                        'simbol' => 'kurang',
                        'jumlah' => $stokAwal - $data[$i][$j]['4']
                    ]);
                } else {
                    // Profit
                    $dokumenStok->losses()->create([
                        'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                        'tanggal' => date('2024-' . $bln . '-' . ($tgl + $i) . ' H:i:s'),
                        'simbol' => 'lebih',
                        'jumlah' => ($stokAwal - $data[$i][$j]['4']) * -1
                    ]);
                }
                $stokAwal = $data[$i][$j]['4'];
            }
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
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

    public function temp()
    {
        $temp = [
            [
                3137.5, 3133.0, 3137.5, 3114.2, 3114.2, 3111.5, 3114.2, 3139.4, 3114.2, 3107.0, 3125.0, 3137.5, 3014.8, 2989.2, 2989.0, 2956.0, 2950.4, 2925.0, 2929.6, 2922.8, 2935.8, 2871.6, 2830.0, 2820.4, 2809.0, 2806.0, 2795.2, 2764.2, 2704.4, 2695.0, 2695.0, 2680.7, 2672.0, 2649.8, 2658.2, 2649.0, 2653.6, 2643.2, 2631.8, 2611.6, 2603.0, 2607.6, 2590.0, 2532.2, 2514.7, 2443.0, 2463.0, 2453.6, 2411.4, 2419.6, 2416.0, 2387.6, 2376.2, 2369.0, 2376.2, 2366.8, 2369.0, 2295.8, 2276.0, 2224.3, 2235.0, 2213.0, 2205.5, 2210.2, 2197.8, 2189.2, 2139.2, 2139.6, 2136.0, 2136.0, 2104.6, 2068.2, 1979.4, 1933.4, 1935.8, 1894.8, 1890.8, 1894.4, 1854.2, 1849.0, 1839.5, 1794.5, 1804.4, 1794.5, 1759.8, 1772.0, 1772.0, 1772.0, 1772.0, 1772.0,
            ], [
                3152.0, 3137.5, 3114.2, 3114.2, 3111.5, 3114.2, 3139.4, 3114.2, 3107.0, 3125.0, 3137.5, 3072.8, 3041.2, 2989.0, 2980.0, 2957.4, 2925.0, 2929.6, 2935.8, 2935.8, 2929.6, 2851.0, 2820.4, 2833.0, 2806.0, 2795.2, 2795.2, 2756.4, 2695.0, 2695.0, 2699.7, 2672.0, 2662.8, 2658.2, 2649.0, 2653.6, 2656.2, 2631.8, 2624.6, 2603.0, 2607.6, 2603.0, 2610.2, 2514.7, 2481.0, 2463.0, 2453.6, 2448.4, 2419.6, 2416.0, 2419.6, 2376.2, 2369.0, 2376.2, 2379.8, 2369.0, 2359.8, 2276.0, 2224.3, 2276.0, 2229.0, 2205.5, 2210.2, 2210.8, 2189.2, 2189.2, 2139.6, 2136.0, 2136.0, 2139.6, 2068.2, 2084.4, 1966.4, 1935.8, 1916.8, 1890.8, 1894.4, 1887.2, 1862.0, 1839.5, 1839.5, 1804.4, 1794.5, 1782.8, 1772.0, 1772.0, 1772.0, 1772.0, 1772.0, 1772.0,
            ]
        ];

        return response()->json($this->flip_row_col_array($temp), 200);
    }

    public function uploadDataTotalisator()
    {
        // Totalisator
        $pertaliteN1 = [
            [
                2012, 4232, 2910, 2070, 4051, 3381, 2625, 0, 3317, 1944, 1682, 2755, 3644, 2235, 455, 5513, 2674, 1995, 2716, 2770, 1865, 723, 3772, 2654, 2456, 3172, 2977, 2897, 3005, 602, 602
            ],
            [
                2876, 2465, 1295, 1581, 2389, 3373, 1285, 3462, 4789, 2527, 5294, 4122, 466, 1434, 2617, 1118, 1105, 1639, 2857, 2685, 2095, 1778, 2273, 2657, 2141, 2891, 2292, 3079, 2926, 2482, 2482
            ]
        ];
        $pertaliteN2 = [
            [
                4228, 4954, 4761, 3621, 6248, 5243, 4941, 0, 4692, 3696, 2112, 4709, 5406, 2611, 536, 7318, 5045, 3039, 5008, 5406, 3282, 1708, 5844, 4131, 4735, 5328, 5065, 4881, 5069, 1109, 1109
            ],
            [
                4928, 4592, 2183, 2894, 3346, 3955, 2003, 4546, 5610, 4028, 5787, 4902, 640, 1740, 4412, 2060, 2149, 2997, 4917, 3947, 3483, 2024, 3166, 4577, 3479, 4709, 4554, 4640, 5353, 4590, 4590
            ]
        ];
        $bioSolar = [
            [
                4031, 6965, 5482, 6699, 5545, 6813, 8032, 1396, 737, 0, 7972, 3892, 5127, 3351, 3320, 8017, 0, 1911, 5281, 0, 0, 7722, 2141, 1322, 0, 0, 4314, 1283, 8558, 5645, 5645
            ],
            [
                7608, 7598, 6382, 5782, 6961, 4010, 6646, 7297, 8041, 8036, 68, 4144, 2903, 9405, 8044, 0, 6129, 7896, 2891, 8040, 8493, 5890, 8039, 6728, 8041, 8023, 3706, 10145, 9128, 9872, 9872
            ]
        ];
        $pertamax = [
            [
                247.80, 350.36, 244.24, 345.13, 194.83, 509.20, 226.93, 821.93, 289.92, 205.31, 430.38, 316.92, 371.91, 609.92, 731.46, 388.24, 154.22, 297.57, 140.33, 317.03, 401.95, 662.68, 260.38, 251.08, 137.53, 144.97, 312.55, 130.54, 158.18, 341.81, 341.81
            ],
            [
                145.41, 140.71, 144.75, 122.93, 205.21, 171.87, 136.99, 338.42, 90.28, 126.43, 272.35, 238.90, 405.28, 411.11, 362.66, 179.63, 352.60, 172.99, 117.63, 141.66, 126.73, 368.53, 65.60, 117.71, 246.43, 188.96, 81.87, 67.42, 43.41, 180.29, 180.29
            ]
        ];
        $pertadex = [
            [
                0, 0, 0, 0, 58, 24, 0, 58, 24, 31, 0, 13, 0, 13, 13, 38, 37, 32, 0, 64, 41, 0, 50, 0, 105, 22, 33, 45, 23, 0, 0
            ],
            [
                19, 0, 0, 0, 52, 7, 13, 21, 0, 52, 19, 0, 13, 0, 78, 0, 0, 0, 13, 0, 16, 13, 0, 35, 33, 0, 13, 0, 0, 0, 0
            ]
        ];
        $dexlite = [
            [],
            []
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
        $standMeterLast = 14713947;
        for ($i = 0; $i < count($bioSolarFlip); $i++) {
            for ($j = 0; $j < count($bioSolarFlip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $nozzleBs1->stand_meter()->create([
                    'dombak_id' => $dombakBs1->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-05-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $bioSolarFlip[$i][$j],
                    'is_confirmation' => false,
                    'urutan' => $jk->urutan
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
        $standMeterLast = 8255847;
        for ($i = 0; $i < count($pertaliteN1Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN1Flip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-05-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertaliteN1Flip[$i][$j],
                    'is_confirmation' => false,
                    'urutan' => $jk->urutan
                ]);

                $standMeterLast += $pertaliteN1Flip[$i][$j];
            }
        }

        $tgl = 1;
        $pertaliteN2Flip = $this->flip_row_col_array($pertaliteN2);
        $standMeterLast = 9260559;
        for ($i = 0; $i < count($pertaliteN2Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN2Flip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $nozzleN2->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-05-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertaliteN2Flip[$i][$j],
                    'is_confirmation' => false,
                    'urutan' => $jk->urutan
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
        $standMeterLast = 91342.38;
        for ($i = 0; $i < count($pertamaxN1); $i++) {
            for ($j = 0; $j < count($pertamaxN1[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-05-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertamaxN1[$i][$j],
                    'is_confirmation' => false,
                    'urutan' => $jk->urutan
                ]);

                $standMeterLast += $pertamaxN1[$i][$j];
            }
        }


        // Pertamina Dex
        $dombakPl = Dombak::where('nama_dombak', 'DM6-PDX')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-3B PDX')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];

        $tgl = 1;
        $pertaminaDex = $this->flip_row_col_array($pertadex);
        $standMeterLast = 137744;
        for ($i = 0; $i < count($pertaminaDex); $i++) {
            for ($j = 0; $j < count($pertaminaDex[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => '2024-05-' . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLast,
                    'stand_meter_akhir' => $standMeterLast + $pertaminaDex[$i][$j],
                    'is_confirmation' => false,
                    'urutan' => $jk->urutan
                ]);

                $standMeterLast += $pertaminaDex[$i][$j];
            }
        }

        // Dexlite (Tidak di gunakan)
        // $jadwalKerja = [
        //     'd61ddc4b-f97c-4361-935a-26718af856d7',
        //     '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
        //     'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        // ];
        // $dombakDx = Dombak::where('nama_dombak', 'DM3-DX')->first();
        // $nozzleDx = Nozzle::where('nama_nozzle', 'N-1A DLX')->first();
        // $tgl = 1;
        // $dexlite = $this->flip_row_col_array($dexlite);
        // $standMeterLast = 4732763;
        // for ($i = 0; $i < count($dexlite); $i++) {
        //     for ($j = 0; $j < count($dexlite[$i]); $j++) {
        //         $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
        //         $nozzleDx->stand_meter()->create([
        //             'dombak_id' => $dombakDx->dombak_id,
        //             'jam_kerja_id' => $jadwalKerja[$j],
        //             'tanggal' => '2024-05-' . ($tgl + $i),
        //             'stand_meter_awal' => $standMeterLast,
        //             'stand_meter_akhir' => $standMeterLast + $dexlite[$i][$j],
        //             'is_confirmation' => false,
        //              'urutan' => $jk->urutan
        //         ]);

        //         $standMeterLast += $dexlite[$i][$j];
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
