<?php

namespace App\Console\Commands;

use App\Models\Aset;
use App\Models\Jurnal;
use App\Models\Spbu;
use App\Support\Akun;
use App\Support\JurnalPosting;
use App\Support\PostingKeuangan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class KeuanganPenyusutan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keuangan:penyusutan
                            {--bulan= : Bulan yang disusutkan (format Y-m, default bulan berjalan)}
                            {--dry-run : Hanya hitung, tidak memposting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Posting jurnal penyusutan aset bulanan per SPBU (Dr Beban Penyusutan / Cr Akumulasi Penyusutan). Idempotent per SPBU per bulan; jalankan tiap akhir bulan atau via scheduler.';

    /**
     * Execute the console command.
     *
     * Asumsi: aset.harga adalah nilai perolehan baris aset dan
     * aset.penyusutan_per_hari berlaku untuk baris tersebut. Penyusutan
     * kumulatif dibatasi maksimum nilai perolehan (tidak menyusut lewat nol),
     * dihitung dari tanggal_digunakan; beban bulan ini = kumulatif s.d. akhir
     * bulan ini dikurangi kumulatif s.d. akhir bulan lalu.
     */
    public function handle(): int
    {
        if (! PostingKeuangan::aktif()) {
            $this->error('Modul GL belum aktif: tabel coa kosong. Jalankan CoaSeeder dulu.');
            return self::FAILURE;
        }

        $bulan = $this->option('bulan')
            ? Carbon::createFromFormat('Y-m', $this->option('bulan'))
            : Carbon::now();
        $akhirBulan = $bulan->copy()->endOfMonth()->startOfDay();
        $akhirBulanLalu = $bulan->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay();
        $dry = (bool) $this->option('dry-run');

        $kumulatif = function (Aset $aset, Carbon $sampai): float {
            $mulai = Carbon::parse($aset->tanggal_digunakan)->startOfDay();
            if ($mulai->gt($sampai)) {
                return 0;
            }
            $hari = $mulai->diffInDays($sampai) + 1;
            return min($hari * floatval($aset->penyusutan_per_hari), floatval($aset->harga));
        };

        $hasil = [];
        foreach (Spbu::get() as $spbu) {
            $sudah = Jurnal::where('tipe_sumber', 'penyusutan')
                ->where('spbu_id', $spbu->spbu_id)
                ->whereDate('tanggal', $akhirBulan->toDateString())
                ->exists();
            if ($sudah) {
                $hasil[] = [$spbu->nama_spbu, '-', 'sudah pernah diposting'];
                continue;
            }

            $beban = 0;
            foreach (Aset::where('spbu_id', $spbu->spbu_id)->where('is_digunakan', 1)->get() as $aset) {
                $beban += $kumulatif($aset, $akhirBulan) - $kumulatif($aset, $akhirBulanLalu);
            }
            if ($beban <= 0.0001) {
                $hasil[] = [$spbu->nama_spbu, '0', 'tidak ada aset yang disusutkan'];
                continue;
            }

            if (! $dry) {
                JurnalPosting::post(
                    $spbu->spbu_id,
                    $akhirBulan->toDateString(),
                    'penyusutan',
                    null,
                    'Penyusutan aset ' . $spbu->nama_spbu . ' bulan ' . $bulan->format('m-Y'),
                    [
                        ['kode_akun' => Akun::BEBAN_PENYUSUTAN, 'debit' => $beban, 'kredit' => 0, 'keterangan' => 'Penyusutan aset bulan ' . $bulan->format('m-Y')],
                        ['kode_akun' => Akun::AKUMULASI_PENYUSUTAN, 'debit' => 0, 'kredit' => $beban, 'keterangan' => 'Akumulasi penyusutan bulan ' . $bulan->format('m-Y')],
                    ]
                );
            }
            $hasil[] = [$spbu->nama_spbu, number_format($beban, 2, ',', '.'), $dry ? 'dry-run' : 'diposting per ' . $akhirBulan->format('d/m/Y')];
        }

        $this->table(['SPBU', 'Beban Penyusutan', 'Status'], $hasil);

        return self::SUCCESS;
    }
}
