<?php

namespace App\Console\Commands;

use App\Models\Bonus;
use App\Models\DetailRencanaPembelian;
use App\Models\DetailRencanaPembelianMs2;
use App\Models\DetailTera;
use App\Models\DokumenStok;
use App\Models\Dombak;
use App\Models\FileLo;
use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\JenisLosses;
use App\Models\Jurnal;
use App\Models\Ktp;
use App\Models\Losses;
use App\Models\Ms2;
use App\Models\PajakPembelian;
use App\Models\PajakPembelianLib;
use App\Models\Pembayaran;
use App\Models\Penerimaan;
use App\Models\PenerimaanStokTaking;
use App\Models\PenerimaanStokTakingPenyesuaian;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\Spbu;
use App\Models\StandMeter;
use App\Models\StatusMember;
use App\Models\StatusPurchase;
use App\Models\StokInventoryDombak;
use App\Models\Supir;
use App\Models\Tera;
use App\Models\TypeDokumenStok;
use App\Support\Akun;
use App\Support\Import\MasterResolver;
use App\Support\JurnalPosting;
use App\Support\PostingKeuangan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Impor data historis SPBU 2434520 tahun 2024 dari CSV hasil extractor
 * (scripts/impor-2024/*.py -> storage/app/impor-2024/*.csv).
 *
 * Urutan tahap: bersihkan-stok-2026 -> pajak-lib -> stand-meter -> pengadaan
 * -> saldo-awal -> stok -> penjualan -> keuangan -> bonus. Tiap tahap satu transaksi;
 * --dry-run selalu rollback.
 * Keputusan pemetaan (nozzle, dombak, B2 = Shift 3, carry-over, dst.) ada di
 * scripts/impor-2024/*.py, bukan di sini.
 *
 * Bentuk data mengikuti alur aplikasi (API v1) agar layar & laporan
 * memperlakukannya sama dengan input manual:
 * - satu baris detail rencana = satu LO (jumlah dalam liter), status rencana MS2
 *   (aplikasi tidak pernah memajukan status melewati MS2);
 * - faktur & pembayaran memposting jurnal lewat model event;
 * - stok: penerimaan (3 dokumen) -> Good Issue Sales per stand meter -> Stok Taking
 *   dipping akhir shift, semua terkonfirmasi. Rantai saldo dihitung dari buku
 *   (bukan disetel ke tinggi terukur di tengah shift) supaya losses per shift
 *   sama dengan selisih dipping di Excel. Saldo stok berjalan (stok_inventory*)
 *   tidak diubah karena mencerminkan kondisi saat ini, bukan 2024.
 */
class ImporHistoris2024 extends Command
{
    protected $signature = 'impor:historis-2024
        {tahap : bersihkan-stok-2026 | bersihkan-uji | pajak-lib | stand-meter | pengadaan | saldo-awal | stok | penjualan | keuangan | bonus}
        {--tahun=2024 : tahun data (CSV di storage/app/impor-<tahun>)}
        {--dry-run : jalankan lalu rollback, hanya tampilkan hasil}
        {--ganti : (saldo-awal) ganti jurnal saldo awal yang sudah ada; (stok) bangun ulang data stok tahun itu}';

    protected $description = 'Impor data historis (Excel docs/data/<tahun>) per tahap';

    private const NOMOR_SPBU = '2434520';
    private const SUPERADMIN = 'superadmin';
    private const TANGGAL_SALDO_AWAL = '2023-12-31';
    private const SALDO_AWAL_BANK = 1000000000;
    private const NO_REKENING = '2434520';
    private const NAMA_REKENING = 'PT. MGS';
    private const AKUN_UTANG_DSP = '2-1300';

    private ?string $memberId = null;

    private int $tahun = 2024;

    /** Penanda keterangan data impor tahun ini, mis. '[Impor 2025]'. */
    private function penanda(): string
    {
        return "[Impor {$this->tahun}]";
    }

    /** Kode order sisa tahun lalu (LO awal tahun yang sudah dibayar tahun lalu). */
    private function carry(): string
    {
        return 'CARRY-OVER-' . ($this->tahun - 1);
    }

    /** Rentang tanggal tahun impor. */
    private function rentang(): array
    {
        return ["{$this->tahun}-01-01", "{$this->tahun}-12-31"];
    }

    public function handle(): int
    {
        $tahap = $this->argument('tahap');
        $metode = match ($tahap) {
            'bersihkan-stok-2026' => 'bersihkanStok2026',
            'bersihkan-uji' => 'bersihkanUji',
            'pajak-lib' => 'pajakLib',
            'saldo-awal' => 'saldoAwal',
            'stand-meter' => 'standMeter',
            'pengadaan' => 'pengadaan',
            'stok' => 'stok',
            'penjualan' => 'penjualan',
            'keuangan' => 'keuangan',
            'bonus' => 'bonus',
            default => null,
        };
        if (is_null($metode)) {
            $this->error("Tahap \"$tahap\" tidak dikenal.");

            return self::FAILURE;
        }

        $this->tahun = (int) $this->option('tahun');
        if ($this->tahun < 2024 || $this->tahun > 2030) {
            $this->error("Tahun {$this->tahun} tidak didukung.");

            return self::FAILURE;
        }
        if ($this->tahun !== 2024 && in_array($tahap, ['bersihkan-stok-2026', 'saldo-awal'], true)) {
            $this->error("Tahap $tahap hanya untuk impor 2024 (cut-over); tahun berikutnya melanjutkan buku 2024.");

            return self::FAILURE;
        }
        $this->info('Database: ' . DB::connection()->getDatabaseName() . " · tahun {$this->tahun}" . ($this->option('dry-run') ? '  (DRY RUN)' : ''));
        $mulai = microtime(true);

        DB::beginTransaction();
        try {
            // Tabel yang baru dikosongkan punya statistik 0 baris, sehingga rencana cek
            // foreign key (di-cache per sesi) memakai seq scan; saat tabel tumbuh dalam
            // transaksi besar ini tiap INSERT jadi O(n). Paksa pemakaian indeks.
            DB::statement('SET LOCAL enable_seqscan = off');
            $this->{$metode}();
            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn('Dry run: semua perubahan di-rollback.');
            } else {
                DB::commit();
                $this->info('Selesai, perubahan disimpan.');
                $this->segarkanStatistik($tahap);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal, semua perubahan tahap ini di-rollback: ' . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());

            return self::FAILURE;
        }
        $this->line(sprintf('Waktu: %.1f detik', microtime(true) - $mulai));

        return self::SUCCESS;
    }

    // ------------------------------------------------------------ tahap 1

    /** Hapus dokumen_stok 2026 yang rusak (stok_akhir berisi angka totalisator). */
    private function bersihkanStok2026(): void
    {
        $ids = DokumenStok::where('tanggal', '>=', '2026-01-01')->pluck('dokumen_stok_id');
        $this->line('dokumen_stok >= 2026-01-01: ' . $ids->count());

        $dependen = [
            'losses', 'konversi_dokumen_stok', 'file_dokumen_stok', 'detail_tera', 'penerimaan',
            'dombak_transfer_dokumen_stok', 'penerimaan_stok_taking', 'penerimaan_stok_taking_penyesuaian',
        ];
        foreach ($dependen as $t) {
            $n = $ids->chunk(1000)->sum(fn ($c) => DB::table($t)->whereIn('dokumen_stok_id', $c)->count());
            if ($n > 0) {
                throw new RuntimeException("Tabel $t punya $n baris yang menunjuk dokumen ini; dibatalkan agar tidak ikut terhapus.");
            }
        }

        $pivot = $ids->chunk(1000)->sum(fn ($c) => DB::table('stand_meter_dokumen_stok')->whereIn('dokumen_stok_id', $c)->delete());
        $dok = $ids->chunk(1000)->sum(fn ($c) => DB::table('dokumen_stok')->whereIn('dokumen_stok_id', $c)->delete());
        $this->line("Terhapus: stand_meter_dokumen_stok=$pivot, dokumen_stok=$dok");
    }

    /**
     * Hapus jurnal uji coba aplikasi yang sumbernya sudah tidak ada (faktur/pembayaran tanpa rencana),
     * mis. JRN-202608-00001/00002 (faktur A-20260813-1, 13 Agu 2026). Keputusan pemilik 2026-11-12.
     */
    private function bersihkanUji(): void
    {
        $yatim = Jurnal::whereIn('tipe_sumber', ['pembelian', 'pembayaran'])
            ->whereBetween('tanggal', $this->rentang())
            ->where(function ($q) {
                $q->where(fn ($a) => $a->where('tipe_sumber', 'pembelian')
                    ->whereNotIn('sumber_id', DB::table('pajak_pembelian')->select('pajak_pembelian_id')))
                    ->orWhere(fn ($b) => $b->where('tipe_sumber', 'pembayaran')
                        ->whereNotIn('sumber_id', DB::table('pembayaran')->select('pembayaran_id')));
            })->get();
        if ($yatim->count() > 5) {
            throw new RuntimeException('Jurnal yatim ' . $yatim->count() . ' baris (> 5); periksa manual.');
        }
        foreach ($yatim as $j) {
            $nilai = DB::table('jurnal_detail')->where('jurnal_id', $j->jurnal_id)->sum('debit');
            $this->line("hapus {$j->nomor_jurnal} {$j->tanggal} {$j->tipe_sumber}: {$j->keterangan} (Rp " . number_format($nilai, 0, ',', '.') . ')');
            DB::table('jurnal_detail')->where('jurnal_id', $j->jurnal_id)->delete();
            DB::table('jurnal')->where('jurnal_id', $j->jurnal_id)->delete();
        }
        $this->line('jurnal yatim dihapus: ' . $yatim->count());
    }

    /** Samakan tarif master dengan faktur Pertamina 2024 (Pertalite/Biosolar). */
    private function pajakLib(): void
    {
        $lib = PajakPembelianLib::where('is_aktif', 1)->first() ?? PajakPembelianLib::first();
        if (is_null($lib)) {
            throw new RuntimeException('Tabel pajak_pembelian_lib kosong.');
        }
        $this->line("Sebelum: PPN {$lib->ppn} · PPBKB {$lib->ppbkb} · PPh {$lib->pph}");
        $lib->update(['ppn' => 11, 'ppbkb' => 5, 'pph' => 0.25, 'is_aktif' => 1]);
        $lib->refresh();
        $this->line("Sesudah: PPN {$lib->ppn} · PPBKB {$lib->ppbkb} · PPh {$lib->pph}");
    }

    /**
     * Stand meter 2024 dicatat sebagai riwayat terkonfirmasi, dengan dombak per
     * baris sesuai pemakaian 2024. Good Issue Sales dibentuk di tahap stok.
     */
    private function standMeter(): void
    {
        $master = new MasterResolver();
        $rows = $this->bacaCsv('stand_meter.csv');
        $baru = 0;
        $ada = 0;
        $seragam = 0;
        foreach ($rows as $r) {
            $nozzle = $master->nozzle($r['nozzle']);
            $dombak = $master->dombak($r['dombak']);
            $jam = $master->jamKerja($r['jam_kerja']);

            $sudah = StandMeter::where('nozzle_id', $nozzle->nozzle_id)
                ->where('jam_kerja_id', $jam->jam_kerja_id)
                ->where('tanggal', $r['tanggal'])
                ->first();
            if ($sudah) {
                // 2026: Jan-Mei sudah diinput lewat aplikasi (belum dikonfirmasi). Angkanya harus sama dengan
                // Excel; dombak diseragamkan dengan Excel (keputusan pemilik) lalu dikonfirmasi.
                if (abs(floatval($sudah->stand_meter_awal) - floatval($r['stand_meter_awal'])) > 0.005
                    || abs(floatval($sudah->stand_meter_akhir) - floatval($r['stand_meter_akhir'])) > 0.005) {
                    throw new RuntimeException("Stand meter {$r['tanggal']} {$r['jam_kerja']} {$r['nozzle']} di DB "
                        . "{$sudah->stand_meter_awal}-{$sudah->stand_meter_akhir} berbeda dengan Excel "
                        . "{$r['stand_meter_awal']}-{$r['stand_meter_akhir']} ({$r['sumber']}).");
                }
                if ($sudah->dombak_id !== $dombak->dombak_id || (string) $sudah->is_confirmation !== '1') {
                    DB::table('stand_meter')->where('stand_meter_id', $sudah->stand_meter_id)
                        ->update(['dombak_id' => $dombak->dombak_id, 'is_confirmation' => 1]);
                    $seragam++;
                }
                $ada++;
                continue;
            }
            StandMeter::create([
                'nozzle_id' => $nozzle->nozzle_id,
                'dombak_id' => $dombak->dombak_id,
                'jam_kerja_id' => $jam->jam_kerja_id,
                'tanggal' => $r['tanggal'],
                'stand_meter_awal' => $r['stand_meter_awal'],
                'stand_meter_akhir' => $r['stand_meter_akhir'],
                'urutan' => $jam->urutan,
                'is_confirmation' => 1,
            ]);
            $baru++;
        }
        $this->line("stand_meter baru: $baru, sudah ada: $ada (diseragamkan dombak/konfirmasi: $seragam), total CSV: " . count($rows));
    }

    // ------------------------------------------------------------ pengadaan

    /**
     * Rencana -> detail per LO -> faktur -> pembayaran -> MS2 -> pengiriman -> LO.
     * Order carry-over 2023 tidak diberi faktur/pembayaran: nilainya masuk saldo
     * awal sebagai persediaan dalam perjalanan (sudah dibayar Desember 2023).
     */
    private function pengadaan(): void
    {
        if (RencanaPembelian::where('keterangan', 'like', $this->penanda() . '%')->exists()) {
            throw new RuntimeException("Data pengadaan impor {$this->tahun} sudah ada.");
        }

        $spbu = $this->spbu();
        $member = $this->superadmin();
        $statusMs2 = StatusPurchase::where('nama_status', 'MS2')->firstOrFail();
        $lib = PajakPembelianLib::where('is_aktif', 1)->firstOrFail();
        $produk = Produk::where('spbu_id', $spbu->spbu_id)->get()->keyBy('nama_produk');
        $jam = JamKerja::get()->keyBy('nama_jam_kerja');

        $orders = $this->bacaCsv('order.csv');
        $los = $this->bacaCsv('lo.csv');

        // shift penerimaan pertama per LO -> jam_kerja di detail_rencana_pembelian_ms2
        $shiftLo = [];
        foreach ($this->bacaCsv('penerimaan.csv') as $p) {
            $shiftLo[$p['no_lo']] ??= $p['jam_kerja'];
        }

        // kelompokkan order per rencana (kunci extractor) & LO per (kunci, produk)
        $rencana = [];
        foreach ($orders as $o) {
            $rencana[$o['kode']]['tanggal'] = $rencana[$o['kode']]['tanggal'] ?? $o['tanggal'];
            $rencana[$o['kode']]['detail'][] = $o;
        }
        $loPer = [];
        foreach ($los as $l) {
            $loPer[$l['kode_order']][$l['produk']][] = $l;
        }
        // carry-over 2024: rencana tanpa faktur, isinya LO yang ditempel ke CARRY-OVER-2023.
        // Tahun berikutnya: LO itu mengisi baris order tahun lalu yang belum terkirim.
        $sisaLalu = [];
        if ($this->tahun > 2024 && ! empty($loPer[$this->carry()])) {
            $sisaLalu = $loPer[$this->carry()];
            unset($loPer[$this->carry()]);
        }
        if (! empty($loPer[$this->carry()])) {
            $det = [];
            foreach ($loPer[$this->carry()] as $nama => $xs) {
                $det[] = ['kode' => $this->carry(), 'produk' => $nama, 'kl' => array_sum(array_column($xs, 'kl')),
                    'net' => 0, 'ppn' => 0, 'ppbkb' => 0, 'pph' => 0, 'total' => 0, 'no_cek_bg' => '-', 'sumber' => 'carry-over'];
            }
            $rencana = [$this->carry() => ['tanggal' => self::TANGGAL_SALDO_AWAL, 'detail' => $det]] + $rencana;
        }
        uksort($rencana, fn ($a, $b) => strcmp($rencana[$a]['tanggal'] . $a, $rencana[$b]['tanggal'] . $b));

        $hargaLiter = $this->hargaNetPerLiter($orders);
        $urutHari = [];
        $baris = [];       // no_lo -> ['detail' => id, 'ms2' => id]
        $hitung = ['rencana' => 0, 'detail' => 0, 'faktur' => 0, 'pembayaran' => 0, 'ms2' => 0];
        if ($sisaLalu) {
            $baris = $this->pasangSisaTahunLalu($sisaLalu, $produk, $jam, $shiftLo, $member);
        }

        foreach ($rencana as $kunci => $r) {
            $tgl = $r['tanggal'];
            $n = $urutHari[$tgl] = ($urutHari[$tgl] ?? 0) + 1;
            $kode = 'A-' . str_replace('-', '', $tgl) . '-' . $n;
            $carry = $kunci === $this->carry();
            $waktu = Carbon::parse($tgl . ' 08:00:00')->addMinutes($n);

            // gabungkan detail per produk (LO dipasangkan per rencana+produk oleh extractor)
            $perProduk = [];
            foreach ($r['detail'] as $d) {
                $x = &$perProduk[$d['produk']];
                $x['kl'] = ($x['kl'] ?? 0) + floatval($d['kl']);
                $x['net'] = ($x['net'] ?? 0) + floatval($d['net']);
                unset($x);
            }

            // baris detail: satu per LO, sisa order yang belum terkirim jadi satu baris tanpa LO.
            // Carry-over: harga 0 karena nilainya sudah ada di saldo awal (dibayar Desember 2023),
            // supaya layar pembayaran tidak menampilkannya sebagai tagihan terbuka.
            $lines = [];
            $tanpaBongkar = [];
            foreach ($perProduk as $nama => $d) {
                $p = $produk[$nama] ?? throw new RuntimeException("Produk $nama tidak ada.");
                $hl = $carry ? 0.0 : ($d['kl'] > 0 ? $d['net'] / ($d['kl'] * 1000) : $hargaLiter[$nama]);
                $terkirim = 0;
                foreach ($loPer[$kunci][$nama] ?? [] as $l) {
                    if (($l['status'] ?? '') === 'tanpa_penerimaan') {
                        // ikuti stok dombak: LO tanpa bongkaran yang menaikkan dipping = belum diterima,
                        // literannya tetap jadi sisa order (sudah dibayar, belum terkirim); nomor LO dicatat
                        $tanpaBongkar[] = $l['no_lo'];
                        continue;
                    }
                    $lines[] = ['produk' => $p, 'liter' => floatval($l['kl']) * 1000, 'harga' => $hl, 'lo' => $l];
                    $terkirim += floatval($l['kl']);
                }
                $sisa = $d['kl'] - $terkirim;
                if (! $carry && $sisa > 0.0005) {
                    $lines[] = ['produk' => $p, 'liter' => $sisa * 1000, 'harga' => $hl, 'lo' => null];
                }
            }
            $grand = array_sum(array_map(fn ($x) => round($x['liter'] * $x['harga'], 6), $lines));
            $ref = $carry ? 'Sisa pesanan Desember 2023, sudah dibayar 2023; LO diterima Januari 2024. Nilai tercatat di saldo awal persediaan'
                : (str_starts_with($kunci, 'ONLINE-') ? 'Pembayaran online' : str_replace(['TRX-', 'SO-', 'BG-'], ['TRX ID ', 'SO ', 'BG '], $kunci));
            if ($tanpaBongkar) {
                $ref .= '; LO tanpa bongkaran di dombak (perlu dicek): ' . implode(', ', $tanpaBongkar);
                $hitung['lo_tanpa_bongkar'] = ($hitung['lo_tanpa_bongkar'] ?? 0) + count($tanpaBongkar);
            }

            $ren = new RencanaPembelian([
                'status_purchase_id' => $statusMs2->status_purchase_id,
                'spbu_id' => $spbu->spbu_id,
                'member_id' => $member,
                'keterangan' => $this->penanda() . ' ' . $ref,
                'keterangan_denied' => null,
                'pembelian_id' => null,
                'kode_pembelian' => $kode,
                'tanggal_pembelian' => $tgl,
                'grand_total' => $grand,
                'is_confirmation' => 1,
                'is_denied' => 0,
            ]);
            $ren->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();
            $hitung['rencana']++;

            $ms2 = new Ms2(['member_id' => $member, 'tanggal' => $tgl, 'kode_sms' => 'MS2-' . substr(str_replace('-', '', $tgl), 2) . '-' . $n, 'is_done' => 1]);
            $ms2->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();
            $hitung['ms2']++;

            foreach ($lines as $i => $x) {
                $det = new DetailRencanaPembelian([
                    'rencana_pembelian_id' => $ren->rencana_pembelian_id,
                    'produk_id' => $x['produk']->produk_id,
                    'jumlah' => $x['liter'],
                    'satuan_jumlah' => 'Liter',
                    'rit' => ceil($x['liter'] / 8000),
                    'harga' => round($x['harga'], 6),
                    'total_harga' => round($x['liter'] * $x['harga'], 6),
                    'is_confirmation' => 0,
                    'ms2' => 0,
                    'delivery' => $x['lo'] ? 1 : 0,
                    'kode_detail' => $kode . '-' . ($i + 1),
                ]);
                $det->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();
                $hitung['detail']++;
                if ($x['lo']) {
                    $jk = $jam[$shiftLo[$x['lo']['no_lo']] ?? 'Shift 1'] ?? $jam['Shift 1'];
                    $link = new DetailRencanaPembelianMs2([
                        'ms2_id' => $ms2->ms2_id,
                        'detail_rencana_pembelian_id' => $det->detail_rencana_pembelian_id,
                        'jam_kerja_id' => $jk->jam_kerja_id,
                        'is_delivery' => 1,
                    ]);
                    $link->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();
                    $baris[$x['lo']['no_lo']] = ['detail' => $det->detail_rencana_pembelian_id, 'ms2' => $ms2->ms2_id, 'lo' => $x['lo'], 'produk' => $x['produk']];
                }
            }

            if ($carry) {
                continue;
            }
            // faktur = nilai faktur Excel; dibuat setelah detail & tanggal_pembelian terisi
            $tot = ['net' => 0, 'ppn' => 0, 'ppbkb' => 0, 'pph' => 0];
            foreach ($r['detail'] as $d) {
                foreach ($tot as $k => $v) {
                    $tot[$k] = $v + floatval($d[$k]);
                }
            }
            $total = $tot['net'] + $tot['ppn'] + $tot['ppbkb'] + $tot['pph'];
            $pajak = new PajakPembelian([
                'rencana_pembelian_id' => $ren->rencana_pembelian_id,
                'pajak_pembelian_lib_id' => $lib->pajak_pembelian_lib_id,
                'net_price' => $tot['net'],
                'ppn' => $tot['ppn'],
                'ppbkb' => $tot['ppbkb'],
                'pph' => $tot['pph'],
                'total_amount' => $total,
                'metode_posting_pajak' => 'hpp',
            ]);
            $pajak->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();   // -> jurnal pembelian
            $hitung['faktur']++;

            $bayar = new Pembayaran([
                'rencana_pembelian_id' => $ren->rencana_pembelian_id,
                'member_id' => $member,
                'tanggal' => $tgl,
                'no_cek_bg' => $r['detail'][0]['no_cek_bg'] ?: 'Online',
                'no_rekening' => self::NO_REKENING,
                'nama_rekening' => self::NAMA_REKENING,
                'total_setoran' => $total,
                'is_confirmation_admin' => 1,
                'is_confirmation_top' => 1,
            ]);
            $bayar->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();    // -> jurnal pembayaran
            $hitung['pembayaran']++;
        }

        // pengiriman per truk (tanggal kirim + nopol); LO tanpa nopol = satu pengiriman sendiri
        $truk = [];
        foreach ($baris as $no => $b) {
            $l = $b['lo'];
            $nopol = strtoupper(preg_replace('/\s+/', ' ', trim($l['no_pol'])));
            $kunci = $nopol !== '' ? $l['tanggal_kirim'] . '|' . $nopol : 'LO|' . $no;
            $truk[$kunci][] = $no;
        }
        $nKirim = 0;
        $nLo = 0;
        $nSupir = 0;
        foreach ($truk as $kunci => $nomor) {
            $l0 = $baris[$nomor[0]]['lo'];
            $tglKirim = $l0['tanggal_kirim'];
            $kirim = new Pengiriman([
                'member_id' => $member,
                'tanggal' => $tglKirim,
                'no_do' => null,
                'no_pol' => mb_substr(trim($l0['no_pol']) !== '' ? trim($l0['no_pol']) : '-', 0, 16),
                'is_confirmation_admin' => 1,
                'is_confirmation_top' => 1,
                'is_done' => 1,
                'catatan' => $this->penanda() . ' dari Excel',
            ]);
            $kirim->save();
            $nKirim++;

            $namaSupir = [];
            foreach ($nomor as $no) {
                foreach (preg_split('/\s*(?:&|,|\/|\bdan\b)\s*/i', $baris[$no]['lo']['supir']) as $s) {
                    $s = trim($s);
                    if ($s !== '' && ! in_array(mb_strtoupper($s), array_map('mb_strtoupper', $namaSupir), true)) {
                        $namaSupir[] = $s;
                    }
                }
            }
            foreach ($namaSupir as $s) {
                Supir::create(['pengiriman_id' => $kirim->pengiriman_id, 'nama_supir' => $s]);
                $nSupir++;
            }

            $ms2Ids = [];
            foreach ($nomor as $no) {
                $b = $baris[$no];
                $lo = new FileLo([
                    'pengiriman_id' => $kirim->pengiriman_id,
                    'produk_id' => $b['produk']->produk_id,
                    'jumlah' => floatval($b['lo']['kl']) * 1000,
                    'satuan_jumlah' => 'Liter',
                    'no_faktur' => '-',
                    'no_lo' => mb_substr($no, 0, 16),
                    'nama_dokumen' => '-',
                    'nama_file' => 'default.png',
                    'tanggal_upload' => $b['lo']['tanggal_terima'],
                ]);
                $w = Carbon::parse($b['lo']['tanggal_terima'] . ' 07:00:00');
                $lo->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                DB::table('detail_rencana_file')->insert([
                    'detail_rencana_pembelian_id' => $b['detail'],
                    'file_lo_id' => $lo->file_lo_id,
                ]);
                $ms2Ids[$b['ms2']] = true;
                $nLo++;
            }
            foreach (array_keys($ms2Ids) as $id) {
                DB::table('pengiriman_ms2')->insert(['pengiriman_id' => $kirim->pengiriman_id, 'ms2_id' => $id]);
            }
        }

        foreach ($hitung as $k => $v) {
            $this->line(str_pad($k, 12) . ": $v");
        }
        $this->line("pengiriman  : $nKirim (supir $nSupir)");
        $this->line("file_lo     : $nLo");
        $this->line('jurnal      : ' . Jurnal::whereIn('tipe_sumber', ['pembelian', 'pembayaran'])->whereYear('tanggal', $this->tahun)->count() . " (pembelian + pembayaran {$this->tahun})");
    }

    /**
     * LO awal tahun milik order tahun lalu (sisa order 31 Des, sudah dibayar): diisikan ke baris
     * order tahun lalu yang belum terkirim (delivery 0), urut tanggal order. Baris sisa dipecah:
     * bagian LO menjadi baris terkirim baru, sisanya tetap terbuka. LO yang tidak kebagian baris
     * terbuka ditempel ke order terakhir tahun lalu produk itu. Faktur tahun lalu tidak berubah.
     *
     * @return array no_lo => ['detail' => id, 'ms2' => id, 'lo' => baris lo.csv, 'produk' => Produk]
     */
    private function pasangSisaTahunLalu(array $perProduk, $produk, $jam, array $shiftLo, string $member): array
    {
        $penandaLalu = '[Impor ' . ($this->tahun - 1) . ']';
        $baris = [];
        $rencanaUbah = [];
        foreach ($perProduk as $nama => $xs) {
            $p = $produk[$nama] ?? throw new RuntimeException("Produk $nama tidak ada.");
            usort($xs, fn ($a, $b) => strcmp($a['tanggal_terima'] . $a['no_lo'], $b['tanggal_terima'] . $b['no_lo']));
            $dasar = fn () => DetailRencanaPembelian::query()
                ->join('rencana_pembelian as r', 'r.rencana_pembelian_id', '=', 'detail_rencana_pembelian.rencana_pembelian_id')
                ->where('r.keterangan', 'like', $penandaLalu . '%')
                ->where('detail_rencana_pembelian.produk_id', $p->produk_id)
                ->select('detail_rencana_pembelian.*', 'r.kode_pembelian', 'r.tanggal_pembelian');
            foreach ($xs as $l) {
                $liter = floatval($l['kl']) * 1000;
                // utamakan baris sisa yang cukup menampung LO (backlog akhir tahun); sisa kecil
                // FIFO (mis. 7 L April) jangan dibesarkan jadi 8 KL karena menambah liter beli
                // backlog akhir tahun = order Desember; baris terbuka lebih lama (mis. order 2025 yang LO-nya
                // tidak pernah dibongkar) bukan tempat LO awal tahun
                $buka = $dasar()->where('detail_rencana_pembelian.delivery', '0')->where('detail_rencana_pembelian.jumlah', '>=', $liter - 0.5)
                    ->where('r.tanggal_pembelian', '>=', ($this->tahun - 1) . '-12-01')
                    ->orderBy('r.tanggal_pembelian')->orderBy('detail_rencana_pembelian.kode_detail')->first()
                    ?? $dasar()->where('detail_rencana_pembelian.delivery', '0')->where('detail_rencana_pembelian.jumlah', '>=', $liter - 0.5)
                    ->orderBy('r.tanggal_pembelian')->orderBy('detail_rencana_pembelian.kode_detail')->first()
                    ?? $dasar()->where('detail_rencana_pembelian.delivery', '0')->where('detail_rencana_pembelian.jumlah', '>', 0.5)
                        ->orderByDesc('detail_rencana_pembelian.jumlah')->first();
                $acuan = $buka ?? $dasar()->orderByDesc('r.tanggal_pembelian')->orderByDesc('detail_rencana_pembelian.kode_detail')->first()
                    ?? throw new RuntimeException("Tidak ada order $nama tahun lalu untuk LO {$l['no_lo']}.");
                $w = Carbon::parse(substr($acuan->tanggal_pembelian, 0, 10) . ' 08:00:00');
                if ($buka && floatval($buka->jumlah) <= $liter + 0.5) {
                    // baris sisa habis: jadi baris LO
                    DB::table('detail_rencana_pembelian')->where('detail_rencana_pembelian_id', $buka->detail_rencana_pembelian_id)
                        ->update(['jumlah' => $liter, 'total_harga' => round($liter * floatval($buka->harga), 6), 'delivery' => '1',
                            'rit' => ceil($liter / 8000)]);
                    $detId = $buka->detail_rencana_pembelian_id;
                } else {
                    if ($buka) {
                        $sisa = floatval($buka->jumlah) - $liter;
                        DB::table('detail_rencana_pembelian')->where('detail_rencana_pembelian_id', $buka->detail_rencana_pembelian_id)
                            ->update(['jumlah' => $sisa, 'total_harga' => round($sisa * floatval($buka->harga), 6), 'rit' => ceil($sisa / 8000)]);
                    }
                    $ke = DetailRencanaPembelian::where('rencana_pembelian_id', $acuan->rencana_pembelian_id)->count() + 1;
                    $det = new DetailRencanaPembelian([
                        'rencana_pembelian_id' => $acuan->rencana_pembelian_id,
                        'produk_id' => $p->produk_id,
                        'jumlah' => $liter,
                        'satuan_jumlah' => 'Liter',
                        'rit' => ceil($liter / 8000),
                        'harga' => floatval($acuan->harga),
                        'total_harga' => round($liter * floatval($acuan->harga), 6),
                        'is_confirmation' => 0,
                        'ms2' => 0,
                        'delivery' => 1,
                        'kode_detail' => $acuan->kode_pembelian . '-' . $ke,
                    ]);
                    $det->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                    $detId = $det->detail_rencana_pembelian_id;
                }
                $rencanaUbah[$acuan->rencana_pembelian_id] = true;

                $ms2 = DB::table('detail_rencana_pembelian_ms2 as x')
                    ->join('detail_rencana_pembelian as d', 'd.detail_rencana_pembelian_id', '=', 'x.detail_rencana_pembelian_id')
                    ->where('d.rencana_pembelian_id', $acuan->rencana_pembelian_id)->value('x.ms2_id');
                if (is_null($ms2)) {
                    $tgl = substr($acuan->tanggal_pembelian, 0, 10);
                    $m = new Ms2(['member_id' => $member, 'tanggal' => $tgl, 'kode_sms' => 'MS2-' . substr(str_replace('-', '', $tgl), 2) . '-S', 'is_done' => 1]);
                    $m->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                    $ms2 = $m->ms2_id;
                }
                $jk = $jam[$shiftLo[$l['no_lo']] ?? 'Shift 1'] ?? $jam['Shift 1'];
                $link = new DetailRencanaPembelianMs2([
                    'ms2_id' => $ms2,
                    'detail_rencana_pembelian_id' => $detId,
                    'jam_kerja_id' => $jk->jam_kerja_id,
                    'is_delivery' => 1,
                ]);
                $link->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                $baris[$l['no_lo']] = ['detail' => $detId, 'ms2' => $ms2, 'lo' => $l, 'produk' => $p];
            }
        }
        foreach (array_keys($rencanaUbah) as $id) {
            DB::table('rencana_pembelian')->where('rencana_pembelian_id', $id)->update([
                'grand_total' => DB::table('detail_rencana_pembelian')->where('rencana_pembelian_id', $id)->sum('total_harga'),
            ]);
        }
        $this->line('LO sisa order ' . ($this->tahun - 1) . ': ' . count($baris) . ' LO ke ' . count($rencanaUbah) . ' rencana tahun lalu');

        return $baris;
    }

    // ------------------------------------------------------------ saldo awal

    /**
     * Jurnal pembuka per 31-12-2023, diseimbangkan ke Modal Disetor:
     * - Bank Rp 1 miliar (keputusan pemilik);
     * - Persediaan BBM = stok tangki awal 1 Jan 2024 + LO carry-over (sudah dibayar
     *   Desember 2023, diterima Januari), dinilai dengan biaya per liter faktur 2024
     *   (net + PPN + PPBKB, sesuai metode posting 'hpp').
     */
    private function saldoAwal(): void
    {
        $spbu = $this->spbu();
        $lama = Jurnal::where('tipe_sumber', 'saldo_awal')->where('sumber_id', $spbu->spbu_id)->first();
        if ($lama && ! $this->option('ganti')) {
            throw new RuntimeException("Saldo awal SPBU ini sudah ada ({$lama->nomor_jurnal}); pakai --ganti untuk menggantinya.");
        }

        $biaya = $this->biayaPerLiter($this->bacaCsv('order.csv'));
        $produkDombak = ['DM1-PX' => 'Pertamax', 'DM2-PL (Lama)' => 'Pertalite', 'DM3-PL (Baru)' => 'Pertalite',
            'DM4-BS' => 'Bio Solar', 'DM6-PDX' => 'Pertamina Dex'];

        $rincian = [];
        $persediaan = 0;
        $sudah = [];
        $awalTahun = array_filter($this->bacaCsv('stok_shift.csv'), fn ($r) => $r['tanggal'] === '2024-01-01' && $r['stok_awal'] !== '');
        usort($awalTahun, fn ($a, $b) => $a['urutan'] <=> $b['urutan']);
        foreach ($awalTahun as $r) {
            if (isset($sudah[$r['dombak']])) {
                continue;
            }
            $sudah[$r['dombak']] = true;
            $p = $produkDombak[$r['dombak']];
            $nilai = floatval($r['stok_awal']) * $biaya[$p];
            $persediaan += $nilai;
            $rincian[] = sprintf('  stok %-14s %10s L x %8s = Rp %s', $r['dombak'], number_format(floatval($r['stok_awal']), 1, ',', '.'),
                number_format($biaya[$p], 2, ',', '.'), number_format($nilai, 0, ',', '.'));
        }
        $carry = [];
        foreach ($this->bacaCsv('lo.csv') as $l) {
            if ($l['kode_order'] === $this->carry()) {
                $carry[$l['produk']] = ($carry[$l['produk']] ?? 0) + floatval($l['kl']) * 1000;
            }
        }
        foreach ($carry as $p => $liter) {
            $nilai = $liter * $biaya[$p];
            $persediaan += $nilai;
            $rincian[] = sprintf('  LO carry-over %-10s %10s L x %8s = Rp %s', $p, number_format($liter, 0, ',', '.'),
                number_format($biaya[$p], 2, ',', '.'), number_format($nilai, 0, ',', '.'));
        }
        $persediaan = round($persediaan, 2);
        $modal = self::SALDO_AWAL_BANK + $persediaan;

        if ($lama) {
            JurnalPosting::batalkan('saldo_awal', $spbu->spbu_id);
            $this->line("Jurnal lama {$lama->nomor_jurnal} dibatalkan.");
        }
        $jurnal = JurnalPosting::post(
            $spbu->spbu_id,
            self::TANGGAL_SALDO_AWAL,
            'saldo_awal',
            $spbu->spbu_id,
            'Saldo awal pembukuan (cut-over impor historis 2024)',
            [
                ['kode_akun' => Akun::BANK, 'debit' => self::SALDO_AWAL_BANK, 'kredit' => 0, 'keterangan' => 'Saldo awal Bank'],
                ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => $persediaan, 'kredit' => 0, 'keterangan' => 'Saldo awal Persediaan BBM (tangki + LO dalam perjalanan)'],
                ['kode_akun' => Akun::MODAL_DISETOR, 'debit' => 0, 'kredit' => $modal, 'keterangan' => 'Penyeimbang saldo awal'],
            ],
            $this->superadmin()
        );
        $this->line("Jurnal {$jurnal->nomor_jurnal} tanggal " . self::TANGGAL_SALDO_AWAL);
        $this->line('  Bank            Rp ' . number_format(self::SALDO_AWAL_BANK, 0, ',', '.'));
        $this->line('  Persediaan BBM  Rp ' . number_format($persediaan, 0, ',', '.'));
        foreach ($rincian as $x) {
            $this->line($x);
        }
        $this->line('  Modal Disetor   Rp ' . number_format($modal, 0, ',', '.'));
    }

    // ------------------------------------------------------------ stok

    /**
     * Rantai dokumen stok per dombak per shift (semua terkonfirmasi):
     * 1) penerimaan per alokasi LO: penyelarasan (no-op) -> Good Receive (buku ->
     *    buku + terukur, losses "Good Issue Penerimaan" = terukur - isi LO) ->
     *    penutup (no-op), plus baris penerimaan & tautannya;
     * 2) Good Issue Sales per stand meter (liter <> 0);
     * 3) Stok Taking ke dipping akhir shift (losses "Stok Taking").
     */
    private function stok(): void
    {
        if (DokumenStok::whereYear('tanggal', $this->tahun)->exists()) {
            if (! $this->option('ganti')) {
                throw new RuntimeException("Dokumen stok {$this->tahun} sudah ada (pakai --ganti untuk membangun ulang).");
            }
            $this->hapusStok();
        }
        $member = $this->superadmin();
        $tipe = TypeDokumenStok::get()->keyBy('type_movement');
        foreach (['Good Issue Sales', 'Good Receive Penerimaan', 'Stok Taking', 'Stok Taking Good Receive Penerimaan',
            'Stok Taking Good Receive Penyesuaian', 'Good Transfer Send', 'Good Transfer Receive'] as $t) {
            $tipe[$t] ?? throw new RuntimeException("type_dokumen_stok \"$t\" tidak ada.");
        }
        $jenis = JenisLosses::get()->keyBy('jenis_loss');
        $jam = JamKerja::get()->keyBy('nama_jam_kerja');
        $dombak = Dombak::get()->keyBy('nama_dombak');
        $inventory = StokInventoryDombak::get()->keyBy('dombak_id');
        $fileLo = FileLo::pluck('file_lo_id', 'no_lo');

        // tera (2026): liter yang keluar lewat nozzle untuk tera lalu kembali ke tangki; bukan penjualan
        // (penjualan_detail.csv kolom tera, dari kolom 'Tera' tab Stock Margin). Dicatat Good Issue Tera
        // lalu Good Return Tera di shift yang sama, dengan header tera per hari.
        $teraSm = [];
        if (is_file($this->pathCsv('penjualan_detail.csv'))) {
            foreach ($this->bacaCsv('penjualan_detail.csv') as $x) {
                if (floatval($x['tera'] ?? 0) > 0.0005) {
                    $teraSm[$x['tanggal'] . '|' . $x['jam_kerja'] . '|' . $x['nozzle']] = [floatval($x['tera']), floatval($x['harga_jual'])];
                }
            }
        }
        if ($teraSm) {
            foreach (['Good Issue Tera', 'Good Return Tera'] as $t) {
                $tipe[$t] ?? throw new RuntimeException("type_dokumen_stok \"$t\" tidak ada.");
            }
        }
        $teraHari = [];   // tanggal -> Tera
        $spbuId = $this->spbu()->spbu_id;

        // stand meter 2024 per (tanggal|jam_kerja_id|dombak_id)
        $sm = [];
        StandMeter::whereYear('tanggal', $this->tahun)->join('nozzle', 'nozzle.nozzle_id', '=', 'stand_meter.nozzle_id')
            ->orderBy('nozzle.nama_nozzle')
            ->get(['stand_meter.stand_meter_id', 'stand_meter.tanggal', 'stand_meter.jam_kerja_id', 'stand_meter.dombak_id',
                'stand_meter.stand_meter_awal', 'stand_meter.stand_meter_akhir', 'nozzle.nama_nozzle'])
            ->each(function ($s) use (&$sm) {
                $sm[substr($s->tanggal, 0, 10) . '|' . $s->jam_kerja_id . '|' . $s->dombak_id][] = $s;
            });

        $terima = [];
        foreach ($this->bacaCsv('penerimaan.csv') as $p) {
            $terima[$p['tanggal'] . '|' . $p['jam_kerja'] . '|' . $p['dombak']][] = $p;
        }

        $transfer = [];
        foreach ($this->bacaCsv('transfer.csv') as $t) {
            $transfer[$t['tanggal'] . '|' . $t['jam_kerja']][] = $t;
        }

        // biaya per liter 2024 per produk (harga.csv: harga pokok berlaku sejak tanggal) untuk menilai susut
        $seriBiaya = [];
        foreach ($this->bacaCsv('harga.csv') as $h) {
            $seriBiaya[$h['produk']][] = [$h['tanggal'], floatval($h['harga_beli'])];
        }
        foreach ($seriBiaya as &$xs) {
            usort($xs, fn ($a, $b) => strcmp($a[0], $b[0]));
        }
        unset($xs);
        $biaya = function (string $produk, string $tanggal) use ($seriBiaya): float {
            $xs = $seriBiaya[$produk] ?? throw new RuntimeException("Biaya per liter $produk tidak ada di harga.csv.");
            $nilai = $xs[0][1];
            foreach ($xs as [$t, $b]) {
                if ($t <= $tanggal) {
                    $nilai = $b;
                }
            }

            return $nilai;
        };
        $produkDombak = DB::table('stok_inventory_dombak as sid')
            ->join('stok_inventory as si', 'si.stok_inventory_id', '=', 'sid.stok_inventory_id')
            ->join('produk as p', 'p.produk_id', '=', 'si.produk_id')
            ->pluck('p.nama_produk', 'sid.dombak_id');
        $produkNama = Produk::pluck('produk_id', 'nama_produk');
        $dtId = [];   // tanggal|jam_kerja|produk -> dombak_transfer_id

        // timeline per dombak: gabungan baris stok, stand meter, penerimaan, transfer
        $nomorShift = $jam->mapWithKeys(fn ($j) => [$j->jam_kerja_id => $j->nama_jam_kerja]);
        $timeline = [];
        // stok_dombak.csv (dm5.py: Biosolar dipecah DM4/DM5); stok_shift.csv untuk CSV lama
        $fileStok = is_file($this->pathCsv('stok_dombak.csv')) ? 'stok_dombak.csv' : 'stok_shift.csv';
        foreach ($this->bacaCsv($fileStok) as $r) {
            $timeline[$r['dombak']][$r['tanggal'] . '|' . $r['urutan']] = $r;
        }
        $namaDombakId = $dombak->mapWithKeys(fn ($x) => [$x->dombak_id => $x->nama_dombak]);
        foreach (array_keys($sm) as $k) {
            [$t, $jkId, $dId] = explode('|', $k);
            $nama = $namaDombakId[$dId];
            $u = $jam[$nomorShift[$jkId]]->urutan;
            $timeline[$nama][$t . '|' . $u] ??= ['tanggal' => $t, 'jam_kerja' => $nomorShift[$jkId], 'urutan' => $u, 'dombak' => $nama, 'stok_awal' => '', 'dipping' => ''];
        }
        foreach ($terima as $k => $ps) {
            $p = $ps[0];
            $timeline[$p['dombak']][$p['tanggal'] . '|' . $p['urutan']] ??= ['tanggal' => $p['tanggal'], 'jam_kerja' => $p['jam_kerja'], 'urutan' => $p['urutan'], 'dombak' => $p['dombak'], 'stok_awal' => '', 'dipping' => ''];
        }
        foreach ($transfer as $ts) {
            foreach ($ts as $t) {
                foreach ([$t['asal'], $t['tujuan']] as $nama) {
                    $timeline[$nama][$t['tanggal'] . '|' . $t['urutan']] ??= ['tanggal' => $t['tanggal'], 'jam_kerja' => $t['jam_kerja'], 'urutan' => $t['urutan'], 'dombak' => $nama, 'stok_awal' => '', 'dipping' => ''];
                }
            }
        }

        $n = ['dokumen' => 0, 'gi' => 0, 'terima' => 0, 'transfer' => 0, 'st' => 0, 'losses' => 0];
        $akhir = [];
        foreach ($timeline as $namaDombak => $rows) {
            $d = $dombak[$namaDombak] ?? throw new RuntimeException("Dombak $namaDombak tidak ada.");
            $inv = $inventory[$d->dombak_id] ?? throw new RuntimeException("Dombak $namaDombak belum terhubung ke stok_inventory.");
            ksort($rows);
            $rows = array_values($rows);
            $pertama = collect($rows)->first(fn ($x) => $x['stok_awal'] !== '');
            $buku = floatval($pertama['stok_awal'] ?? 0);

            foreach ($rows as $r) {
                $jk = $jam[$r['jam_kerja']];
                $mulai = Carbon::parse($r['tanggal'] . ' ' . $jk->jam_datang_in);
                $selesai = $mulai->copy()->addHours(8);
                $detik = 0;
                $buat = function (string $jenisDok, float $awal, float $akhirDok, Carbon $w) use (&$detik, &$n, $tipe, $inv, $member, $d, $jk, $r) {
                    $w = $w->copy()->addSeconds($detik++);
                    $dok = new DokumenStok([
                        'stok_inventory_id' => $inv->stok_inventory_id,
                        'member_id' => $member,
                        'dombak_id' => $d->dombak_id,
                        'jam_kerja_id' => $jk->jam_kerja_id,
                        'type_dokumen_stok_id' => $tipe[$jenisDok]->type_dokumen_stok_id,
                        'tanggal' => $r['tanggal'],
                        'stok_awal' => round($awal, 6),
                        'stok_akhir' => round($akhirDok, 6),
                        'is_confirmation' => 1,
                        'waktu' => $w->format('H:i:s'),
                    ]);
                    $dok->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                    $n['dokumen']++;

                    return $dok;
                };
                $rugi = function (DokumenStok $dok, string $namaJenis, float $selisih, Carbon $w) use (&$n, $jenis, $r, $d, $produkDombak, $biaya) {
                    if (abs($selisih) < 0.001) {
                        return;
                    }
                    $j = $jenis[$namaJenis] ?? throw new RuntimeException("jenis_losses \"$namaJenis\" tidak ada.");
                    $simbol = $selisih < 0 ? 'kurang' : 'lebih';
                    $jumlah = round(abs($selisih), 6);
                    $loss = new Losses([
                        'jenis_losses_id' => $j->jenis_losses_id,
                        'dokumen_stok_id' => $dok->dokumen_stok_id,
                        'tanggal' => $r['tanggal'],
                        'simbol' => $simbol,
                        'jumlah' => $jumlah,
                    ]);
                    // Event model dimatikan: aturan aplikasi menilai susut dengan harga beli DEFAULT
                    // (harga berlaku saat ini, net). Untuk riwayat 2024 dipakai biaya per liter periode
                    // itu (net + PPN + PPBKB, sama dengan nilai persediaan & HPP). Akun dan bentuk
                    // jurnal sama persis dengan PostingKeuangan::postLosses.
                    $loss->forceFill(['losses_id' => Uuid::uuid4()->toString(), 'created_at' => $w, 'updated_at' => $w]);
                    Losses::withoutEvents(fn () => $loss->save());
                    $n['losses']++;

                    $produk = $r['tanggal'] < '2024-02-01' && $d->nama_dombak === 'DM1-PX' ? 'Pertalite' : $produkDombak[$d->dombak_id];
                    $nilai = round($jumlah * $biaya($produk, $r['tanggal']), 2);
                    if ($nilai <= 0.0001) {
                        return;
                    }
                    $ket = 'Susut stok (' . $simbol . ') ' . floatval($jumlah) . ' liter';
                    $baris = $simbol === 'kurang'
                        ? [['kode_akun' => Akun::BEBAN_SUSUT_BBM, 'debit' => $nilai, 'kredit' => 0, 'keterangan' => $ket],
                            ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => 0, 'kredit' => $nilai, 'keterangan' => $ket]]
                        : [['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => $nilai, 'kredit' => 0, 'keterangan' => $ket],
                            ['kode_akun' => Akun::BEBAN_SUSUT_BBM, 'debit' => 0, 'kredit' => $nilai, 'keterangan' => $ket]];
                    JurnalPosting::post($d->spbu_id, $r['tanggal'], 'losses', $loss->losses_id, $ket, $baris, null);
                };

                // 1) penerimaan dan pemindahan antar tangki, diproses urut jam kejadian: rantai
                //    saldo harus sama dengan urutan waktu dokumen (riwayat stok aplikasi diurutkan
                //    per tanggal lalu created_at)
                $kejadian = [];
                foreach ($terima[$r['tanggal'] . '|' . $r['jam_kerja'] . '|' . $namaDombak] ?? [] as $p) {
                    $kejadian[] = ['terima', $this->jamBongkar($p['jam'], $mulai, $selesai), $p];
                }
                foreach ($transfer[$r['tanggal'] . '|' . $r['jam_kerja']] ?? [] as $t) {
                    if (in_array($namaDombak, [$t['asal'], $t['tujuan']], true)) {
                        $kejadian[] = ['transfer', $this->jamBongkar($t['jam'], $mulai, $selesai), $t];
                    }
                }
                usort($kejadian, fn ($a, $b) => $a[1] <=> $b[1]);   // usort PHP 8 stabil

                foreach ($kejadian as [$jenisKejadian, $w, $x]) {
                    if ($jenisKejadian === 'transfer') {
                        // pemindahan antar tangki (Good Transfer Send/Receive)
                        $t = $x;
                        $kunci = $t['tanggal'] . '|' . $t['jam_kerja'] . '|' . $t['produk'];
                        if (! isset($dtId[$kunci])) {
                            $dt = new \App\Models\DombakTransfer([
                                'produk_id' => $produkNama[$t['produk']],
                                'jam_kerja_id' => $jk->jam_kerja_id,
                                'tanggal' => $t['tanggal'],
                                'is_confirmation' => 1,
                                'waktu' => $w->format('H:i:s'),
                            ]);
                            $dt->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                            $dtId[$kunci] = $dt->dombak_transfer_id;
                            $n['transfer']++;
                        }
                        // keluar = liter dari tangki asal; masuk = liter yang diterima tangki tujuan;
                        // selisihnya susut pemindahan (losses "Good Transfer" pada dokumen terima)
                        $kirim = $namaDombak === $t['asal'];
                        $q = floatval($kirim ? $t['liter'] : ($t['liter_terima'] ?? $t['liter']));
                        $dok = $buat($kirim ? 'Good Transfer Send' : 'Good Transfer Receive', $buku, $kirim ? $buku - $q : $buku + $q, $w);
                        DB::table('dombak_transfer_dokumen_stok')->insert(['dombak_transfer_id' => $dtId[$kunci], 'dokumen_stok_id' => $dok->dokumen_stok_id]);
                        if (! $kirim) {
                            $rugi($dok, 'Good Transfer', $q - floatval($t['liter']), $w);
                        }
                        $buku += $kirim ? -$q : $q;

                        continue;
                    }
                    $p = $x;
                    $loId = $fileLo[$p['no_lo']] ?? throw new RuntimeException("LO {$p['no_lo']} belum diimpor (jalankan tahap pengadaan).");
                    $masuk = floatval($p['jumlah_terukur']);
                    $taking = $buat('Stok Taking Good Receive Penerimaan', $buku, $buku, $w);
                    $dokTerima = $buat('Good Receive Penerimaan', $buku, $buku + $masuk, $w);
                    $rugi($dokTerima, 'Good Issue Penerimaan', $masuk - floatval($p['jumlah_rencana']), $w);
                    $buku += $masuk;
                    $tutup = $buat('Stok Taking Good Receive Penyesuaian', $buku, $buku, $w);

                    $pen = new Penerimaan(['file_lo_id' => $loId, 'dokumen_stok_id' => $dokTerima->dokumen_stok_id, 'tanggal' => $r['tanggal']]);
                    $pen->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                    PenerimaanStokTaking::create(['penerimaan_id' => $pen->penerimaan_id, 'dokumen_stok_id' => $taking->dokumen_stok_id]);
                    PenerimaanStokTakingPenyesuaian::create(['penerimaan_id' => $pen->penerimaan_id, 'dokumen_stok_id' => $tutup->dokumen_stok_id]);
                    $n['terima']++;
                }

                // 2) penjualan per stand meter
                $wJual = $selesai->copy()->subMinutes(10);
                foreach ($sm[$r['tanggal'] . '|' . $jk->jam_kerja_id . '|' . $d->dombak_id] ?? [] as $s) {
                    $liter = floatval($s->stand_meter_akhir) - floatval($s->stand_meter_awal);
                    if ($liter < 0.0005) {
                        continue;   // nol / minus pembulatan stand meter: bukan penjualan (selisihnya ke stok taking)
                    }
                    [$tera, $hargaTera] = $teraSm[$r['tanggal'] . '|' . $jk->nama_jam_kerja . '|' . $s->nama_nozzle] ?? [0.0, 0.0];
                    $tera = min($tera, $liter);
                    if ($liter - $tera >= 0.0005) {
                        $dok = $buat('Good Issue Sales', $buku, $buku - ($liter - $tera), $wJual);
                        DB::table('stand_meter_dokumen_stok')->insert(['stand_meter_id' => $s->stand_meter_id, 'dokumen_stok_id' => $dok->dokumen_stok_id]);
                        $buku -= $liter - $tera;
                        $n['gi']++;
                    }
                    if ($tera >= 0.0005) {
                        $hdr = $teraHari[$r['tanggal']] ??= Tera::create([
                            'spbu_id' => $spbuId, 'tanggal' => $r['tanggal'], 'grand_total' => 0,
                            'keterangan' => $this->penanda() . ' tera nozzle (kolom Tera tab Stock Margin); BBM kembali ke tangki',
                        ]);
                        $ket = "{$s->nama_nozzle} {$jk->nama_jam_kerja}: " . round($tera, 2) . ' L';
                        foreach ([['Good Issue Tera', -$tera, 'keluar untuk tera'], ['Good Return Tera', $tera, 'kembali dari tera']] as [$jt, $gerak, $ket2]) {
                            $dt = $buat($jt, $buku, $buku + $gerak, $wJual);
                            DetailTera::create(['tera_id' => $hdr->tera_id, 'dokumen_stok_id' => $dt->dokumen_stok_id,
                                'quantity' => round($tera, 6), 'harga' => $hargaTera, 'keterangan' => "$ket $ket2"]);
                            $buku += $gerak;
                        }
                        $hdr->grand_total = round(floatval($hdr->grand_total) + $tera * $hargaTera, 6);
                        $hdr->save();
                        $n['tera'] = ($n['tera'] ?? 0) + 1;
                    }
                }

                // 3) stok taking dipping akhir shift
                if ($r['dipping'] !== '') {
                    $dip = floatval($r['dipping']);
                    $dok = $buat('Stok Taking', $buku, $dip, $selesai);
                    $rugi($dok, 'Stok Taking', $dip - $buku, $selesai);
                    $buku = $dip;
                    $n['st']++;
                }
            }
            $akhir[$namaDombak] = [$buku, end($rows)];
        }

        foreach ($n as $k => $v) {
            $this->line(str_pad($k, 9) . ": $v");
        }
        $this->line("Saldo buku akhir {$this->tahun} per dombak (vs dipping terakhir di Excel):");
        foreach ($akhir as $nama => [$buku, $r]) {
            $this->line(sprintf('  %-14s %12s L  (Excel %s %s: %s)', $nama, number_format($buku, 2, ',', '.'), $r['tanggal'], $r['jam_kerja'], $r['dipping'] === '' ? '-' : number_format(floatval($r['dipping']), 2, ',', '.')));
        }
        $this->line("Jurnal losses {$this->tahun}: " . Jurnal::where('tipe_sumber', 'losses')->whereYear('tanggal', $this->tahun)->count());
    }

    /**
     * Hapus hasil tahap stok 2024 (opsi --ganti), dalam transaksi tahap ini: jurnal losses,
     * losses, penerimaan beserta tautan dokumennya, pemindahan tangki hasil impor (yang
     * tertaut ke dokumen 2024; transfer lain tidak disentuh), tautan stand meter, lalu
     * dokumen stoknya. LO, stand meter, penjualan, dan jurnal lain tidak berubah.
     */
    private function hapusStok(): void
    {
        $dok = fn () => DB::table('dokumen_stok')->select('dokumen_stok_id')->whereBetween('tanggal', $this->rentang());
        $pen = fn () => DB::table('penerimaan')->select('penerimaan_id')->whereIn('dokumen_stok_id', $dok());
        $dt = DB::table('dombak_transfer_dokumen_stok')->whereIn('dokumen_stok_id', $dok())->distinct()->pluck('dombak_transfer_id');

        $n = [];
        $n['jurnal losses'] = DB::table('jurnal')->where('tipe_sumber', 'losses')
            ->whereIn('sumber_id', DB::table('losses')->select('losses_id')->whereIn('dokumen_stok_id', $dok()))->delete();
        $n['losses'] = DB::table('losses')->whereIn('dokumen_stok_id', $dok())->delete();
        $n['penerimaan_stok_taking'] = DB::table('penerimaan_stok_taking')->whereIn('penerimaan_id', $pen())->delete();
        $n['penerimaan_stok_taking_penyesuaian'] = DB::table('penerimaan_stok_taking_penyesuaian')->whereIn('penerimaan_id', $pen())->delete();
        $n['penerimaan'] = DB::table('penerimaan')->whereIn('dokumen_stok_id', $dok())->delete();
        $n['dombak_transfer_dokumen_stok'] = DB::table('dombak_transfer_dokumen_stok')->whereIn('dokumen_stok_id', $dok())->delete();
        $n['dombak_transfer'] = DB::table('dombak_transfer')->whereIn('dombak_transfer_id', $dt)->delete();
        $n['stand_meter_dokumen_stok'] = DB::table('stand_meter_dokumen_stok')->whereIn('dokumen_stok_id', $dok())->delete();
        $teraIds = DB::table('detail_tera')->whereIn('dokumen_stok_id', $dok())->distinct()->pluck('tera_id');
        $n['detail_tera'] = DB::table('detail_tera')->whereIn('dokumen_stok_id', $dok())->delete();
        $n['tera'] = DB::table('tera')->whereIn('tera_id', $teraIds)->where('keterangan', 'like', $this->penanda() . '%')->delete();
        $n['dokumen_stok'] = DB::table('dokumen_stok')->whereBetween('tanggal', $this->rentang())->delete();
        $this->line('Dihapus (--ganti): ' . collect($n)->map(fn ($v, $k) => "$k=$v")->implode(', '));
    }

    // ------------------------------------------------------------ penjualan

    /**
     * Penjualan per shift (status Selesai) + detail per stand meter + setoran bank.
     *
     * Urutan mengikuti aplikasi (API v1) supaya jurnal terbentuk benar:
     * penjualan dibuat dengan persetujuan 0 -> detail -> setoran (langsung diterima,
     * memposting jurnal Bank/Kas Operator) -> total & uang kotak diisi lalu persetujuan
     * admin+top dinaikkan pada instance baru (memposting jurnal penjualan & HPP).
     * Harga 2024 dibuat sebagai riwayat (is_default 0); harga berlaku saat ini tidak diubah.
     * `harga_beli` disimpan setara-net (biaya per liter Excel ÷ faktor kapitalisasi pajak),
     * sama dengan arti harga beli di master, sehingga HPP yang diposting
     * (net × faktor, PostingKeuangan::faktorKapitalisasi) kembali ke biaya Excel.
     */
    private function penjualan(): void
    {
        if (\App\Models\Penjualan::whereYear('tanggal', $this->tahun)->exists()) {
            throw new RuntimeException("Penjualan {$this->tahun} sudah ada.");
        }
        $spbu = $this->spbu();
        $member = $this->superadmin();
        $selesai = \App\Models\StatusSetoran::where('nama_status', 'Selesai')->firstOrFail();
        $produk = Produk::where('spbu_id', $spbu->spbu_id)->get()->keyBy('nama_produk');
        $jam = JamKerja::get()->keyBy('nama_jam_kerja');
        $detail = $this->bacaCsv('penjualan_detail.csv');

        // stand meter 2024: tanggal|jam_kerja_id|nama_nozzle -> id
        $sm = [];
        StandMeter::whereYear('stand_meter.tanggal', $this->tahun)->join('nozzle', 'nozzle.nozzle_id', '=', 'stand_meter.nozzle_id')
            ->get(['stand_meter.stand_meter_id', 'stand_meter.tanggal', 'stand_meter.jam_kerja_id', 'nozzle.nama_nozzle'])
            ->each(function ($s) use (&$sm) {
                $sm[substr($s->tanggal, 0, 10) . '|' . $s->jam_kerja_id . '|' . $s->nama_nozzle] = $s->stand_meter_id;
            });

        // baris harga riwayat: satu per (produk, harga jual, harga pokok), jam_kerja = shift pertama berlaku
        $pertama = [];
        foreach ($detail as $x) {
            $k = $x['produk_harga'] . '|' . $x['harga_jual'] . '|' . $x['harga_beli'];
            $pertama[$k] ??= $x;
        }
        $hargaId = [];
        foreach ($this->bacaCsv('harga.csv') as $h) {
            $k = $h['produk'] . '|' . $h['harga_jual'] . '|' . $h['harga_beli'];
            $x = $pertama[$k] ?? throw new RuntimeException("Harga $k tidak dipakai di detail penjualan.");
            $p = $produk[$h['produk']] ?? throw new RuntimeException("Produk {$h['produk']} tidak ada.");
            $w = Carbon::parse($x['tanggal'] . ' ' . $jam[$x['jam_kerja']]->jam_datang_in);
            $faktor = PostingKeuangan::faktorKapitalisasi($p->produk_id, $x['tanggal']);
            $row = new \App\Models\Harga([
                'produk_id' => $p->produk_id,
                'jam_kerja_id' => $jam[$x['jam_kerja']]->jam_kerja_id,
                'tanggal' => $x['tanggal'],
                'harga_jual' => $h['harga_jual'],
                'harga_beli' => round(floatval($h['harga_beli']) / $faktor, 6),
                'is_default' => 0,
            ]);
            $row->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
            $hargaId[$k] = $row->harga_id;
        }

        $shift = [];
        foreach ($detail as $x) {
            $shift[$x['tanggal'] . '|' . $x['urutan']][] = $x;
        }
        ksort($shift);

        $n = ['harga' => count($hargaId), 'penjualan' => 0, 'detail' => 0, 'setoran' => 0];
        $omzet = 0;
        foreach ($shift as $rows) {
            $x0 = $rows[0];
            $jk = $jam[$x0['jam_kerja']];
            $w = Carbon::parse($x0['tanggal'] . ' ' . $jk->jam_datang_in)->addHours(8);   // akhir shift
            $pj = new \App\Models\Penjualan([
                'status_setoran_id' => $selesai->status_setoran_id,
                'spbu_id' => $spbu->spbu_id,
                'jam_kerja_id' => $jk->jam_kerja_id,
                'tanggal' => $x0['tanggal'],
                'total_penjualan' => 0,
                'total_uang_kotak' => 0,
                'is_accepted_admin' => 0,
                'is_accepted_top' => 0,
            ]);
            $pj->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
            $n['penjualan']++;

            $total = 0.0;
            foreach ($rows as $x) {
                $smId = $sm[$x['tanggal'] . '|' . $jk->jam_kerja_id . '|' . $x['nozzle']]
                    ?? throw new RuntimeException("Stand meter {$x['nozzle']} {$x['tanggal']} {$x['jam_kerja']} tidak ada.");
                $nilai = round(floatval($x['liter']) * floatval($x['harga_jual']), 6);
                $det = new \App\Models\DetailPenjualan([
                    'penjualan_id' => $pj->penjualan_id,
                    'stand_meter_id' => $smId,
                    'harga_id' => $hargaId[$x['produk_harga'] . '|' . $x['harga_jual'] . '|' . $x['harga_beli']],
                    'liter' => $x['liter'],
                    'total_detail_jual' => $nilai,
                    'is_confirmed' => 1,
                ]);
                $det->forceFill(['created_at' => $w, 'updated_at' => $w])->save();
                $total += $nilai;
                $n['detail']++;
            }
            $total = round($total, 6);

            // setoran penuh ke bank di hari yang sama (keputusan pemilik data)
            $setor = new \App\Models\Penarikan([
                'penjualan_id' => $pj->penjualan_id,
                'member_id' => $member,
                'tanggal' => $x0['tanggal'],
                'nama_bank' => 'Mandiri',
                'no_referensi' => 'IMP-' . substr(str_replace('-', '', $x0['tanggal']), 2) . '-S' . $x0['urutan'],
                'metode_transfer' => 'Setor Tunai',
                'total_uang' => $total,
                'is_accepted' => 1,
            ]);
            $setor->forceFill(['created_at' => $w, 'updated_at' => $w])->save();   // -> jurnal setoran
            $n['setoran']++;

            // instance baru supaya transisi persetujuan memicu jurnal penjualan + HPP sekali
            \App\Models\Penjualan::find($pj->penjualan_id)->update([
                'total_penjualan' => $total,
                'total_uang_kotak' => $total,
                'is_accepted_admin' => 1,
                'is_accepted_top' => 1,
            ]);
            $omzet += $total;
        }

        foreach ($n as $k => $v) {
            $this->line(str_pad($k, 10) . ": $v");
        }
        $this->line("Omzet {$this->tahun}  : Rp " . number_format($omzet, 0, ',', '.'));
        foreach (['penjualan', 'penjualan_hpp', 'penarikan'] as $t) {
            $j = Jurnal::where('tipe_sumber', $t)->whereYear('tanggal', $this->tahun);
            $this->line(str_pad("jurnal $t", 22) . ': ' . $j->count());
        }
    }

    // ------------------------------------------------------------ keuangan

    /**
     * Biaya, alokasi, reward dan pembagian laba bulanan sebagai jurnal manual
     * (dibayar dari Bank). Rincian bonus per karyawan ada di tahap `bonus`.
     */
    private function keuangan(): void
    {
        if (Jurnal::where('tipe_sumber', 'manual')->where('keterangan', 'like', $this->penanda() . '%')->exists()) {
            throw new RuntimeException("Jurnal keuangan impor {$this->tahun} sudah ada.");
        }
        $spbu = $this->spbu();
        $member = $this->superadmin();

        $baris = $this->bacaCsv('keuangan_bulanan.csv');

        // Utang DSP (2025): saldo awal per 31 Des tahun lalu = total pelunasan tahun ini, sehingga
        // akhir tahun menjadi 0 (keputusan pemilik). Lawan: Modal Disetor (dana DSP dianggap pinjaman).
        $utang = array_sum(array_map(fn ($x) => floatval($x['nilai']), array_filter($baris, fn ($x) => $x['jenis'] === 'utang')));
        if ($utang > 0) {
            $induk = \App\Models\Coa::where('kode_akun', '2-0000')->value('coa_id');
            \App\Models\Coa::firstOrCreate(['kode_akun' => self::AKUN_UTANG_DSP], [
                'parent_coa_id' => $induk, 'nama_akun' => 'Utang DSP', 'tipe_akun' => 'kewajiban', 'saldo_normal' => 'kredit',
                'kategori_arus_kas' => 'pendanaan', 'is_kas' => false, 'is_header' => false, 'is_aktif' => true,
                'keterangan' => 'Pinjaman pihak DSP, dilunasi bulanan (impor historis)',
            ]);
            $ket = "Saldo awal utang DSP (= total pelunasan {$this->tahun})";
            JurnalPosting::post(
                $spbu->spbu_id,
                ($this->tahun - 1) . '-12-31',
                'manual',
                Uuid::uuid5(Uuid::NAMESPACE_URL, "impor-{$this->tahun}:utang-dsp-awal")->toString(),
                $this->penanda() . ' ' . $ket,
                [
                    ['kode_akun' => Akun::MODAL_DISETOR, 'debit' => $utang, 'kredit' => 0, 'keterangan' => $ket],
                    ['kode_akun' => self::AKUN_UTANG_DSP, 'debit' => 0, 'kredit' => $utang, 'keterangan' => $ket],
                ],
                $member
            );
            $this->line('saldo awal utang DSP ' . ($this->tahun - 1) . '-12-31: Rp ' . number_format($utang, 0, ',', '.'));
        }

        $n = 0;
        $tot = ['beban' => 0, 'prive' => 0, 'utang' => 0];
        foreach ($baris as $x) {
            $nilai = floatval($x['nilai']);
            JurnalPosting::post(
                $spbu->spbu_id,
                $x['tanggal'],
                'manual',
                Uuid::uuid5(Uuid::NAMESPACE_URL, "impor-{$this->tahun}:keuangan:" . $x['sumber'])->toString(),
                $this->penanda() . ' ' . $x['keterangan'],
                [
                    ['kode_akun' => $x['kode_akun'], 'debit' => $nilai, 'kredit' => 0, 'keterangan' => $x['keterangan']],
                    ['kode_akun' => Akun::BANK, 'debit' => 0, 'kredit' => $nilai, 'keterangan' => 'Dibayar dari Bank'],
                ],
                $member
            );
            $tot[$x['jenis']] += $nilai;
            $n++;
        }
        $this->line("jurnal manual: $n (beban Rp " . number_format($tot['beban'], 0, ',', '.') . ', prive Rp ' . number_format($tot['prive'], 0, ',', '.')
            . ', pelunasan utang Rp ' . number_format($tot['utang'], 0, ',', '.') . ')');
    }

    // ------------------------------------------------------------ bonus

    /**
     * Catatan bonus per karyawan (tabel bonus, tanpa jurnal: uangnya sudah keluar lewat
     * jurnal "Reward Karyawan" di tahap keuangan). Nama di sheet dicocokkan ke nama KTP
     * member; nama yang tidak ada dibuatkan member baru (keputusan pemilik data).
     * Idempoten: baris yang sudah tercatat (tanggal + deskripsi sama) dilewati.
     */
    private function bonus(): void
    {
        $anggota = fn () => DB::table('member')->join('ktp', 'ktp.ktp_id', '=', 'member.ktp_id')
            ->whereNull('member.deleted_at')->pluck('ktp.nama', 'member.member_id');
        $daftar = $anggota();
        $n = ['baru' => 0, 'sudah ada' => 0];
        $memberBaru = [];
        foreach ($this->bacaCsv('bonus.csv') as $b) {
            $deskripsi = $this->penanda() . ' Reward karyawan ' . $b['bulan'] . ' (' . $b['nama'] . ', ' . $b['jabatan'] . ')';
            if (Bonus::where('tanggal', $b['tanggal'])->where('deksripsi', $deskripsi)->exists()) {
                $n['sudah ada']++;
                continue;
            }
            $id = $this->cocokkanNama($b['nama'], $daftar);
            if (is_null($id)) {
                [$id, $info] = $this->buatMember($b);
                $memberBaru[] = $info;
                $daftar = $anggota();
            }
            $bonus = new Bonus();
            $bonus->forceFill([
                'bonus_id' => Uuid::uuid4()->toString(),
                'member_id' => $id,
                'persentase' => (int) round(floatval($b['persen'])),
                'total_bonus' => round(floatval($b['nilai']), 2),
                'tanggal' => $b['tanggal'],
                'deksripsi' => $deskripsi,
            ]);
            Bonus::withoutEvents(fn () => $bonus->save());
            $n['baru']++;
        }
        $this->line("bonus per karyawan: {$n['baru']} baru, {$n['sudah ada']} sudah ada");
        foreach ($memberBaru as $info) {
            $this->line("member baru: $info");
        }
    }

    /**
     * Member untuk nama di sheet bonus yang tidak ada di master. Data KTP yang tidak
     * ada di Excel diisi penanda (NIK nol, tempat lahir '-', tanggal lahir = bergabung
     * sejak) untuk dilengkapi di aplikasi. Status Tidak Aktif dan akun login nonaktif
     * berpassword acak, jadi tidak ada yang bisa masuk sampai admin mengaktifkannya.
     *
     * @return array{0: string, 1: string} [member_id, ringkasan]
     */
    private function buatMember(array $b): array
    {
        $nama = Str::title(Str::lower(trim($b['nama'])));
        $mulai = Carbon::parse($b['tanggal'])->startOfMonth()->toDateString();
        $jk = match (strtoupper(trim($b['jk'] ?? ''))) {
            'L' => 'pria',
            'P' => 'wanita',
            default => '-',
        };
        $nomor = 1 + (int) DB::table('userlogin')->where('username', 'like', 'MGSJ-%')
            ->get(['username'])->max(fn ($u) => (int) substr($u->username, 5));
        $username = sprintf('MGSJ-%03d', $nomor);

        $ktp = Ktp::create([
            'nik' => '0000000000000000',
            'nama' => $nama,
            'jenis_kelamin' => $jk,
            'tempat_lahir' => '-',
            'tanggal_lahir' => $mulai,
            'verified' => 0,
        ]);
        $member = $ktp->member()->create([
            'status_member_id' => StatusMember::where('nama_status', 'Tidak Aktif')->value('status_member_id')
                ?? throw new RuntimeException('Status member "Tidak Aktif" tidak ada.'),
            'email' => Str::slug($nama, '.') . '@spbu' . self::NOMOR_SPBU . '.invalid',
            'no_hp' => null,
            'no_wa' => null,
            'avatar' => 'default.png',
            'bergabung_sejak' => $mulai,
        ]);
        $member->user_login()->create([
            'username' => $username,
            'password' => Str::random(40),   // di-hash oleh cast model; tidak disimpan di mana pun
            'is_aktif' => 0,
        ]);
        $jabatan = Jabatan::where('nama_jabatan', trim($b['jabatan']))->value('jabatan_id');
        if ($jabatan) {
            $member->jabatan()->syncWithoutDetaching([$jabatan]);
        }

        return [$member->member_id, "$nama ($username, " . ($jabatan ? trim($b['jabatan']) : 'tanpa jabatan') . ", JK $jk, bergabung $mulai, Tidak Aktif)"];
    }

    /** Cocokkan nama di sheet bonus ('Anshori', 'Zamora H.N.S', 'Asrul Harapat') ke nama KTP member. */
    private function cocokkanNama(string $nama, $anggota): ?string
    {
        $norm = function (string $s): array {
            $s = strtolower(preg_replace('/[^a-z ]/i', ' ', $s));
            $t = array_values(array_filter(explode(' ', $s), fn ($x) => $x !== ''));
            if (count($t) > 1 && $t[0] === 'h') {
                array_shift($t);   // gelar haji
            }

            return $t;
        };
        $cari = $norm($nama);
        if (! $cari || $cari === ['nama']) {
            return null;
        }
        $terbaik = null;
        $skor = 0;
        foreach ($anggota as $id => $nm) {
            $t = $norm($nm);
            if (! $t) {
                continue;
            }
            similar_text(implode(' ', $cari), implode(' ', $t), $persen);
            $tokenSama = in_array($cari[0], $t, true);
            $lanjutCocok = true;
            for ($i = 1; $i < count($cari); $i++) {
                $lanjutCocok = $lanjutCocok && isset($t[$i]) && str_starts_with($t[$i], $cari[$i]);
            }
            $s = ($tokenSama && $lanjutCocok) ? 100 + $persen : ($persen >= 75 ? $persen : 0);
            if ($s > $skor) {
                [$terbaik, $skor] = [$id, $s];
            }
        }

        return $terbaik;
    }

    // ------------------------------------------------------------ bantuan

    /** Statistik planner tabel yang baru berubah besar (di luar transaksi). */
    private function segarkanStatistik(string $tahap): void
    {
        $tabel = match ($tahap) {
            'bersihkan-stok-2026' => ['dokumen_stok', 'stand_meter_dokumen_stok'],
            'stand-meter' => ['stand_meter'],
            'pengadaan' => ['rencana_pembelian', 'detail_rencana_pembelian', 'pajak_pembelian', 'pembayaran', 'ms2',
                'detail_rencana_pembelian_ms2', 'pengiriman', 'pengiriman_ms2', 'supir', 'file_lo', 'detail_rencana_file',
                'jurnal', 'jurnal_detail'],
            'stok' => ['dokumen_stok', 'stand_meter_dokumen_stok', 'losses', 'penerimaan', 'penerimaan_stok_taking',
                'penerimaan_stok_taking_penyesuaian', 'jurnal', 'jurnal_detail'],
            'penjualan' => ['harga', 'penjualan', 'detail_penjualan', 'penarikan', 'jurnal', 'jurnal_detail'],
            'keuangan' => ['jurnal', 'jurnal_detail'],
            default => [],
        };
        foreach ($tabel as $t) {
            DB::statement(($tahap === 'bersihkan-stok-2026' ? 'VACUUM ANALYZE ' : 'ANALYZE ') . $t);
        }
        if ($tabel) {
            $this->line('Statistik diperbarui: ' . implode(', ', $tabel));
        }
    }

    /** Jam bongkar dari kolom "Jam" Bongkaran ('17.45'); di luar shift -> 1 jam setelah shift mulai. */
    private function jamBongkar(string $teks, Carbon $mulai, Carbon $selesai): Carbon
    {
        // dijepit ke [mulai + 1 menit, selesai - 15 menit]: sesudah Stok Taking shift
        // sebelumnya (dicatat pada jam pergantian shift) dan sebelum Good Issue (selesai - 10 menit)
        $awal = $mulai->copy()->addMinute();
        $batas = $selesai->copy()->subMinutes(15);
        if (preg_match('/^(\d{1,2})[.:](\d{2})$/', trim($teks), $m) && intval($m[1]) < 24 && intval($m[2]) < 60) {
            $w = $mulai->copy()->setTime(intval($m[1]), intval($m[2]));
            if ($w->lt($mulai)) {
                $w->addDay();   // shift 3 melewati tengah malam
            }
            if ($w->gte($mulai) && $w->lt($selesai)) {
                return $w->lt($awal) ? $awal : ($w->gt($batas) ? $batas : $w);
            }
        }

        return $mulai->copy()->addHour();
    }

    /** Harga net per liter per produk dari order pertama yang bernilai (untuk baris tanpa faktur). */
    private function hargaNetPerLiter(array $orders): array
    {
        $h = [];
        foreach ($orders as $o) {
            if (! isset($h[$o['produk']]) && floatval($o['kl']) > 0 && floatval($o['net']) > 0) {
                $h[$o['produk']] = floatval($o['net']) / (floatval($o['kl']) * 1000);
            }
        }

        return $h;
    }

    /** Biaya per liter (net + PPN + PPBKB) dari order pertama yang bernilai per produk. */
    private function biayaPerLiter(array $orders): array
    {
        $h = [];
        foreach ($orders as $o) {
            if (! isset($h[$o['produk']]) && floatval($o['kl']) > 0 && floatval($o['net']) > 0) {
                $h[$o['produk']] = (floatval($o['net']) + floatval($o['ppn']) + floatval($o['ppbkb'])) / (floatval($o['kl']) * 1000);
            }
        }

        return $h;
    }

    private function spbu(): Spbu
    {
        return Spbu::where('nomor_spbu', self::NOMOR_SPBU)->firstOrFail();
    }

    private function superadmin(): string
    {
        if ($this->memberId) {
            return $this->memberId;
        }
        $id = DB::table('userlogin')->where('username', self::SUPERADMIN)->value('member_id');
        if (is_null($id)) {
            throw new RuntimeException('User superadmin tidak ditemukan.');
        }

        return $this->memberId = $id;
    }

    private function pathCsv(string $nama): string
    {
        return storage_path("app/impor-{$this->tahun}/" . $nama);
    }

    private function bacaCsv(string $nama): array
    {
        $path = $this->pathCsv($nama);
        if (! is_file($path)) {
            throw new RuntimeException("$path belum ada; jalankan extractor di scripts/impor-2024 dulu.");
        }
        $f = fopen($path, 'r');
        $header = fgetcsv($f);
        $rows = [];
        while (($line = fgetcsv($f)) !== false) {
            $rows[] = array_combine($header, $line);
        }
        fclose($f);

        return $rows;
    }
}
