<?php

namespace App\Http\Controllers;

use App\Exports\PenerimaanExport;
use App\Exports\StokDombakExport;
use App\Exports\TotalisatorExport;
use App\Exports\TotalPenjualanExport;
use App\Models\DokumenStok;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\Spbu;
use App\Models\StandMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MarginController extends Controller
{
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $produk = Produk::where('is_aktif', 1)->get();
        $typeReport = [['type_report_id' => 1, 'nama_type_report' => 'Totalisator'], ['type_report_id' => 2, 'nama_type_report' => 'Total Penjualan BBM'], ['type_report_id' => 3, 'nama_type_report' => 'Setoran, Penerimaan BBM'], ['type_report_id' => 4, 'nama_type_report' => 'Bongkaran BBM Aktual'], ['type_report_id' => 5, 'nama_type_report' => 'Stok BBM di Dombak']];

        $spbuSelected = $request->spbu_id;
        $produkSelected = $request->produk_id ?? 'all';
        $monthSelected = $request->month ?? date('Y-m');
        $typeReportSelected = $request->type_report_id ?? '1';

        $waktuMulai = Carbon::createFromFormat('Y-m', $monthSelected)->firstOfMonth();
        $waktuSelesai = Carbon::createFromFormat('Y-m', $monthSelected)->endOfMonth();
        if (!is_null($request->type_report_id)) {
            if ($request->type_report_id == '1') {
                return $this->totalisator($waktuMulai, $waktuSelesai);
            }

            if ($request->type_report_id == '2') {
                return $this->total_penjualan($waktuMulai, $waktuSelesai);
            }
            if ($request->type_report_id == '3') {
                return $this->setoran_penerimaan($waktuMulai, $waktuSelesai);
            }

            if ($request->type_report_id == '4') {
                return $this->bongkaran($waktuMulai, $waktuSelesai);
            }
            if ($request->type_report_id == '5') {
                return $this->stok_dombak($waktuMulai, $waktuSelesai);
            }
        }
        return view('financial/margin/index', compact('spbu', 'spbuSelected', 'monthSelected', 'produk', 'produkSelected', 'typeReport', 'typeReportSelected'));
    }
    public function totalisator($mulai, $selesai)
    {
        $data = StandMeter::select('stand_meter.tanggal', 'jam_kerja.nama_jam_kerja', 'nozzle.nama_nozzle', 'produk.nama_produk', 'stand_meter.stand_meter_awal', 'stand_meter.stand_meter_akhir', 'harga.harga_jual')->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')->join('produk', 'produk.produk_id', 'nozzle.produk_id')->join('harga', 'harga.produk_id', 'produk.produk_id')->where('harga.is_default', 1)->where('stand_meter.is_confirmation', 1)->whereBetween('stand_meter.tanggal', [$mulai, $selesai])->orderBy('stand_meter.tanggal', 'asc')->orderBy('stand_meter.urutan', 'asc')->orderBy('nozzle.nama_nozzle', 'asc')->get()->toArray();

        $temp = [];
        $tgl = null;
        $shift = null;
        $total = 0;
        foreach ($data as $d) {
            $tgl = is_null($tgl) ? $d['tanggal'] : $tgl;
            $shift = is_null($shift) ? $d["nama_jam_kerja"] : $shift;

            if ($tgl == $d['tanggal']) {

                if ($shift == $d["nama_jam_kerja"]) {
                    $total += (floatval($d["stand_meter_akhir"]) - floatval($d["stand_meter_awal"])) * floatval($d["harga_jual"]);

                    $temp[] = [
                        'tanggal' => $d['tanggal'],
                        "Shift" => $d["nama_jam_kerja"],
                        "nama_nozzle" => $d["nama_nozzle"],
                        "nama_produk" => $d["nama_produk"],
                        "stand_meter_awal" => floatval($d["stand_meter_awal"]),
                        "stand_meter_akhir" => floatval($d["stand_meter_akhir"]),
                        "harga_jual" => 'Rp. ' . number_format(floatval($d["harga_jual"]), 2, ",", "."),
                        "total_penjualan" => 'Rp. ' . number_format((floatval($d["stand_meter_akhir"]) - floatval($d["stand_meter_awal"])) * floatval($d["harga_jual"]), 2, ",", "."),
                    ];
                } else {
                    $temp[] = [
                        'tanggal' => '',
                        "Shift" => '',
                        "nama_nozzle" => '',
                        "nama_produk" => '',
                        "stand_meter_awal" => '',
                        "stand_meter_akhir" => '',
                        "harga_jual" => 'Total Penjualan',
                        "total_penjualan" => 'Rp. ' . number_format($total, 2, ",", "."),
                    ];
                    $total = 0;
                    $shift = $d["nama_jam_kerja"];
                }
            } else {
                $temp[] = [
                    'tanggal' => '',
                    "Shift" => '',
                    "nama_nozzle" => '',
                    "nama_produk" => '',
                    "stand_meter_awal" => '',
                    "stand_meter_akhir" => '',
                    "harga_jual" => 'Total Penjualan',
                    "total_penjualan" => 'Rp. ' . number_format($total, 2, ",", "."),
                ];
                $total = 0;
                $tgl = $d['tanggal'];
                $shift = $d["nama_jam_kerja"];
            }
        }
        unset($tgl);
        unset($shift);
        unset($total);
        unset($data);

        $header = [
            "Tanggal",
            "Shift",
            "Nama Nozzle",
            "Produk",
            "Stand Meter Awal",
            "Stand Meter Akhir",
            "Harga Jual",
            "Total Penjualan"
        ];

        array_unshift($temp, $header);

        $final_to_download = Excel::download(new TotalisatorExport($temp), 'Totalisator.xls', \Maatwebsite\Excel\Excel::XLS);

        return $final_to_download;
    }
    public function total_penjualan($mulai, $selesai)
    {
        $produk = Produk::orderBy('nama_produk', 'asc')->where('is_aktif', 1)->get();

        $temp = [];
        foreach ($produk as $p) {
            $r = $p->nozzle()->join('stand_meter', 'stand_meter.nozzle_id', 'nozzle.nozzle_id')->selectRaw('SUM("stand_meter_akhir") as stand_meter_akhir')->selectRaw('SUM("stand_meter_awal") as stand_meter_awal')->selectRaw('stand_meter.tanggal')->orderBy('stand_meter.tanggal')->where('stand_meter.is_confirmation', 1)->whereBetween('stand_meter.tanggal', [$mulai, $selesai])->groupBy('stand_meter.tanggal')->get()->toArray();

            $tempre = [];
            $totalPenjualan = 0;
            $jumlahPenjualan = 0;

            $tempre[] = [
                'tanggal' => $p->nama_produk,
                'penjualan' => '',
                'harga_jual' => '',
                'total_penjualan' => ''
            ];

            $tempre[] = [
                'tanggal' => 'Tanggal',
                'penjualan' => 'Total Penjualan',
                'harga_jual' => 'Harga Satuan',
                'total_penjualan' => 'Jumlah'
            ];

            foreach ($r as $e) {
                $tempre[] = [
                    'tanggal' => $e['tanggal'],
                    'penjualan' => $e['stand_meter_akhir'] - $e['stand_meter_awal'],
                    'harga_jual' => number_format($p->harga()->where('harga.is_default', 1)->first()->harga_jual, 2, ",", "."),
                    'total_penjualan' => '\'' . number_format((floatval($e['stand_meter_akhir']) - floatval($e['stand_meter_awal'])) * $p->harga()->where('harga.is_default', 1)->first()->harga_jual, 2, ",", ".")
                ];
                $totalPenjualan += $e['stand_meter_akhir'] - $e['stand_meter_awal'];
                $jumlahPenjualan += ((floatval($e['stand_meter_akhir']) - floatval($e['stand_meter_awal'])) * $p->harga()->where('harga.is_default', 1)->first()->harga_jual);
            }
            $tempre[] = [
                'tanggal' => '',
                'penjualan' => '\'' . number_format($totalPenjualan, 2, ",", "."),
                'harga_jual' => '',
                'total_penjualan' => '\'' . number_format($jumlahPenjualan, 2, ",", ".")
            ];
            $temp[] = $tempre;
        }

        unset($produk);
        unset($tempre);
        unset($r);
        unset($totalPenjualan);
        unset($jumlahPenjualan);

        $final_to_download = Excel::download(new TotalPenjualanExport($temp), 'Total_penjualan.xls', \Maatwebsite\Excel\Excel::XLS);
        // Storage::delete('temp/' . $name);
        return $final_to_download;
    }
    public function setoran_penerimaan($mulai, $selesai)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Sedang dalam Pengembangan ⏱️',
            'data' => []
        ], 200);
        exit;
    }
    public function bongkaran($mulai, $selesai)
    {
        $produk = Produk::orderBy('nama_produk', 'asc')->where('is_aktif', 1)->get();
        $temp = [];
        $data = [];

        foreach ($produk as $p) {
            $r = Pengiriman::select('ktp.nama', 'penerimaan.tanggal', 'pengiriman.no_pol', 'pengiriman.no_do', 'file_lo.jumlah', 'file_lo.satuan_jumlah', 'file_lo.no_lo', 'dokumen_stok.stok_awal', 'dokumen_stok.stok_akhir', 'jam_kerja.nama_jam_kerja', 'jam_kerja.jam_datang_in', 'jam_kerja.jam_pulang_out', 'losses.simbol', 'losses.jumlah')
                ->join('file_lo', 'file_lo.pengiriman_id', 'pengiriman.pengiriman_id')
                ->join('penerimaan', 'penerimaan.file_lo_id', 'file_lo.file_lo_id')
                ->join('dokumen_stok', 'dokumen_stok.dokumen_stok_id', 'penerimaan.dokumen_stok_id')
                ->leftJoin('losses', 'losses.dokumen_stok_id', 'dokumen_stok.dokumen_stok_id')
                ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'dokumen_stok.jam_kerja_id')
                ->join('member', 'member.member_id', 'pengiriman.member_id')
                ->join('ktp', 'ktp.ktp_id', 'member.ktp_id')
                ->where('pengiriman.is_done', 1)
                ->whereBetween('penerimaan.tanggal', [$mulai, $selesai])
                ->where('file_lo.produk_id', $p->produk_id)
                ->orderBy('penerimaan.tanggal', 'asc')
                ->get()->toArray();

            $temp[] = [
                $p->nama_produk,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            $temp[] = [
                'Tanggal',
                'Shift',
                'Jam',
                'Nama Penerima',
                'No. Polisi',
                'Stok Awal',
                'Stok Dibeli',
                'Stok Akhir',
                'Total Aktual',
                'Profit/Loss',
            ];
            foreach ($r as $q) {
                $temp[] = [
                    'tanggal' => $q['tanggal'],
                    'jam_kerja' => $q['nama_jam_kerja'],
                    'jam' => $q['jam_datang_in'] . '-' . $q['jam_pulang_out'],
                    'nama' => $q['nama'],
                    'no_pol' => $q['no_pol'],
                    'stok_awal' => number_format($q['stok_awal'], 2, ",", '.'),
                    'stok_dibeli' => number_format(($q['simbol'] == 'kurang') ? (($q['stok_akhir'] - $q['stok_awal']) + $q['jumlah']) : (($q['stok_akhir'] - $q['stok_awal']) - $q['jumlah']), 2, ",", '.'),
                    'stok_akhir' => number_format($q['stok_akhir'], 2, ",", '.'),
                    'total_aktual' => number_format($q['stok_akhir'] - $q['stok_awal'], 2, ",", '.'),
                    'total' => ($q['simbol'] == 'kurang') ? '(' . number_format($q['jumlah'], 2, ',', '.') . ')' : number_format($q['jumlah'], 2, ',', '.')
                ];
            }
            $temp[] = [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
        }

        $final_to_download = Excel::download(new PenerimaanExport($temp), 'bongkaran_aktual.xls', \Maatwebsite\Excel\Excel::XLS);
        // Storage::delete('temp/' . $name);
        return $final_to_download;
    }
    public function stok_dombak(Carbon $mulai, Carbon $selesai)
    {
        $produk = Produk::orderBy('nama_produk', 'asc')->where('is_aktif', 1)->get();
        $temp = [];

        foreach ($produk as $p) {
            $r = DokumenStok::select('dokumen_stok.dokumen_stok_id', 'dokumen_stok.tanggal', 'dokumen_stok.stok_awal', 'dokumen_stok.stok_akhir', 'losses.simbol', 'losses.jumlah', 'jam_kerja.nama_jam_kerja', 'jam_kerja.jam_datang_in', 'jam_kerja.jam_pulang_out', 'jam_kerja.urutan', 'type_dokumen_stok.type_movement', 'stand_meter.stand_meter_awal', 'stand_meter.stand_meter_akhir')
                ->join('stok_inventory', 'dokumen_stok.stok_inventory_id', 'stok_inventory.stok_inventory_id')
                ->leftJoin('losses', 'losses.dokumen_stok_id', 'dokumen_stok.dokumen_stok_id')
                ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'dokumen_stok.jam_kerja_id')
                ->join('type_dokumen_stok', 'type_dokumen_stok.type_dokumen_stok_id', 'dokumen_stok.type_dokumen_stok_id')
                ->leftJoin('stand_meter_dokumen_stok', 'stand_meter_dokumen_stok.dokumen_stok_id', 'dokumen_stok.dokumen_stok_id')
                ->leftJoin('stand_meter', 'stand_meter_dokumen_stok.stand_meter_id', 'stand_meter.stand_meter_id')
                ->whereBetween('dokumen_stok.tanggal', [$mulai, $selesai])
                ->where('dokumen_stok.is_confirmation', 1)
                ->where('stok_inventory.produk_id', $p->produk_id)
                ->orderBy('dokumen_stok.tanggal', 'asc')
                ->orderBy('jam_kerja.urutan', 'asc')
                ->get()->toArray();

            $tgl = null;
            $shift = null;
            $newArr = [];
            foreach ($r as $q) {
                $tgl = is_null($tgl) ? $q['tanggal'] : $tgl;
                $shift = is_null($shift) ? $q['nama_jam_kerja'] : $shift;


                if ($tgl != $q['tanggal']) {
                    $tgl = $q['tanggal'];
                    $shift = $q['nama_jam_kerja'];
                    $newArr[] = [
                        "dokumen_stok_id" => null,
                        "tanggal" => $tgl,
                        "stok_awal" => null,
                        "stok_akhir" => null,
                        "simbol" => null,
                        "jumlah" => null,
                        "nama_jam_kerja" => $shift,
                        "jam_datang_in" => null,
                        "jam_pulang_out" => null,
                        "urutan" => null,
                        "type_movement" => null,
                        "stand_meter_awal" => null,
                        "stand_meter_akhir" => null
                    ];
                    $newArr[] = $q;
                } else {
                    if ($shift != $q['nama_jam_kerja']) {
                        $shift = $q['nama_jam_kerja'];
                        $newArr[] = [
                            "dokumen_stok_id" => null,
                            "tanggal" => $tgl,
                            "stok_awal" => null,
                            "stok_akhir" => null,
                            "simbol" => null,
                            "jumlah" => null,
                            "nama_jam_kerja" => $shift,
                            "jam_datang_in" => null,
                            "jam_pulang_out" => null,
                            "urutan" => null,
                            "type_movement" => null,
                            "stand_meter_awal" => null,
                            "stand_meter_akhir" => null
                        ];
                        $newArr[] = $q;
                    } else {
                        $newArr[] = $q;
                    }
                }
            }

            unset($r);

            $tgl = null;
            $shift = null;
            $data = [
                'Tanggal' => null,
                'Shift' => null,
                'Jam' => null,
                'Stok Awal' => null,
                'Penerimaan BBM Aktual' => null,
                'Penjualan' => null,
                'Stok Akhir Teoritis' => null,
                'Stok Akhir Dipping' => null,
                'Total Losses' => null
            ];
            $temp[] = [
                $p->nama_produk,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            $temp[] = [
                'Tanggal',
                'Shift',
                'Jam',
                'Stok Awal',
                'Penerimaan BBM Aktual',
                'Penjualan',
                'Stok Akhir Teoritis',
                'Stok Akhir Dipping',
                'Total Losses',
            ];
            $totalPenerimaan = 0;
            $totalPenjualan = 0;
            $totalLosses = 0;
            $lastStok = null;
            foreach ($newArr as $e) {
                $tgl = is_null($tgl) ? $e['tanggal'] : $tgl;
                $shift = is_null($shift) ? $e['nama_jam_kerja'] : $shift;

                if ($tgl == $e['tanggal']) {
                    if ($shift == $e['nama_jam_kerja']) {
                        if ($e['type_movement'] == 'Stok Taking Good Receive Penerimaan') {
                            $data = [
                                'Tanggal' => is_null($data['Tanggal']) ? $tgl : $data['Tanggal'],
                                'Shift' => is_null($data['Shift']) ? $shift : $data['Shift'],
                                'Jam' => is_null($data['Jam']) ? $e['jam_datang_in'] . '-' . $e['jam_pulang_out'] : $data['Jam'],
                                'Stok Awal' =>  $e['stok_awal'],
                                'Penerimaan BBM Aktual' => $data['Penerimaan BBM Aktual'],
                                'Penjualan' => $data['Penjualan'],
                                'Stok Akhir Teoritis' => $data['Stok Akhir Teoritis'],
                                'Stok Akhir Dipping' => $data['Stok Akhir Dipping'],
                                'Total Losses' => $data['Total Losses']
                            ];
                        }
                        if ($e['type_movement'] == 'Good Receive Penerimaan') {
                            $data = [
                                'Tanggal' => is_null($data['Tanggal']) ? $tgl : $data['Tanggal'],
                                'Shift' => is_null($data['Shift']) ? $shift : $data['Shift'],
                                'Jam' => is_null($data['Jam']) ? $e['jam_datang_in'] . '-' . $e['jam_pulang_out'] : $data['Jam'],
                                'Stok Awal' => $data['Stok Awal'],
                                'Penerimaan BBM Aktual' => is_null($data['Penerimaan BBM Aktual']) ? (($e['simbol'] == 'lebih')  ? (($e['stok_akhir'] - $e['stok_awal']) - $e['jumlah']) : (($e['stok_akhir'] - $e['stok_awal']) + $e['jumlah'])) : (($e['simbol'] == 'lebih')  ? (($e['stok_akhir'] - $e['stok_awal']) - $e['jumlah'])  + $data['Penerimaan BBM Aktual'] : (($e['stok_akhir'] - $e['stok_awal']) + $e['jumlah']) + $data['Penerimaan BBM Aktual']),
                                'Penjualan' => $data['Penjualan'],
                                'Stok Akhir Teoritis' => $data['Stok Akhir Teoritis'],
                                'Stok Akhir Dipping' => $data['Stok Akhir Dipping'],
                                'Total Losses' => $data['Total Losses']
                            ];
                            $totalPenerimaan += is_null($data['Penerimaan BBM Aktual']) ? (($e['simbol'] == 'lebih')  ? (($e['stok_akhir'] - $e['stok_awal']) - $e['jumlah']) : (($e['stok_akhir'] - $e['stok_awal']) + $e['jumlah'])) : (($e['simbol'] == 'lebih')  ? (($e['stok_akhir'] - $e['stok_awal']) - $e['jumlah'])  + $data['Penerimaan BBM Aktual'] : (($e['stok_akhir'] - $e['stok_awal']) + $e['jumlah']) + $data['Penerimaan BBM Aktual']);
                        }
                        if ($e['type_movement'] == 'Good Issue Sales') {
                            $data = [
                                'Tanggal' => is_null($data['Tanggal']) ? $tgl : $data['Tanggal'],
                                'Shift' => is_null($data['Shift']) ? $shift : $data['Shift'],
                                'Jam' => is_null($data['Jam']) ? $e['jam_datang_in'] . '-' . $e['jam_pulang_out'] : $data['Jam'],
                                'Stok Awal' => $data['Stok Awal'],
                                'Penerimaan BBM Aktual' => $data['Penerimaan BBM Aktual'],
                                'Penjualan' => is_null($data['Penjualan']) ? ($e['stand_meter_akhir'] - $e['stand_meter_awal']) : ($e['stand_meter_akhir'] - $e['stand_meter_awal']) + $data['Penjualan'] ?? 0,
                                'Stok Akhir Teoritis' => $data['Stok Akhir Teoritis'],
                                'Stok Akhir Dipping' => $data['Stok Akhir Dipping'],
                                'Total Losses' => $data['Total Losses']
                            ];
                            $totalPenjualan += is_null($data['Penjualan']) ? ($e['stand_meter_akhir'] - $e['stand_meter_awal']) : ($e['stand_meter_akhir'] - $e['stand_meter_awal']) + $data['Penjualan'] ?? 0;
                        }
                        if ($e['type_movement'] == 'Stok Taking') {
                            $data = [
                                'Tanggal' => is_null($data['Tanggal']) ? $tgl : $data['Tanggal'],
                                'Shift' => is_null($data['Shift']) ? $shift : $data['Shift'],
                                'Jam' => is_null($data['Jam']) ? $e['jam_datang_in'] . '-' . $e['jam_pulang_out'] : $data['Jam'],
                                'Stok Awal' => is_null($lastStok) ? $e['stok_awal'] : $lastStok,
                                'Penerimaan BBM Aktual' => $data['Penerimaan BBM Aktual'],
                                'Penjualan' => $data['Penjualan'],
                                'Stok Akhir Teoritis' => $e['stok_awal'],
                                'Stok Akhir Dipping' => $e['stok_akhir'],
                                'Total Losses' => ($e['simbol'] == 'kurang') ? '(' . number_format($e['jumlah'], 2, ",", ".") . ')' : ' ' . number_format($e['jumlah'], 2, ",", ".")
                            ];
                            if (!is_null($e['simbol'])) {
                                if ($e['simbol'] == 'kurang') {
                                    $totalLosses -= $e['jumlah'];
                                }
                                if ($e['simbol'] == 'lebih') {
                                    $totalLosses += $e['jumlah'];
                                }
                            }
                            $lastStok = $e['stok_akhir'];
                        }
                    } else {
                        $data = [
                            'Tanggal' => empty($data['Tanggal']) || is_null($data['Tanggal']) ? '0' : $data['Tanggal'],
                            'Shift' => empty($data['Shift']) || is_null($data['Shift']) ? '0' : $data['Shift'],
                            'Jam' => empty($data['Jam']) || is_null($data['Jam']) ? '0' : $data['Jam'],
                            'Stok Awal' => empty($data['Stok Awal']) || is_null($data['Stok Awal']) ? '0' : number_format(is_null($data['Stok Awal']) ? 0 : $data['Stok Awal'], 2, ",", "."),
                            'Penerimaan BBM Aktual' => empty($data['Penerimaan BBM Aktual']) || is_null($data['Penerimaan BBM Aktual']) ? '0' : number_format(is_null($data['Penerimaan BBM Aktual']) ? 0 : $data['Penerimaan BBM Aktual'], 2, ",", "."),
                            'Penjualan' => empty($data['Penjualan']) || is_null($data['Penjualan']) ? '0' : number_format(is_null($data['Penjualan']) ? 0 : $data['Penjualan'], 2, ",", "."),
                            'Stok Akhir Teoritis' => empty($data['Stok Akhir Teoritis']) || is_null($data['Stok Akhir Teoritis']) ? '0' : number_format(is_null($data['Stok Akhir Teoritis']) ? 0 : $data['Stok Akhir Teoritis'], 2, ",", "."),
                            'Stok Akhir Dipping' => empty($data['Stok Akhir Dipping']) || is_null($data['Stok Akhir Dipping']) ? '0' : number_format(is_null($data['Stok Akhir Dipping']) ? 0 : $data['Stok Akhir Dipping'], 2, ",", "."),
                            'Total Losses' => empty($data['Total Losses']) || is_null($data['Total Losses']) ? '0' : $data['Total Losses'],
                        ];
                        $temp[] = $data;
                        $shift = $e['nama_jam_kerja'];
                        $data = [
                            'Tanggal' => null,
                            'Shift' => null,
                            'Jam' => null,
                            'Stok Awal' => $lastStok,
                            'Penerimaan BBM Aktual' => null,
                            'Penjualan' => null,
                            'Stok Akhir Teoritis' => null,
                            'Stok Akhir Dipping' => null,
                            'Total Losses' => null
                        ];
                    }
                } else {
                    $data = [
                        'Tanggal' => empty($data['Tanggal']) || is_null($data['Tanggal']) ? '0' : $data['Tanggal'],
                        'Shift' => empty($data['Shift']) || is_null($data['Shift']) ? '0' : $data['Shift'],
                        'Jam' => empty($data['Jam']) || is_null($data['Jam']) ? '0' : $data['Jam'],
                        'Stok Awal' => empty($data['Stok Awal']) || is_null($data['Stok Awal']) ? '0' : number_format(is_null($data['Stok Awal']) ? 0 : $data['Stok Awal'], 2, ",", "."),
                        'Penerimaan BBM Aktual' => empty($data['Penerimaan BBM Aktual']) || is_null($data['Penerimaan BBM Aktual']) ? '0' : number_format(is_null($data['Penerimaan BBM Aktual']) ? 0 : $data['Penerimaan BBM Aktual'], 2, ",", "."),
                        'Penjualan' => empty($data['Penjualan']) || is_null($data['Penjualan']) ? '0' : number_format(is_null($data['Penjualan']) ? 0 : $data['Penjualan'], 2, ",", "."),
                        'Stok Akhir Teoritis' => empty($data['Stok Akhir Teoritis']) || is_null($data['Stok Akhir Teoritis']) ? '0' : number_format(is_null($data['Stok Akhir Teoritis']) ? 0 : $data['Stok Akhir Teoritis'], 2, ",", "."),
                        'Stok Akhir Dipping' => empty($data['Stok Akhir Dipping']) || is_null($data['Stok Akhir Dipping']) ? '0' : number_format(is_null($data['Stok Akhir Dipping']) ? 0 : $data['Stok Akhir Dipping'], 2, ",", "."),
                        'Total Losses' => empty($data['Total Losses']) || is_null($data['Total Losses']) ? '0' : $data['Total Losses'],
                    ];
                    $temp[] = $data;
                    $tgl = $e['tanggal'];
                    $shift = $e['nama_jam_kerja'];
                    $data = [
                        'Tanggal' => null,
                        'Shift' => null,
                        'Jam' => null,
                        'Stok Awal' => $lastStok,
                        'Penerimaan BBM Aktual' => null,
                        'Penjualan' => null,
                        'Stok Akhir Teoritis' => null,
                        'Stok Akhir Dipping' => null,
                        'Total Losses' => null
                    ];
                }
            }

            $temp[] = [
                'Tanggal' => 'Total',
                'Shift' => null,
                'Jam' => null,
                'Stok Awal' => null,
                'Penerimaan BBM Aktual' => number_format($totalPenerimaan, 2, ",", "."),
                'Penjualan' => number_format($totalPenjualan, 2, ",", "."),
                'Stok Akhir Teoritis' => null,
                'Stok Akhir Dipping' => null,
                'Total Losses' => number_format($totalLosses, 2, ",", ".")
            ];
        }
        unset($produk);
        unset($totalPenerimaan);
        unset($totalPenjualan);
        unset($totalLosses);
        unset($tgl);
        unset($shift);
        unset($data);
        unset($lastStok);
        unset($newArr);

        $final_to_download = Excel::download(new StokDombakExport($temp), 'stok_dombak.xls', \Maatwebsite\Excel\Excel::XLS);
        return $final_to_download;
    }
}
