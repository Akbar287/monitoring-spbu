<?php

namespace App\Console\Commands;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\Spbu;
use App\Support\Akun;
use App\Support\JurnalPosting;
use App\Support\PostingKeuangan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KeuanganTutupTahun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keuangan:tutup-tahun
                            {tahun : Tahun buku yang ditutup (mis. 2026)}
                            {--dry-run : Hanya hitung, tidak memposting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jurnal penutup tahunan: memindahkan saldo seluruh akun nominal (pendapatan/HPP/beban) per 31 Desember ke Laba Ditahan, per SPBU. Idempotent per SPBU per tahun.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! PostingKeuangan::aktif()) {
            $this->error('Modul GL belum aktif: tabel coa kosong. Jalankan CoaSeeder dulu.');
            return self::FAILURE;
        }

        $tahun = intval($this->argument('tahun'));
        $tanggal = $tahun . '-12-31';
        $dry = (bool) $this->option('dry-run');

        $nominal = Coa::where('is_header', false)->whereIn('tipe_akun', Coa::TIPE_NOMINAL)->get()->keyBy('coa_id');

        $hasil = [];
        foreach (Spbu::get() as $spbu) {
            $sudah = Jurnal::where('tipe_sumber', 'closing')
                ->where('spbu_id', $spbu->spbu_id)
                ->whereDate('tanggal', $tanggal)
                ->exists();
            if ($sudah) {
                $hasil[] = [$spbu->nama_spbu, '-', 'sudah pernah ditutup'];
                continue;
            }

            // saldo akun nominal s.d. 31 Des (termasuk closing tahun sebelumnya)
            $saldo = DB::table('jurnal_detail')
                ->join('jurnal', 'jurnal.jurnal_id', 'jurnal_detail.jurnal_id')
                ->where('jurnal.is_posted', true)
                ->where('jurnal.spbu_id', $spbu->spbu_id)
                ->where('jurnal.tanggal', '<=', $tanggal)
                ->whereIn('jurnal_detail.coa_id', $nominal->keys())
                ->groupBy('jurnal_detail.coa_id')
                ->select('jurnal_detail.coa_id', DB::raw('sum(jurnal_detail.debit - jurnal_detail.kredit) as saldo'))
                ->pluck('saldo', 'coa_id');

            $lines = [];
            $laba = 0; // kredit positif = laba
            foreach ($saldo as $coaId => $s) {
                $s = floatval($s);
                if (abs($s) < 0.005) {
                    continue;
                }
                // balik saldo akun nominal ke nol
                $lines[] = [
                    'kode_akun' => $nominal[$coaId]->kode_akun,
                    'debit' => $s < 0 ? abs($s) : 0,
                    'kredit' => $s > 0 ? $s : 0,
                    'keterangan' => 'Penutupan ' . $nominal[$coaId]->nama_akun . ' tahun ' . $tahun,
                ];
                $laba += -$s;
            }
            if (count($lines) === 0) {
                $hasil[] = [$spbu->nama_spbu, '0', 'tidak ada akun nominal bersaldo'];
                continue;
            }
            $lines[] = [
                'kode_akun' => Akun::LABA_DITAHAN,
                'debit' => $laba < 0 ? abs($laba) : 0,
                'kredit' => $laba > 0 ? $laba : 0,
                'keterangan' => 'Laba (rugi) bersih tahun ' . $tahun,
            ];

            if (! $dry) {
                JurnalPosting::post(
                    $spbu->spbu_id,
                    $tanggal,
                    'closing',
                    null,
                    'Jurnal penutup tahun ' . $tahun . ' ' . $spbu->nama_spbu,
                    $lines
                );
            }
            $hasil[] = [$spbu->nama_spbu, number_format($laba, 2, ',', '.'), $dry ? 'dry-run' : 'ditutup ke Laba Ditahan'];
        }

        $this->table(['SPBU', 'Laba (Rugi) Ditutup', 'Status'], $hasil);
        $this->info('Catatan: jalankan setelah semua transaksi tahun ' . $tahun . ' final. Periode Desember sebaiknya ditutup (menu Tutup Buku) SETELAH command ini.');

        return self::SUCCESS;
    }
}
