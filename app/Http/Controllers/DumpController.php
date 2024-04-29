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

    public function uploadDataStokTaking()
    {
        // Semua dimulai dari Februari

        // Pertamax
        $dataPertamax = [
            [
                231.5, 250.0, 250.0, 255.5, 250.0, 250.0, 250.0, 250.0, 250.0, 250.0, 261.0, 250.0, 245.7, 250.0, 250.0, 250.0, 250.0, 250.0, 250.0, 261.0, 213.2, 261.0, 250.0, 250.0, 250.0, 236.1, 250.0, 250.0, 227.0, 240.7, 250.0, 217.8, 217.8, 240.7, 231.5, 250.0, 8392.9, 8179.0, 7376.6, 7602.4, 7582.2, 7471.7, 7461.0, 7228.7, 7008.9, 7058.0, 6969.8, 6490.5, 6461.6, 6310.4, 5866.0, 5855.9, 5047.7, 4277.9, 4287.5, 3844.4, 3385.4, 3376.3, 2834.1, 2711.0, 2808.8, 2630.0, 2582.3, 2598.5, 2550.0, 2312.0, 2312.0, 2070.0, 1973.3, 1973.3, 1681.8, 1512.2, 1485.1, 751.3, 603.0, 640.1, 328.0, 328.0, 314.2, 341.7, 8249.7, 8189.1, 8027.5, 7380.2, 7299.4, 6706.1, 6199.3
            ],
            [
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, [236.1, 8000.0, 8392.9], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, [346.3, 8000.0, 8544.8], 0, 0, 0, 0, 0, 0, 0
            ],
            [
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 158.6, 536.4, 0, 68.7, 63.1, 0, 219.9, 238.3, 0, 79.5, 467.8, 0, 191.8, 400.3, 0, 770.1, 781.8, 0, 442.3, 422.0, 0, 533.3, 91.6, 0, 131.9, 61.2, 0, 135.5, 127.9, 0, 248.7, 95.5, 0, 287.3, 184.8, 0, 710.4, 172.3, 0, 312.9, 0, 0, 0, 246.5, 0, 235.2, 604.1, 0, 557.7, 533.9, 0
            ],
            [
                231.5, 250.0, 250.0, 255.5, 250.0, 250.0, 250.0, 250.0, 250.0, 250.0, 261.0, 250.0, 245.7, 250.0, 250.0, 250.0, 250.0, 250.0, 250.0, 261.0, 213.2, 261.0, 250.0, 250.0, 250.0, 236.1, 250.0, 250.0, 227.0, 240.7, 250.0, 217.8, 217.8, 240.7, 231.5, 8250.0, 8234.3, 7642.6, 7376.6, 7533.8, 7519.1, 7471.7, 7241.1, 6990.5, 7008.9, 6978.5, 6502.0, 6490.5, 6269.8, 5910.1, 5866.0, 5085.8, 4265.9, 4277.9, 3754.2, 3422.4, 3385.4, 2843.0, 2742.6, 2711.0, 2676.9, 2568.8, 2582.3, 2463.0, 2422.1, 2312.0, 2063.3, 1974.5, 1973.3, 1686.0, 1497.0, 1512.2, 774.7, 579.0, 603.0, 327.2, 328.0, 328.0, 314.2, 8095.2, 8249.7, 7953.9, 7423.5, 7380.2, 6741.7, 6172.2, 6199.3,
            ],
            [
                250.0, 250.0, 255.5, 250.0, 250.0, 250.0, 250.0, 250.0, 250.0, 261.0, 250.0, 245.7, 250.0, 250.0, 250.0, 250.0, 250.0, 250.0, 261.0, 213.2, 261.0, 250.0, 250.0, 250.0, 236.1, 250.0, 250.0, 227.0, 240.7, 250.0, 217.8, 217.8, 240.7, 231.5, 250.0, 8392.9, 8179.0, 7376.6, 7602.4, 7582.2, 7471.7, 7461.0, 7228.7, 7008.9, 7058.0, 6969.8, 6490.5, 6461.6, 6310.4, 5866.0, 5855.9, 5047.7, 4277.9, 4287.5, 3844.4, 3385.4, 3376.3, 2834.1, 2711.0, 2808.8, 2630.0, 2582.3, 2598.5, 2550.0, 2312.0, 2312.0, 2070.0, 1973.3, 1973.3, 1681.8, 1512.2, 1485.1, 751.3, 603.0, 640.1, 328.0, 328.0, 314.2, 341.7, 8249.7, 8189.1, 8027.5, 7380.2, 7299.4, 6706.1, 6199.3, 6169.0,
            ]
        ];

        // Pertalite
        $dataPertalite = [
            [
                14033.8, 8966.7, 4188.7, 13957.0, 8565.0, 1703.0, 8946.1, 2013.7, 562.0, 15177.8, 8153.0, 1710.6, 14620.5, 7102.3, 1945.1, 15136.6, 8925.5, 11521.2, 11559.1, 3644.5, 8225.1, 6927.2, 7323.3, 9553.8, 14276.0, 8338.4, 1277.0, 14601.4, 8266.3, 4626.3, 10401.0, 3068.1, 562.0, 8771.0, 562.0, 601.7, 9615.6, 1556.0, 562.0, 14168.1, 8915.2, 5829.8, 14187.3, 7408.0, 5290.9, 7344.6, 1012.0, 567.7, 13184.9, 5172.0, 3734.5, 3882.2, 579.0, 573.3, 5599.9, 579.0, 573.3, 573.3, 4402.1, 6577.0, 14614.1, 6762.4, 2029.0, 10313.7, 2180.3, 5779.9, 5809.9, 8492.9, 1548.6, 9801.0, 579.0, 1358.6, 1392.6, 8822.5, 1801.7, 10075.8, 567.7, 573.3, 8884.3, 6062.0, 573.3, 8750.4, 584.7, 5430.8, 5430.8, 532.2, 3947.4
            ],
            [
                null,
                null,
                [[4188.7, 8000.0,  12450.8], [5221.8, 8000.0,  13234.0]],
                null,
                null,
                [[764.3, 8000.0, 8956.4]],
                null,
                null,
                [[562, 8000.0, 8771.00], [7122.9, 8000.0, 151730]], null,
                null,
                [[1703.0, 8000.0,  9955.4], [6535.8, 8000.0,  14556.5]], null,
                null,
                [[1870.1, 8000.0,  10145.9], [7112.6, 8000.0,  15136.6]],
                null,
                [[5290.9, 8000.0,  13503.5]],
                null,
                null,
                [[562.0, 8000.0,  8771.0]],
                [[2196.6, 8000.0,  10459.0]],
                [[5101.9, 8000.0,  13316.0]],
                [[1923.0, 8000.0,  10195.7]],
                [[6185.6, 8000.0,  14282.4]],
                null,
                null,
                [[1277.0, 8000.0,  9522.9], [6535.8, 8000.0, 14595.0]],
                null,
                null,
                [[3994.0, 8000.0, 12232.8]],
                null,
                null,
                [[562.0, 8000.0, 8771.0]],
                null,
                null,
                [[562.0, 8000.0, 8791.6], [7195.0, 8000.0, 15237.7]],
                null,
                null,
                [[562.0, 8000.0, 8781.3], [6226.8, 8000.0, 14250.3]],
                null,
                null,
                [[573.3, 8000.0, 8791.6], [6041.4, 8000.0, 14168.1]],
                null,
                null,
                [[1930.0, 8000.0, 10205.0]],
                null,
                null,
                [[562.0, 8000.0, 8791.6], [6329.8, 8000.0, 14435.9]],
                null,
                null,
                [[1703.0,  8000.0,  10035.9]],
                null,
                null,
                [[579.0,  8000.0, 8874.0]],
                null,
                null,
                null,
                [[590.4, 8000.0, 8781.3]],
                [[2433.0, 8000.0, 10762.9]],
                [[6556.4, 8000.0, 14913.0]],
                null,
                null,
                [[2029.0, 8000.0, 10304.0]],
                null,
                [[1179.9, 8000.0, 9450.8]],
                null,
                [[728.0, 8000.0, 8863.7]],
                null,
                [[1534.0, 8000.0, 9831.0]],
                null,
                [[590.4, 8000.0, 8843.1]],
                null,
                [[584.7, 8000.0, 8822.5]],
                null,
                [[1816.8, 8000.0, 10065.8]],
                null,
                null,
                [[590.4, 8000.0, 8781.3]],
                [[6165.0, 8000.0, 14237.5]],
                null,
                [[573.3, 8000.0, 8801.9]],
                null,
                [[590.4, 8000.0, 8801.9]],
                null,
                null,
                [[590.4, 8000.0, 8822.5]],
                [[3984.4, 8000.0, 12204.7]]
            ],
            [
                [1803, 3308], [1881, 2611], [2919, 3503], [1701, 3702], [2169, 4415], [264, 714], [2005, 4729], [504, 945], [375, 1160], [2144, 4893], [2148, 4082], [1340, 1930], [2440, 4978], [1575, 3518], [680, 2221], [2144, 4108], [1732, 3666], [0, 0], [2480, 5169], [1150, 2409], [3932, 5277], [2690, 4898], [1892, 3949], [1406, 1815], [1714, 4248], [2169, 4658], [1456, 1359], [1949, 4407], [1177, 2306], [818, 1598], [2189, 4903], [721, 1766], [0, 0], [2516, 5461], [0, 0], [3260, 3882], [2488, 5310], [362, 645], [1413, 1140], [1801, 3460], [1281, 1716], [3223, 4518], [1999, 4715], [780, 1272], [2802, 3206], [1758, 4400], [142, 303], [1428, 2229], [2289, 5517], [442, 928], [4092, 3748], [1108, 2161], [0, 0], [1479, 1578], [1630, 3296], [0, 0], [0, 0], [1399, 2808], [1953, 3948], [0, 0], [2708, 5099], [1415, 3157], [0, 0], [2486, 5283], [1658, 2877], [0, 0], [1770, 3675], [2131, 4586], [0, 0], [3210, 5732], [2976, 4233], [0, 0], [248, 545], [2305, 4457], [0, 0], [2951, 6263], [0, 0], [0, 0], [3640, 6935], [1987, 3375], [0, 0], [2380, 5591], [1142, 2079], [0, 0], [1849, 2905], [1954, 2681], [0, 0]
            ],
            [
                8922.8, 4474.7, 13766.7, 8554.0, 1981.0, 8725.0, 8740.1, 564.7, 15027.0, 8140.8, 1923.0, 14440.6, 7202.5, 2009.3, 15044.1, 8884.6, 11527.5, 11521.2, 3910.1, 8085.5, 7016.1, 7339.2, 9482.3, 14332.8, 8314.0, 1511.4, 14462.0, 8245.4, 4783.3, 10210.3, 3309.0, 581.1, 8562.0, 794.0, 562.0, 9459.7, 1817.6, 549.0, 14009.0, 8907.1, 5918.2, 14088.8, 7473.3, 5356.0, 7203.9, 1265.6, 567.0, 12910.7, 5378.9, 3802.0, 3894.5, 613.2, 579.0, 5516.3, 673.9, 579.0, 573.3, 4366.3, 6501.1, 14577.0, 6807.1, 2190.4, 10029.0, 2544.7, 5645.3, 5779.9, 8364.9, 1775.9, 9548.6, 859.0, 1370.0, 1358.6, 8599.6, 2060.5, 9801.7, 861.8, 567.7, 8573.3, 6309.3, 700.0, 8573.3, 779.4, 5363.7, 5430.8, 676.8, 3897.2, 11947.4
            ],
            [
                8966.7, 4188.7, 13957.0, 8565.0, 1703.0, 8946.1, 2013.7, 562.0, 15177.8, 8153.0, 1710.6, 14620.5, 7102.3, 1945.1, 15136.6, 8925.5, 11521.2, 11559.1, 3644.5, 8225.1, 6927.2, 7323.3, 9553.8, 14276.0, 8338.4, 1277.0, 14601.4, 8266.3, 4626.3, 10401.0, 3068.1, 562.0, 8771.0, 562.0, 601.7, 9615.6, 1556.0, 562.0, 14168.1, 8915.2, 5829.8, 14187.3, 7408.0, 5290.9, 7344.6, 1012.0, 567.7, 13184.9, 5172.0, 3734.5, 3882.2, 579.0, 573.3, 5599.9, 579.0, 573.3, 573.3, 4402.1, 6577.0, 14614.1, 6762.4, 2029.0, 10313.7, 2180.3, 5779.9, 5809.9, 8492.9, 1548.6, 9801.0, 579.0, 1358.6, 1392.6, 8822.5, 1801.7, 10075.8, 567.7, 573.3, 8884.3, 6062.0, 573.3, 8750.4, 584.7, 5430.8, 5430.8, 532.2, 3947.4, 12186.0,
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
        $dombak = Dombak::select('nama_dombak', 'dombak_id')->where('nama_dombak', 'DM4-BS')->first();
        $tgl = 1;
        $bln = '02';
        $nozzle = [
            Nozzle::where('nama_nozzle', 'N-3A BS')->first()
        ];
        $standMeterLast = [
            13966444
        ];

        // $nozzle1 = Nozzle::where('nama_nozzle', 'N-1B PX')->first(); // Pertamax
        // $nozzle1 = Nozzle::where('nama_nozzle', 'N-3A BS')->first(); // Bio Solar
        // $nozzle1 = Nozzle::where('nama_nozzle', 'N-3B PDX')->first(); // Nozzle-3B Pertamina Dex

        // End Dinamis
        $dataBioSolarFlip = $this->flip_row_col_array($dataBioSolar);
        unset($dataBioSolar);
        $jamKerja = [
            'd61ddc4b-f97c-4361-935a-26718af856d7',
            '2d377da7-19fd-4459-8a1b-fd5bf5b390a3',
            'ddcfd64a-dae4-4044-a58f-ae7035108a92',
        ];
        $data = array_chunk($dataBioSolarFlip, count($jamKerja));
        unset($dataBioSolarFlip);

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
        $dombakPl = Dombak::where('nama_dombak', 'DM2-PL')->first();

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
