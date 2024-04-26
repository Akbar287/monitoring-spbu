<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\JenisLosses;
use App\Models\Nozzle;
use App\Models\PajakPembelianLib;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\Spbu;
use App\Models\StandMeter;
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
        // Dombak
        //     "DM1-PX" => "7ba4e9f2-0e71-402f-8199-4d21f20eea48"
        //     "DM2-PL" => "8fd5fafd-5add-4688-9b0a-76a8f6e237c4"
        //     "DM3-DX" => "0e3b3f9c-92fe-452f-9c7e-8d663217315e"
        //     "DM4-BS" => "67f1255b-3b89-4164-bd0e-ea9279a1b640"
        //     "DM5-BS" => "b812ee18-7f60-4232-801f-f635bf765dcd"
        //     "DM6-PDX" => "6affaa45-f173-4605-8a84-08cee72fdf98"
        // End Dombak

        // 'stok_awal'
        // 'penerimaan_bbm'
        // 'penjualan'
        // 'stok_akhir_teoritis'
        // 'stok_akhir_dipping'

        // Pertamax
        $dataPertamax = [
            [
                305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 3936.4, 3881.4, 3936.4, 3881.4, 593.4, 603.0, 568.2, 568.2, 578.3, 563.1, 553.0, 578.3, 553.0, 553.0, 553.0, 558.1, 563.1, 553.0, 573.2, 553.0, 553.0, 568.2, 553.0, 553.0, 736.4, 722.4, 708.4, 736.4, 736.4, 703.7, 8848.0, 5977.1, 5967.0, 6017.5, 12407.2, 12538.5, 12700.3, 12528.4, 12631.5, 12614.5, 15328.3, 15314.7, 15358.1, 12124.4, 12185.0, 12174.9, 11040.0, 11083.3, 13369.9, 11456.9, 11578.6, 11646.1, 8118.4, 8503.2, 8229.5, 5182.0, 5163.0, 5249.7, 328.0, 328.0, 309.6, 323.4, 305.0, 305.0, 328.0, 314.2, 305.0, 314.2, 314.2, 15900.0, 15776.0, 15900.0, 15900.0, 15880.0, 15865.0, 8554.8, 8497.4, 8564.0, 8516.4, 250.0, 261.0, 236.1, 255.5, 250.0, 277.5,
            ],
            [
                0, 0, 0, 0, 0, 0, 0, 3742.0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 169.0, 0, 0, 0, 0, 0, 8000.0, 0, 0, 0, 8000.0, 0, 0, 0, 0, 0, 8000.0, 0, 0, 0, 0, 0, 0, 0, 2162.1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 15879.8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ],
            [
                0, 0, 0, 0, 0, 0, 0, 62.6, 0, 0, 0, 3311.5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2750.6, 0, 0, 1641.5, 0, 0, 0, 0, 0, 5130.4, 0, 0, 3165.1, 0, 0, 1020.0, 0, 0, 1530.1, 0, 0, 3368.5, 0, 0, 2881.2, 0, 0, 4805.6, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6952.0, 0, 0, 0, 8373.0, 0, 0, 0, 0, 0, 0
            ],
            [
                305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 3984.4, 3936.4, 3881.4, 3936.4, 569.9, 593.4, 603.0, 568.2, 568.2, 578.3, 563.1, 553.0, 578.3, 553.0, 553.0, 553.0, 558.1, 563.1, 553.0, 573.2, 553.0, 553.0, 568.2, 553.0, 722.0, 736.4, 722.4, 708.4, 736.4, 736.4, 8703.7, 6097.4, 5977.1, 5967.0, 12376.0, 12407.2, 12538.5, 12700.3, 12528.4, 12631.5, 15484.1, 15328.3, 15314.7, 12193.0, 12124.4, 12185.0, 10644.8, 11040.0, 13245.4, 10001.4, 11456.9, 11578.6, 8277.6, 8118.4, 8503.2, 5348.4, 5182.0, 5163.0, 444.1, 328.0, 328.0, 309.6, 323.4, 305.0, 305.0, 328.0, 314.2, 305.0, 314.2, 16194.0, 15900.0, 15776.0, 15900.0, 15900.0, 15880.0, 8913.0, 8554.8, 8497.4, 8564.0, 143.4, 250.0, 261.0, 236.1, 255.5, 250.0, 277.5
            ],
            [
                305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 305.0, 3936.4, 3881.4, 3936.4, 3881.4, 593.4, 603.0, 568.2, 568.2, 578.3, 563.1, 553.0, 578.3, 553.0, 553.0, 553.0, 558.1, 563.1, 553.0, 573.2, 553.0, 553.0, 568.2, 553.0, 553.0, 736.4, 722.4, 708.4, 736.4, 736.4, 703.7, 8848.0, 5977.1, 5967.0, 6017.5, 12407.2, 12538.5, 12700.3, 12528.4, 12631.5, 12614.5, 15328.3, 15314.7, 15358.1, 12124.4, 12185.0, 12174.9, 11040.0, 11083.3, 13369.9, 11456.9, 11578.6, 11646.1, 8118.4, 8503.2, 8229.5, 5182.0, 5163.0, 5249.7, 328.0, 328.0, 309.6, 323.4, 305.0, 305.0, 328.0, 314.2, 305.0, 314.2, 314.2, 15900.0, 15776.0, 15900.0, 15900.0, 15880.0, 15865.0, 8544.8, 8497.4, 8564, 8516.4, 250, 261, 236.1, 255.5, 250, 277.5, 265
            ]
        ];

        // Pertalite
        $dataPertalite = [
            [
                13969.9, 8153.0, 12251.50, 15043.0, 8225.1, 562.0, 8698.9, 8719.5, 13152.0, 13152.0, 13152.0, 13152.0, 13201.4, 7461.3, 562.0, 8729.8, 2581.0, 690.1, 9286.0, 3316.4, 6927.2, 4665.0, 562.0, 14582.0, 12736.0, 7557.6, 12166.9, 13694.0, 8719.5, 14276.0, 14595.0, 9327.2, 14859.0, 10688.0, 6278.3, 11380.4, 15037.8, 10304.0, 15147.0, 15147.0, 10075.8, 14492.5, 14486.1, 9595.0, 6443.1, 14479.8, 10106.0, 6577.0, 14614.1, 10498.0, 6474.0, 6401.9, 1148.0, 7280.3, 15282.6, 11090.1, 12914.7, 13580.2, 8822.5, 5510.9, 3254.0, 562.0, 6062.0, 6062.0, 573.3, 2861.5, 2508.2, 562.0, 3626.0, 5859.0, 573.3, 5290.9, 10304.0, 5371.0, 9512.6, 9522.9, 11455.7, 7844.7, 7855.3, 3431.0, 14352.8, 14384.7, 9646.5, 14404, 14410.4, 9059.4, 3626.0, 12008.4, 6813.9, 11015.8, 14442.3, 10106.0, 13995.6,
            ],
            [
                0, 8000.0, 8000.0, 0.00, 0.0, 8000.0, 0.00, 4412.0, 0.00, 0.00, 0.00, 0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 8000.0, 0, 0, 16000.0, 0, 0, 8000.0, 8000.0, 0, 8000.0, 8000.0, 0, 7842.7, 0, 0, 8000.0, 8000.0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 0, 0, 8000.0, 8000.0, 0, 5928.9, 0, 0, 0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 8000.0, 0, 8000.0, 8000.0, 0, 8000.0, 0, 8000.0, 0, 0, 0, 16000.0, 0, 0, 6952.0, 0, 0, 0, 8373.0, 0, 8000.0, 8000.0, 0, 8000.0, 0
            ],
            [
                5818.0, 3985.0, 5062.0, 6859.0, 7428.0, 0, 0, 0, 0, 0, 0, 0, 5618.0, 6660.0, 0, 6002.0, 1480.0, 0, 5757.0, 3673.0, 2970.0, 4043.0, 2112.0, 1980.0, 5017.0, 3530.0, 6403.0, 4935.0, 2398.0, 7641.0, 5297.0, 2207.0, 4251.0, 4235.0, 3038.0, 4266.0, 4792.0, 3057.0, 0, 5155.0, 3478.0, 0, 4998.0, 2993.0, 0, 4464.0, 3383.0, 0, 4226.0, 3865.0, 0, 5178.0, 2049.0, 0, 4274.0, 3486.0, 0, 4715.0, 3114.0, 2200.0, 2699.0, 2593.0, 0, 5400.0, 5719.0, 350.0, 1942.0, 4949.0, 5875.0, 5170.0, 3355.0, 3160.0, 4747.0, 4049.0, 0, 6182.0, 4407.0, 0, 4266.0, 5200.0, 0, 4776.0, 2065.0, 0, 5420.0, 5199.0, 0, 5055.0, 3892.0, 4505.0, 4438.0, 4003.0, 0
            ],
            [
                8151.9, 12168.0, 15189.5, 8184.0, 797.1, 8562.0, 8740.1, 13131.5, 13152.0, 13152.0, 13152.0, 13152.0, 7583.4, 801.3, 8562.0, 2727.8, 1101.0, 8690.1, 3529.0, 7643.4, 3957.2, 622.0, 14450.0, 12602.0, 7719.0, 12027.6, 13763.9, 8759.0, 14321.5, 14635.0, 9298.0, 14962.9, 10608.0, 6453.0, 11240.3, 15114.4, 10245.8, 15247.0, 15147.0, 9992.0, 14597.8, 14492.5, 9488.1, 6602.0, 14443.1, 10015.8, 6723.0, 14577.0, 10388.1, 6633.0, 6474.0, 1223.9, 7099.0, 15280.3, 11008.6, 13533.0, 12914.7, 8865.2, 5708.5, 3310.9, 555.0, 5969.0, 6062.0, 662.0, 2854.3, 2511.5, 566.2, 3613.0, 5751.0, 689.0, 5218.3, 10130.9, 5557.0, 9322.0, 9512.6, 11340.9, 7048.7, 7844.7, 3589.3, 14231.0, 14352.8, 9608.7, 14533.5, 14404.0, 8990.4, 3860.4, 11999.0, 6953.4, 10921.9, 14510.8, 10004.3, 14103.0, 13995.6,
            ],
            [
                8153.0, 12251.5, 15043.0, 8225.1, 562.0, 8698.9, 8719.5, 13152.0, 13152.0, 13152.0, 13152.0, 13201.4, 7461.3, 562.0, 8729.8, 2581.0, 690.1, 9286.0, 3316.4, 6927.2, 4665.0, 562.0, 14582.0, 12736.0, 7557.6, 12166.9, 13694.0, 8719.5, 14276.0, 14595.0, 9327.2, 14859.0, 10688.0, 6278.3, 11380.4, 15037.8, 10304.0, 15147.0, 15147.0, 10075.8, 14492.5, 14486.1, 9595.0, 6443.1, 14479.8, 10106.0, 6577.0, 14614.1, 10498.0, 6474.0, 6401.9, 1148.0, 7280.3, 15282.6, 11090.1, 12914.7, 13580.2, 8822.5, 5510.9, 3254.0, 562.0, 6062.0, 6062.0, 573.3, 2861.5, 2508.2, 562.0, 3626.0, 5859.0, 573.3, 5290.9, 10304.0, 5371.0, 9512.6, 9522.9, 11455.7, 7844.7, 7855.3, 3431.0, 14352.8, 14384.7, 9646.5, 14404, 14410.4, 9059.4, 3626.0, 12008.4, 6813.9, 11015.8, 14442, 10106, 13995.6, 14033.8
            ]
        ];

        // Bio Solar
        $dataBioSolar = [
            [
                10749.6, 9313.0, 12734.2, 12677.0, 8440.8, 4485.0, 9676.6, 4952.1, 4503.7, 4410.7, 4429.3, 4550.0, 4550.0, 2726.0, 413.7, 399.0, 5312.0, 3640.2, 3589.0, 399.0, 423.5, 8676.0, 3529.6, 8431.0, 14767.0, 11185.7, 7439.3, 3972.6, 582.2, 4876.7, 4848.7, 399.0, 374.5, 5263.0, 5351.2, 5302.2, 13101.2, 13108.7, 13177.2, 11508.0, 11550.6, 11508.0, 6741.8, 6790.4, 6751.6, 14424.1, 10714.0, 10714.0, 2652.2, 2644.0, 2644.0, 3171.6, 3154.5, 3146.0, 9769.6, 9788.0, 9695.0, 7693.9, 5439.4, 5459.0, 11052.3, 8558.4, 8558.4, 8764.2, 5233.6, 6234.4, 12019.0, 9993.7, 10068.0, 7135.0, 5802.0, 5704.0, 12514.0, 10625.0, 10625.0, 13788.0, 8921.0, 4867.3, 5410.0, 5312.0, 4316.7, 12187.0, 8313.4, 5527.6, 13365.9, 9769.6, 5018.0, 4961.5, 7344.3, 2293.2, 10456.7, 6407.0, 12061.6,
            ],
            [
                0, 5661.0, 0, 0, 0, 8000.0, 0, 0, 0, 0, 0, 0, 2806.2, 0, 0, 8000.0, 0, 0, 0, 0, 8000.0, 0, 8000.0, 8000.0, 0, 0, 0, 0, 8000.0, 0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 3160.6, 0, 0, 0, 4677.0, 0, 8000.0, 0, 0, 8000.0, 0, 0, 0, 8000.0, 0, 8000.0, 0, 8000.0, 8000.0
            ],
            [
                1446.0, 2052.0, 0, 4516.0, 3881.0, 2910.0, 4725.0, 473.0, 0, 0, 0, 0, 4251.0, 2183.0, 0, 3406.0, 1478.0, 0, 3149.0, 0, 22.0, 4994.0, 3170.0, 1376.0, 3959.0, 3833.0, 3238.0, 3519.0, 3859.0, 0, 4187.0, 0, 3406.0, 0, 0, 0, 0, 0, 1740.0, 0, 0, 12936.0, 0, 0, 0, 3953.0, 46.0, 16001.0, 0, 0, 7496.0, 11.0, 0, 1605.0, 0, 0, 10074.0, 2280.0, 0, 2363.0, 2497.0, 0, 7916.0, 2463.0, 0, 2127.0, 2097.0, 0, 10921.0, 1477.0, 29.0, 990.0, 2052.0, 0, 0, 5136.0, 3956.0, 0, 4311.0, 1207.0, 0, 4004.0, 2804.0, 0, 3778.0, 4743.0, 0, 5576.0, 4876.0, 22.0, 4139.0, 2187.0, 10204.0
            ],
            [
                9303.6, 12922.0, 12734.2, 8161.0, 4559.8, 9575.0, 4951.6, 4479.1, 4503.7, 4410.7, 4429.3, 4550.0, 3105.2, 543.0, 413.7, 4993.0, 3834.0, 3640.2, 440.0, 399.0, 8401.5, 3682.0, 8359.6, 15055.0, 10808.0, 7352.7, 4201.3, 453.6, 4723.2, 4876.7, 661.7, 399.0, 4968.5, 5263.0, 5351.2, 13302.2, 13101.2, 13108.7, 11437.2, 11508.0, 11550.6, 6572.0, 6741.8, 6790.4, 14751.6, 10471.1, 10668.0, 2713.0, 2652.2, 2644.0, 3148.0, 3160.6, 3154.5, 9541.0, 9769.6, 9788.0, 7621.0, 5413.9, 5439.4, 11096.0, 8555.3, 8558.4, 8642.4, 6301.2, 5233.6, 12107.4, 9922.0, 9993.7, 7147.0, 5658.0, 5773.0, 12714.0, 10462.0, 10625.0, 13785.6, 8652.0, 4965.0, 4867.3, 5776.0, 4105.0, 12316.7, 8183.0, 5509.4, 13527.6, 9587.9, 5026.6, 5018.0, 7385.5, 2468.3, 10271.2, 6317.7, 12220.0, 9857.6,
            ],
            [
                9313.0, 12734.2, 12677.0, 8440.8, 4485.0, 9676.6, 4952.1, 4503.7, 4410.7, 4429.3, 4550.0, 4550.0, 2726.0, 413.7, 399.0, 5312.0, 3640.2, 3589.0, 399.0, 423.5, 8676.0, 3529.6, 8431.0, 14767.0, 11185.7, 7439.3, 3972.6, 582.2, 4876.7, 4848.7, 399.0, 374.5, 5263.0, 5351.2, 5302.2, 13101.2, 13108.7, 13177.2, 11508.0, 11550.6, 11508.0, 6741.8, 6790.4, 6751.6, 14424.1, 10714.0, 10714.0, 2652.2, 2644.0, 2644.0, 3171.6, 3154.5, 3146.0, 9769.6, 9788.0, 9695.0, 7693.9, 5439.4, 5459.0, 11052.3, 8558.4, 8558.4, 8764.2, 5233.6, 6234.4, 12019.0, 9993.7, 10068.0, 7135.0, 5802.0, 5704.0, 12514.0, 10625.0, 10625.0, 13788.0, 8921.0, 4867.3, 5410.0, 5312.0, 4316.7, 12187.0, 8313.4, 5527.6, 13365.9, 9769.6, 5018.0, 4961.5, 7344.3, 2293.2, 10456.7, 6407, 12061.6, 9984.3,
            ]
        ];

        $dataPertaminaDex = [];
        $dataDexlite = [];

        $dombak = Dombak::select('nama_dombak', 'dombak_id')->where('nama_dombak', 'DM2-PL')->first();

        $dataPertamaxFlip = $this->flip_row_col_array($dataPertamax);
        unset($dataPertamaxFlip);

        $data = $dataPertamax;
        unset($dataPertamax);

        $standMeterLast = 5665318;

        // Inisiasi Standar
        $tgl = 1;
        $jadwalKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];
        $data = array_chunk($dataPertamaxFlip, count($jadwalKerja));
        $type_dokumen_stock = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Stok Taking')->first();

        $type_dokumen_stock_penerimaan = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Good Receive Penerimaan')->first();
        $type_dokumen_stock_penjualan = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Good Issue Sales')->first();

        $stok_inventory = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first();
        $jenisLosses = JenisLosses::where('jenis_loss', 'Stok Taking')->first();
        $stokAwal = $stok_inventory->stok;

        // Process ...
        for ($i = 0; $i < count($data); $i++) {
            for ($j = 0; $j < count($jadwalKerja); $j++) {
                if ($data[$i][$j]['1'] !== 0) {
                    // Ada Penerimaan
                    $dokumenStokPenerimaan = $type_dokumen_stock_penerimaan->dokumen_stok()->create([
                        'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'member_id' => Auth::user()->member_id,
                        'dombak_id' => $dombak->dombak_id,
                        'tanggal' => date('2024-01-' . ($tgl + $i)),
                        'stok_awal' => $stokAwal,
                        'stok_akhir' => $stokAwal + $data[$i][$j]['1'],
                        'is_confirmation' => true
                    ]);
                    $stokAwal = $stokAwal + $data[$i][$j]['1'];
                    $konversi = $dombak->konversi()->where('volume', ($stokAwal + $data[$i][$j]['1']))->first();

                    // Update Stok
                    $stok_inventory->update([
                        'stok' => $stok_inventory->stok + $data[$i][$j]['1']
                    ]);
                    $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->update([
                        'stok' => $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->stok + $data[$i][$j]['1']
                    ]);

                    // Buat relasi Konversi ke dokumen
                    if (!is_null($konversi)) {
                        DB::table('konversi_dokumen_stok')->insert([
                            'konversi_id' => $konversi->konversi_id,
                            'dokumen_stok_id' => $dokumenStokPenerimaan->dokumen_stok_id
                        ]);
                    }
                }
                if ($data[$i][$j]['2'] !== 0) {
                    // Ada Penjualan
                    $dokumenStokPenjualan = $type_dokumen_stock_penjualan->dokumen_stok()->create([
                        'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'member_id' => Auth::user()->member_id,
                        'dombak_id' => $dombak->dombak_id,
                        'tanggal' => date('2024-01-' . ($tgl + $i)),
                        'stok_awal' => $stokAwal,
                        'stok_akhir' => $stokAwal - $data[$i][$j]['2'],
                        'is_confirmation' => true
                    ]);
                    $stokAwal = $stokAwal - $data[$i][$j]['2'];

                    // Update Stok
                    $stok_inventory->update([
                        'stok' => $stok_inventory->stok - $data[$i][$j]['2']
                    ]);
                    $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->update([
                        'stok' => $stok_inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first()->stok - $data[$i][$j]['2']
                    ]);

                    // Relasi Konversi ke dokumen stok
                    $konversi = $dombak->konversi()->where('volume', ($stokAwal - $data[$i][$j]['2']))->first();

                    if (!is_null($konversi)) {
                        DB::table('konversi_dokumen_stok')->insert([
                            'konversi_id' => $konversi->konversi_id,
                            'dokumen_stok_id' => $dokumenStokPenjualan->dokumen_stok_id
                        ]);
                    }

                    // Isi Stand Meter
                    $nozzle = Nozzle::where('nama_nozzle', 'N-1B PX')->first();

                    $standMeter = $nozzle->stand_meter()->create([
                        'dombak_id' => $dombak->dombak_id,
                        'jam_kerja_id' => $jadwalKerja[$j],
                        'tanggal' => '2024-01-' . ($tgl + $i),
                        'stand_meter_awal' => $standMeterLast,
                        'stand_meter_akhir' => $standMeterLast + $data[$i][$j]['2'],
                        'is_confirmation' => true
                    ]);
                    $standMeterLast += $data[$i][$j]['2'];

                    DB::table('stand_meter_dokumen_stok')->insert([
                        'stand_meter_id' => $standMeter->stand_meter_id,
                        'dokumen_stok_id' => $dokumenStokPenjualan->dokumen_stok_id
                    ]);
                }

                // Stok Taking
                $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
                    'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                    'jam_kerja_id' => $jadwalKerja[$j],
                    'member_id' => Auth::user()->member_id,
                    'dombak_id' => $dombak->dombak_id,
                    'tanggal' => date('2024-01-' . ($tgl + $i)),
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
                        'tanggal' => date('2024-01-' . ($tgl + $i) . ' H:i:s'),
                        'simbol' => 'kurang',
                        'jumlah' => $stokAwal - $data[$i][$j]['4']
                    ]);
                } else {
                    // Profit
                    $dokumenStok->losses()->create([
                        'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                        'tanggal' => date('2024-01-' . ($tgl + $i) . ' H:i:s'),
                        'simbol' => 'lebih',
                        'jumlah' => ($stokAwal - $data[$i][$j]['4']) * -1
                    ]);
                }
                $stokAwal = $data[$i][$j]['4'];
            }
        }

        return redirect()->json([], 200);
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
