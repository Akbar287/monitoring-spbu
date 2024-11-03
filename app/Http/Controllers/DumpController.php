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

    public function tambal() {
        // tgl 22 july2024 || N-1A PL
        $data = [
            [7.370,  1800.900,  1608.670,  1700.070,  1016.280,  1813.420,  1050.670,  448.030,  224.540,  1589.080 ],
            [1083.080,  1341.740,  1482.330,  1608.980,  1011.830,  1876.040,  1619.050,  1691.810,  1757.850, 1722.910],
            [0,         0,          0,          0,          0,          0,      0,          0,          0,      0]
        ];

        $dombak = Dombak::where('nama_dombak', 'DM3-PL')->first();
        $nozzle = Nozzle::where('nama_nozzle', 'N-1A PL')->get();

        $standMeterLastPertaliteN3 =  69.590;
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92'
        ];
        $tglTemplate = '2024-07-';
        $tgl = 22;
        $typeDokumenStok = TypeDokumenStok::where('type_movement', 'Good Issue Sales')->first();

        $dataFlip = $this->flip_row_col_array($data);
        $si = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id;
        for ($i = 0; $i < count($dataFlip); $i++) {
            for ($j = 0; $j < count($dataFlip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $standmeter = $nozzle->stand_meter()->create([
                    'dombak_id' => $dombak->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastPertaliteN3,
                    'stand_meter_akhir' => $standMeterLastPertaliteN3 + $dataFlip[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standmeter->dokumen_stok()->create([
                    'stok_inventory_id' => $si,
                    'member_id' => Auth::user()->member()->first()->member_id,
                    'dombak_id' => $dombak->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stok_awal' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                    'stok_akhir' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertaliteN3 + $dataFlip[$i][$j]),
                    'is_confirmation' => 1,
                ]);

                $standMeterLastPertaliteN3 += $dataFlip[$i][$j];
            }
        }

        return response()->json([
            'data' => [],
            'message' => 'ok',
            'status' => 'success'
        ], 200);exit;
    }

    // Totalisator Lama
    public function uploadDataTotalisator()
    {
        // Totalisator
        $pertaliteN1 = [
            [
                2597.729, 0.000, 457.559, 2589.330, 0.000, 3449.238, 0.000, 1896.130, 1908.439, 2086.697, 0.000, 4700.312, 0.000, 0.000, 1571.584, 627.376, 0.000, 0.000, 1820.540, 0.000, 458.759, 0.000, 0.000, 0.000, 693.241, 884.014, 0.000, 0.000, 1324.795, 2177.371, 1743.050
            ],
            [
                3279.904, 3825.126, 4157.809, 2178.519, 0.000, 4058.408, 4163.080, 4887.379, 4639.225, 4717.994, 1754.454, 3887.342, 3856.328, 4855.017, 4728.222, 5024.576, 4549.444, 4609.858, 4360.405, 4633.826, 4956.368, 4864.564, 5021.250, 2453.176, 4475.343, 4715.925, 4111.073, 3065.579, 4127.959, 1999.042, 1776.110
            ],
            // [
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000,
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            // ]
        ];
        $pertaliteN2 = [
            [
                2487.797, 0.000, 366.237, 2120.912, 0.000, 3492.886, 0.000, 1807.416, 1799.160, 1660.643, 0.000, 4904.711, 0.000, 0.000, 1444.501, 595.149, 0.000, 0.000, 2052.380, 0.000, 544.028, 0.000, 0.000, 0.000, 811.206, 891.164, 0.000, 0.000, 1826.539, 2422.487, 1875.101
            ],
            [
                2516.065, 4066.865, 3710.018, 1466.977, 0.000, 3654.706, 4036.035, 4424.054, 4340.662, 4087.893, 1471.491, 3445.808, 3148.571, 5409.689, 4575.384, 5176.568, 3887.745, 4512.407, 4721.481, 4580.893, 5311.816, 4712.726, 5600.551, 2705.971, 3877.207, 5524.691, 4493.212, 3587.275, 4221.718, 2404.141, 2174.223
            ],
            // [
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000,
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            // ]
        ];
        $pertaliteN3 = [
            [
                1604.070, 0.000, 296.140, 1247.290, 0.090, 2060.520, 0.000, 1098.060, 1160.590, 1097.270, 0.000, 2599.210, 0.000, 0.000, 772.950, 420.630, 0.000, 0.000, 1096.700, 0.000, 281.064, 0.000, 0.000, 0.000, 487.350, 521.360, 0.000, 0.000, 1381.390, 1867.010, 1892.320
            ],
            [
                1876.480, 1736.860, 2281.420, 1072.710, 0.000, 2346.970, 2232.390, 2421.120, 1923.410, 2104.420, 873.950, 1760.620, 1678.200, 2759.110, 1956.240, 2577.210, 2113.360, 1964.840, 2427.090, 2278.170, 2587.976, 2439.640, 2542.790, 1610.210, 2335.460, 2809.640, 2445.160, 1829.560, 2173.070, 2216.600, 1783.680
            ],
            // [
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000,
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            // ]
        ];
        $bioSolar = [
            [
                2718.980, 7349.598, 4897.849, 0.000, 5100.481, 5157.133, 2704.154, 2249.161, 2650.572, 2538.547, 4445.818, 7673.914, 6930.402, 4493.310, 3343.999, 5942.773, 5662.598, 5020.814, 7381.008, 7312.352, 6138.438, 5577.495, 0.000, 2384.374, 2372.759, 2340.585, 2232.147, 5942.539, 8496.670, 2905.251, 3268.555
            ],
            [
                4719.807, 0.000, 3914.137, 4046.294, 2912.416, 5310.716, 5802.559, 5380.170, 5498.528, 3586.985, 0.000, 6500.764, 7163.112, 3975.323, 0.000, 0.000, 2519.089, 3475.748, 2137.707, 5997.533, 7561.865, 7862.631, 5857.322, 5684.027, 5701.931, 5806.930, 7738.561, 0.000, 7103.632, 4766.626, 4376.598
            ],
            // [
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000,
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            // ]
        ];
        $pertamax = [
            [
                680.870, 839.430, 1040.360, 729.640, 1544.210, 666.080, 1356.200, 788.420, 773.950, 1029.010, 1548.730, 354.310, 1372.100, 1580.530, 1126.260, 1141.590, 1564.980, 1670.200, 1179.340, 1615.020, 1105.690, 1297.800, 1007.860, 1100.090, 1087.990, 1293.370, 1174.540, 955.530, 569.240, 302.480, 494.950
            ],
            [
                198.370, 138.830, 207.630, 467.070, 1399.970, 219.750, 115.350, 265.280, 246.630, 344.100, 1029.520, 220.010, 232.820, 274.280, 262.820, 255.570, 290.430, 398.020, 170.120, 182.130, 233.370, 224.750, 226.260, 449.640, 205.880, 252.020, 239.490, 195.430, 177.150, 463.980, 402.450
            ],
            // [
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000,
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            // ]
        ];
        $pertadex = [
            [
                0.000, 29.874, 0.000, 9.375, 31.250, 0.625, 87.507, 0.000, 6.250, 79.317, 10.707, 14.881, 0.000, 24.958, 6.250, 25.042, 0.000, 20.639, 86.259, 6.250, 6.250, 10.000, 0.000, 12.555, 26.567, 12.500, 12.500, 19.062, 0.000, 21.875, 12.500
            ],
            [
                0.000, 18.813, 0.000, 31.293, 25.000, 12.500, 13.125, 0.000, 28.182, 0.000, 0.000, 68.753, 18.750, 18.797, 18.750, 6.250, 0.000, 14.379, 50.060, 0.000, 15.625, 0.000, 62.500, 12.500, 0.000, 14.104, 9.375, 0.000, 34.375, 19.062, 63.996
            ],
            // [
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000,
            //     0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            // ]
        ];
        $dexlite = [
            [],
            [],
            []
        ];

        $standMeterLastBiosolar = 15477969.328;
        $standMeterLastPertamax = 151744.86;
        // $standMeterLastDexlite = 69.59;
        $standMeterLastPertaliteN1 = 8715400.907;
        $standMeterLastPertaliteN2 = 9941239.212;
        $standMeterLastPertaliteN3 = 26524.24;
        $standMeterLastPertadex = 141527.985;

        $tglTemplate = '2024-08-';
        $typeDokumenStok = TypeDokumenStok::where('type_movement', 'Good Issue Sales')->first();


        // BioSolar
        $dombakBs1 = Dombak::where('nama_dombak', 'DM4-BS')->first();
        // $dombakBs2 = Dombak::where('nama_dombak', 'DM5-BS')->first();

        // Nozzle
        $nozzleBs1 = Nozzle::where('nama_nozzle', 'N-3A BS')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3'
        ];
        $tgl = 1;

        // Biosolar
        $bioSolarFlip = $this->flip_row_col_array($bioSolar);
        $si = $dombakBs1->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id;
        for ($i = 0; $i < count($bioSolarFlip); $i++) {
            for ($j = 0; $j < count($bioSolarFlip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $standmeter = $nozzleBs1->stand_meter()->create([
                    'dombak_id' => $dombakBs1->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastBiosolar,
                    'stand_meter_akhir' => $standMeterLastBiosolar + $bioSolarFlip[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standmeter->dokumen_stok()->create([
                    'stok_inventory_id' => $si,
                    'member_id' => Auth::user()->member()->first()->member_id,
                    'dombak_id' => $dombakBs1->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stok_awal' => $dombakBs1->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                    'stok_akhir' => $dombakBs1->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastBiosolar + $bioSolarFlip[$i][$j]),
                    'is_confirmation' => 1,
                ]);

                $standMeterLastBiosolar += $bioSolarFlip[$i][$j];
            }
        }

        // Pertalite
        $dombakPl = Dombak::where('nama_dombak', 'DM2-PL')->first();

        $dombakPl2 = Dombak::where('nama_dombak', 'DM3-PL')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-2A PL')->first();
        $nozzleN2 = Nozzle::where('nama_nozzle', 'N-2B PL')->first();
        $nozzleN3 = Nozzle::where('nama_nozzle', 'N-1A PL')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3'
        ];

        $tgl = 1;
        $pertaliteN1Flip = $this->flip_row_col_array($pertaliteN1);
        for ($i = 0; $i < count($pertaliteN1Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN1Flip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $standmeter = $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastPertaliteN1,
                    'stand_meter_akhir' => $standMeterLastPertaliteN1 + $pertaliteN1Flip[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standmeter->dokumen_stok()->create([
                    'stok_inventory_id' => $si,
                    'member_id' => Auth::user()->member()->first()->member_id,
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                    'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertaliteN1 + $bioSolarFlip[$i][$j]),
                    'is_confirmation' => 1,
                ]);

                $standMeterLastPertaliteN1 += $pertaliteN1Flip[$i][$j];
            }
        }

        $tgl = 1;
        $pertaliteN2Flip = $this->flip_row_col_array($pertaliteN2);
        for ($i = 0; $i < count($pertaliteN2Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN2Flip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $standmeter = $nozzleN2->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastPertaliteN2,
                    'stand_meter_akhir' => $standMeterLastPertaliteN2 + $pertaliteN2Flip[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standmeter->dokumen_stok()->create([
                    'stok_inventory_id' => $si,
                    'member_id' => Auth::user()->member()->first()->member_id,
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                    'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertaliteN2 + $bioSolarFlip[$i][$j]),
                    'is_confirmation' => 1,
                ]);

                $standMeterLastPertaliteN2 += $pertaliteN2Flip[$i][$j];
            }
        }

        $tgl = 1;
        $pertaliteN3Flip = $this->flip_row_col_array($pertaliteN3);
        for ($i = 0; $i < count($pertaliteN3Flip); $i++) {
            for ($j = 0; $j < count($pertaliteN3Flip[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $nozzleN3->stand_meter()->create([
                    'dombak_id' => $dombakPl2->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastPertaliteN3,
                    'stand_meter_akhir' => $standMeterLastPertaliteN3 + $pertaliteN3Flip[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standMeterLastPertaliteN3 += $pertaliteN3Flip[$i][$j];
            }
        }

        // Pertamax
        $dombakPl = Dombak::where('nama_dombak', 'DM1-PX')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-1B PX')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3'
        ];

        $tgl = 1;
        $pertamaxN1 = $this->flip_row_col_array($pertamax);
        for ($i = 0; $i < count($pertamaxN1); $i++) {
            for ($j = 0; $j < count($pertamaxN1[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $standmeter = $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastPertamax,
                    'stand_meter_akhir' => $standMeterLastPertamax + $pertamaxN1[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standmeter->dokumen_stok()->create([
                    'stok_inventory_id' => $si,
                    'member_id' => Auth::user()->member()->first()->member_id,
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                    'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertamax + $bioSolarFlip[$i][$j]),
                    'is_confirmation' => 1,
                ]);

                $standMeterLastPertamax += $pertamaxN1[$i][$j];
            }
        }


        // Pertamina Dex
        $dombakPl = Dombak::where('nama_dombak', 'DM6-PDX')->first();

        // Nozzle
        $nozzleN1 = Nozzle::where('nama_nozzle', 'N-3B PDX')->first();

        // Jadwal Kerja
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3'
        ];

        $tgl = 1;
        $pertaminaDex = $this->flip_row_col_array($pertadex);
        for ($i = 0; $i < count($pertaminaDex); $i++) {
            for ($j = 0; $j < count($pertaminaDex[$i]); $j++) {
                $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();
                $standmeter = $nozzleN1->stand_meter()->create([
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stand_meter_awal' => $standMeterLastPertadex,
                    'stand_meter_akhir' => $standMeterLastPertadex + $pertaminaDex[$i][$j],
                    'is_confirmation' => 1,
                    'urutan' => $jk->urutan
                ]);

                $standmeter->dokumen_stok()->create([
                    'stok_inventory_id' => $si,
                    'member_id' => Auth::user()->member()->first()->member_id,
                    'dombak_id' => $dombakPl->dombak_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                    'tanggal' => $tglTemplate . ($tgl + $i),
                    'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                    'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertadex + $bioSolarFlip[$i][$j]),
                    'is_confirmation' => 1,
                ]);

                $standMeterLastPertadex += $pertaminaDex[$i][$j];
            }
        }

        // Dexlite (Tidak di gunakan)
        // $jadwalKerja = [
        //     'd61ddc4b-f97c-4361-935a-26718af856d7',
        //     '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
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
        //             'urutan' => $jk->urutan
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

    // Totalisator Baru
    public function uploadDataTotalisatorBaru()
    {
        // Constant
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92'
        ];
        $typeDokumenStok = TypeDokumenStok::where('type_movement', 'Good Issue Sales')->first();

        // End Constants

        // Edit
        $tglTemplate = '2024-10-';
        // Totalisator
        // Pertalite
        $pertaliteN1A = [
            [
                0.000, 0.000, 0.000, 0.000, 0.000, 6.200, 560.630, 1500.310, 0.000, 0.000, 213.460, 768.950, 0.000, 813.690, 743.160, 0.000, 0.000, 0.000, 45.750, 0.000, 134.090, 76.650, 0.000, 0.000, 0.000, 0.000, 742.180, 1002.700, 0.000, 56.920, 80.220
            ],
            [
                1281.210, 1432.010, 1519.530, 1904.300, 1576.810, 878.260, 47.040, 1322.470, 1099.360, 1117.220, 1268.910, 1444.340, 865.190, 1340.500, 1026.310, 314.480, 30.670, 1376.060, 721.690, 1144.400, 1188.970, 714.940, 816.460, 1448.950, 993.630, 927.410, 742.250, 640.120, 830.480, 1255.300, 913.190
            ],
            [
                0.000, 0.000, 402.390, 0.000, 0.000, 0.000, 0.000, 112.250, 0.000, 0.000, 162.780, 0.000, 0.000, 0.000, 449.350, 0.000, 0.000, 313.900, 113.000, 82.500, 601.960, 707.420, 195.460, 26.170, 513.200, 0.000, 6.220, 51.060, 62.110, 564.840, 569.020
            ]
        ];
        $pertaliteN1C = [
            [
                0.000, 0.000, 0.000, 33.930, 15.000, 0.000, 404.640, 151.240, 0.000, 0.000, 134.260, 1716.270, 0.000, 35.500, 203.160, 50.260, 0.000, 0.000, 94.540, 0.000, 344.840, 328.280, 0.000, 16.050, 0.000, 0.000, 389.560, 515.180, 0.000, 5.600, 247.500
            ],
            [
                544.220, 1431.850, 754.880, 669.210, 642.520, 255.960, 451.560, 848.350, 773.060, 501.890, 695.010, 15.680, 664.380, 1145.630, 884.620, 430.780, 1022.100, 728.560, 875.950, 399.850, 547.210, 724.640, 1012.320, 354.790, 448.870, 454.410, 543.920, 827.540, 740.710, 774.930, 610.100
            ],
            [
                0.000, 0.000, 1487.780, 0.000, 0.000, 0.000, 0.000, 1165.210, 876.780, 0.000, 1164.300, 0.000, 0.000, 0.000, 1236.970, 0.000, 0.000, 1648.320, 318.570, 523.740, 895.110, 2254.560, 189.520, 435.990, 1779.440, 0.000, 600.840, 345.000, 285.200, 2341.340, 1681.610
            ]
        ];
        $pertaliteN2A = [
            [
                0, 0, 0, 0, 0, 0, 1549.73, 2530.87, 0, 0, 511.33, 0, 0, 1357.22, 1520.77, 157.48, 0, 0, 269.17, 0, 820.17, 359.73, 0, 0, 0, 0, 940.66, 1349.05, 0, 808.43, 6.93
            ],
            [
                3104.60, 1946.92, 3354.01, 4513.07, 3765.11, 2032.96, 2803.48, 3330.47, 3221.44, 2315.79, 3629.81, 3600.36, 2249.94, 4106.61, 3578.24, 3725.61, 1796.38, 4220.63, 3160.47, 2544.67, 3727.36, 2646.03, 3115.75, 3338.34, 3302.37, 1770.72, 1989.23, 1889.72, 2838.97, 3130.50, 1972.01
            ],
            [
                626.98, 588.9, 0, 629.95, 768.8, 876.41, 506.52, 562.2, 0, 0, 354.98, 781.5, 1202.26, 0, 632.83, 1012.66, 786.6, 393.21, 539.54, 0, 479.61, 0, 1046.71, 388.42, 0, 761.14, 283.36, 1253.04, 419.36, 0, 0
            ]
        ];
        $pertaliteN2B = [
            [
                0, 0, 0, 0, 0, 0, 1866.81, 3375.69, 0, 0, 601.53, 0, 0, 1530.36, 1667.20, 147.5, 0, 0, 342.99, 0, 963.55, 444, 0, 0, 0, 0, 2337.27, 2337.13, 0, 1233.70, 15
            ],
            [
                4123.87, 2489.28, 3630.98, 4883.15, 4805.63, 2436.64, 3762.40, 4018.95, 3475.26, 2762.63, 4449.09, 4433.17, 2631.59, 4436.03, 4975.48, 4429.66, 1696.25, 4573.05, 3862.40, 3525.42, 4262.66, 3664.36, 3912.39, 3817.34, 4432.85, 2522.29, 2620.17, 2802.12, 3349.05, 2840.20, 2683.84
            ],
            [
                1558.49, 1774.55, 0, 1965.48, 1868.79, 1988.71, 1800.98, 1290.06, 0, 0, 1412.12, 1795.55, 2483.08, 0, 1228.16, 1607.05, 1371.50, 660.98, 832.85, 0, 1460.97, 0, 1769.38, 1605.92, 5, 1582.46, 1267.40, 1691.32, 988.79, 0, 0
            ]
        ];

        // Pertamax
        $pertamaxN1B = [
            [
                850.55, 731.59, 953.18, 905.71, 763.67, 928.53, 956.24, 0, 970.37, 906.01, 1138.69, 0, 1042.25, 864.67, 853.43, 1216.81, 13.32, 746.47, 764.89, 767.14, 714.13, 909.06, 756.37, 807.36, 414.69, 621.29, 151.68, 305.69, 735.06, 583.84, 581.48
            ],
            [
                92.31, 141.86, 2.82, 218.62, 184.27, 615.62, 4.74, 190.21, 419.60, 618.93, 0.00, 153.94, 77.80, 245.21, 125.65, 56.53, 586.03, 103.850, 236.10, 114.21, 169.07, 111.48, 54.17, 243.50, 222.90, 146.41, 155.04, 115.79, 252.37, 126.92, 224.51
            ],
            [
                0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 7.250, 204.970, 239.720, 0.000, 0.000, 27.730, 183.630, 48.480, 2.330, 143.710, 3.15, 7.250, 0.000, 81.350, 2.430, 33.860, 6.610, 3.220, 0.000, 1.770, 8.060, 0.000, 19.730, 12.490
            ]
        ];
        $pertamaxN1BD = [
            [
                765.16, 686.31, 771.99, 741.45, 686.16, 801.83, 677.96, 0, 542.97, 750.16, 1035.63, 0, 678.3, 807.35, 526.55, 232.04, 1686.31, 854.58, 624.97, 864.05, 646.79, 702.84, 651.04, 599.07, 602.68, 592.38, 156.99, 116.93, 639.19, 296.62, 594.77
            ],
            [
                73.77, 103.56, 0, 46.78, 38.65, 430.71, 4.07, 84.42, 323.52, 389.06, 0, 80.18, 89.73, 134.100, 50.74, 411.03, 315.24, 88.96, 56.95, 117, 137.46, 69.17, 88.83, 120.78, 68.48, 61, 83.61, 148.92, 96.74, 155.81, 32.28
            ],
            [
                0.00, 0.00, 0.00, 16.13, 0.00, 46.30, 0.00, 4.03, 84.55, 125.78, 0.00, 0.00, 0.00, 45.37, 8.21, 188.45, 2.98, 63.23, 0.00, 0.00, 41.93, 0.00, 48.38, 4.03, 0.00, 1.37, 3.22, 10.71, 0.00, 0.00, 2.01
            ]
        ];

        // BioSolar
        $bioSolarN3A = [
            [
                2271.580, 3698.220, 4370.380, 4464.180, 4106.120, 4435.800, 4261.660, 4549.780, 3970.760, 4361.690, 3296.210, 4503.590, 5269.580, 2610.310, 2850.590, 4619.460, 4737.670, 3861.500, 2107.080, 2218.110, 3082.750, 3543.340, 4233.890, 4043.850, 4051.880, 3552.430, 3322.650, 3567.610, 3004.440, 4063.850, 2614.600
            ],
            [
                54.430, 0.000, 1918.900, 0.000, 0.000, 0.000, 0.000, 41.170, 77.210, 0.000, 1053.530, 1107.040, 663.840, 913.620, 552.530, 0.000, 1295.700, 1239.460, 0.000, 2883.210, 14.780, 220.590, 1811.720, 0.000, 829.020, 0.000, 0.000, 1641.380, 429.390, 35.890, 2863.230
            ],
            [
                0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 740.470, 0.000, 0.000, 1502.060, 0.000, 0.000, 0.000, 0.000, 0.000, 0.00, 847.84, 0.00, 0.00, 0.00, 0.00, 80.87, 0.00, 0.00
            ]
        ];
        $bioSolarN3C = [
            [
                2645.030, 4514.370, 4215.460, 5082.900, 4771.800, 4236.010, 4336.940, 4433.370, 4314.600, 4939.570, 3834.090, 4944.460, 4818.070, 3498.330, 3611.490, 4351.730, 4365.720, 4223.780, 2803.180, 2225.840, 3400.030, 3756.460, 4880.100, 5129.450, 4409.170, 3616.620, 3416.920, 3851.800, 3702.420, 2863.840, 3327.630
            ],
            [
                60.000, 38.980, 1505.920, 0.000, 20.000, 0.000, 26.470, 69.110, 41.170, 41.170, 1257.860, 934.670, 1148.720, 1027.660, 884.690, 12.500, 1850.360, 1224.750, 0.000, 2360.590, 112.600, 0.000, 1671.920, 0.000, 910.270, 0.000, 0.000, 1808.360, 0.000, 121.370, 3109.750
            ],
            [
                0.000, 44.110, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 1089.790, 0.000, 0.000, 1723.690, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 1003.270, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000
            ]
        ];

        // PDX
        $pertadexN3B = [
            [
                45.590, 0.000, 11.150, 45.990, 162.740, 151.100, 146.910, 173.380, 162.670, 217.730, 134.650, 247.240, 128.040, 173.990, 156.240, 249.730, 253.240, 214.940, 109.160, 115.260, 190.440, 190.340, 227.400, 284.310, 228.660, 223.810, 197.070, 193.320, 200.060, 206.380, 179.200
            ],
            [
                66.880, 0.000, 0.000, 13.000, 37.170, 46.820, 79.550, 66.400, 32.360, 52.020, 54.120, 111.400, 48.920, 71.220, 79.080, 116.560, 90.210, 218.650, 68.770, 164.670, 51.620, 24.160, 152.550, 51.920, 102.870, 52.020, 27.500, 85.320, 24.520, 44.600, 132.050
            ],
            [
                0.000, 14.860, 0.000, 0.000, 26.770, 0.000, 0.000, 0.000, 0.000, 0.000, 0.000, 11.440, 0.000, 40.050, 0.000, 0.000, 81.650, 28.260, 0.000, 0.000, 63.180, 7.430, 55.750, 58.650, 69.510, 0.000, 0.000, 0.000, 0.000, 22.300, 0.000
            ]
        ];
        $pertadexN3D = [
            [
                0.740, 0.000, 0.000, 14.860, 162.060, 166.850, 148.740, 153.040, 140.530, 181.930, 117.190, 208.650, 152.730, 161.190, 207.760, 243.860, 282.540, 222.540, 165.730, 116.280, 164.810, 229.990, 250.870, 277.580, 213.710, 178.080, 168.830, 249.570, 192.340, 135.070, 165.510
            ],
            [
                0.000, 75.430, 13.030, 0.000, 0.000, 0.000, 0.000, 2.230, 28.230, 1.480, 171.820, 59.780, 51.260, 81.330, 37.110, 27.260, 128.420, 59.470, 7.430, 202.360, 13.370, 59.830, 120.950, 0.000, 57.190, 0.000, 7.430, 92.470, 22.300, 35.790, 241.770
            ],
            [
                0.000, 0.000, 0.000, 0.000, 0.000, 56.130, 0.000, 0.000, 0.000, 7.430, 3.710, 0.000, 0.000, 57.510, 0.000, 0.000, 96.880, 0.000, 0.000, 0.000, 11.150, 0.000, 0.000, 51.500, 0.000, 0.000, 0.000, 7.430, 18.580, 0.000, 0.000
            ]
        ];

        $temp = [
            'pertalite' => [
                'dombak' => ['0e3b3f9c-92fe-452f-9c7e-8d663217315e', '0e3b3f9c-92fe-452f-9c7e-8d663217315e', '8fd5fafd-5add-4688-9b0a-76a8f6e237c4', '8fd5fafd-5add-4688-9b0a-76a8f6e237c4'],
                'nozzle' => ['089d4111-3b46-40f3-80a0-5c5bf9fb8cbe', '4fb9cbb8-1be5-48ce-8eda-699f33b81a2d', '38939114-836e-40ee-b2c7-480edbc3c9ca', '7ab82d3a-af3a-43ae-81a9-6e31108f0372'],
                'stand_meter' => [185286.990000, 23997.650000, 8990350.200000, 10254337.620000],
                'dokumen_stok' => [$pertaliteN1A, $pertaliteN1C, $pertaliteN2A, $pertaliteN2B],
            ],
            'pertamax' => [
                'dombak' => ['7ba4e9f2-0e71-402f-8199-4d21f20eea48', '7ba4e9f2-0e71-402f-8199-4d21f20eea48'],
                'nozzle' => ['6b5b4045-728b-46d2-acd8-20f9d88e9ef0', '6e1b08b6-c1c6-4234-adea-028237e4a30c'],
                'stand_meter' => [217500.170000, 6894.690000],
                'dokumen_stok' => [$pertamaxN1B, $pertamaxN1BD],
            ],
            'bio_solar' => [
                'dombak' => ['67f1255b-3b89-4164-bd0e-ea9279a1b640', '67f1255b-3b89-4164-bd0e-ea9279a1b640'],
                'nozzle' => ['d4c91636-623e-4763-bd78-0b5262092b72', '720f0ed6-1a1f-4577-a1d0-b3b31f8ad65e'],
                'stand_meter' => [15941557.110000, 76895.130000],
                'dokumen_stok' => [$bioSolarN3A, $bioSolarN3C],
            ],
            'pdx' => [
                'dombak' => ['6affaa45-f173-4605-8a84-08cee72fdf98', '6affaa45-f173-4605-8a84-08cee72fdf98'],
                'nozzle' => ['cb33c8eb-f257-4b21-9ef1-dda345f54404', 'c01d0b2b-f701-4055-b245-cda60a2a80d2'],
                'stand_meter' => [144132.810000, 518.630000],
                'dokumen_stok' => [$pertadexN3B, $pertadexN3D],
            ],
        ];

        // return response()->json($temp, 200);exit;

        // Pertalite
        // dd($temp['pertalite']['dombak']);
        for($k = 0; $k < count($temp['pertalite']['dombak']); $k++) { // jml (nozzle)
            $dombakPl = Dombak::where('dombak_id', $temp['pertalite']['dombak'][$k])->first();
            $nozzlePl = Nozzle::where('nozzle_id', $temp['pertalite']['nozzle'][$k])->first();
            $standMeterLastPertalite = $temp['pertalite']['stand_meter'][$k];
            $pertalite = $temp['pertalite']['dokumen_stok'][$k];

            $si = $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id;
            $tgl = 1;
            $pertaliteFlip = $this->flip_row_col_array($pertalite);

            for ($i = 0; $i < count($pertaliteFlip); $i++) { //30 Loop (Hari)
                for ($j = 0; $j < count($pertaliteFlip[$i]); $j++) { //3 Loop (Shift)
                    $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();

                    $standmeter = $nozzlePl->stand_meter()->create([
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stand_meter_awal' => $standMeterLastPertalite,
                        'stand_meter_akhir' => $standMeterLastPertalite + $pertaliteFlip[$i][$j],
                        'is_confirmation' => 1,
                        'urutan' => $jk->urutan
                    ]);

                    $standmeter->dokumen_stok()->create([
                        'stok_inventory_id' => $si,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                        'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertalite + $pertaliteFlip[$i][$j]),
                        'is_confirmation' => 1,
                    ]);

                    $standMeterLastPertalite += $pertaliteFlip[$i][$j];
                }
            }
        }

        // Pertamax
        for($k=0;$k < count($temp['pertamax']['dombak']); $k++) {
            $dombakPl = Dombak::where('dombak_id', $temp['pertamax']['dombak'][$k])->first();
            $nozzlePl = Nozzle::where('nozzle_id', $temp['pertamax']['nozzle'][$k])->first();
            $standMeterLastPertalite = $temp['pertamax']['stand_meter'][$k];
            $pertalite = $temp['pertamax']['dokumen_stok'][$k];

            $si = $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id;
            $tgl = 1;
            $pertaliteFlip = $this->flip_row_col_array($pertalite);
            for ($i = 0; $i < count($pertaliteFlip); $i++) {
                for ($j = 0; $j < count($pertaliteFlip[$i]); $j++) {
                    $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();

                    $standmeter = $nozzlePl->stand_meter()->create([
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stand_meter_awal' => $standMeterLastPertalite,
                        'stand_meter_akhir' => $standMeterLastPertalite + $pertaliteFlip[$i][$j],
                        'is_confirmation' => 1,
                        'urutan' => $jk->urutan
                    ]);

                    $standmeter->dokumen_stok()->create([
                        'stok_inventory_id' => $si,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                        'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertalite + $pertaliteFlip[$i][$j]),
                        'is_confirmation' => 1,
                    ]);

                    $standMeterLastPertalite += $pertaliteFlip[$i][$j];
                }
            }
        }

        // Bio Solar
        for($k=0;$k < count($temp['bio_solar']['dombak']); $k++) {
            $dombakPl = Dombak::where('dombak_id', $temp['bio_solar']['dombak'][$k])->first();
            $nozzlePl = Nozzle::where('nozzle_id', $temp['bio_solar']['nozzle'][$k])->first();
            $standMeterLastPertalite = $temp['bio_solar']['stand_meter'][$k];
            $pertalite = $temp['bio_solar']['dokumen_stok'][$k];

            $si = $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id;
            $tgl = 1;
            $pertaliteFlip = $this->flip_row_col_array($pertalite);
            for ($i = 0; $i < count($pertaliteFlip); $i++) {
                for ($j = 0; $j < count($pertaliteFlip[$i]); $j++) {
                    $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();

                    $standmeter = $nozzlePl->stand_meter()->create([
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stand_meter_awal' => $standMeterLastPertalite,
                        'stand_meter_akhir' => $standMeterLastPertalite + $pertaliteFlip[$i][$j],
                        'is_confirmation' => 1,
                        'urutan' => $jk->urutan
                    ]);

                    $standmeter->dokumen_stok()->create([
                        'stok_inventory_id' => $si,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                        'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertalite + $pertaliteFlip[$i][$j]),
                        'is_confirmation' => 1,
                    ]);

                    $standMeterLastPertalite += $pertaliteFlip[$i][$j];
                }
            }
        }

        // PDX
        for($k=0; $k < count($temp['pdx']['dombak']); $k++) {
            $dombakPl = Dombak::where('dombak_id', $temp['pdx']['dombak'][$k])->first();
            $nozzlePl = Nozzle::where('nozzle_id', $temp['pdx']['nozzle'][$k])->first();
            $standMeterLastPertalite = $temp['pdx']['stand_meter'][$k];
            $pertalite = $temp['pdx']['dokumen_stok'][$k];

            $si = $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id;
            $tgl = 1;
            $pertaliteFlip = $this->flip_row_col_array($pertalite);
            for ($i = 0; $i < count($pertaliteFlip); $i++) {
                for ($j = 0; $j < count($pertaliteFlip[$i]); $j++) {
                    $jk = JamKerja::where('jam_kerja_id', $jadwalKerja[$j])->first();

                    $standmeter = $nozzlePl->stand_meter()->create([
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stand_meter_awal' => $standMeterLastPertalite,
                        'stand_meter_akhir' => $standMeterLastPertalite + $pertaliteFlip[$i][$j],
                        'is_confirmation' => 1,
                        'urutan' => $jk->urutan
                    ]);

                    $standmeter->dokumen_stok()->create([
                        'stok_inventory_id' => $si,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'dombak_id' => $dombakPl->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'type_dokumen_stok_id' => $typeDokumenStok->type_dokumen_stok_id,
                        'tanggal' => $tglTemplate . ($tgl + $i),
                        'stok_awal' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok,
                        'stok_akhir' => $dombakPl->stok_inventory_dombak()->first()->stok_inventory()->first()->stok + ($standMeterLastPertalite + $pertaliteFlip[$i][$j]),
                        'is_confirmation' => 1,
                    ]);

                    $standMeterLastPertalite += $pertaliteFlip[$i][$j];
                }
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
