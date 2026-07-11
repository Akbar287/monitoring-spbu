<?php

namespace App\Console\Commands;

use App\Models\PajakPembelian;
use App\Models\Pembayaran;
use App\Models\Penarikan;
use App\Models\Penjualan;
use App\Models\PettyCash;
use App\Support\JurnalPosting;
use App\Support\PostingKeuangan;
use Illuminate\Console\Command;

class KeuanganBackfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keuangan:backfill
                            {--dari= : Batas awal tanggal transaksi (Y-m-d)}
                            {--sampai= : Batas akhir tanggal transaksi (Y-m-d)}
                            {--dry-run : Hanya hitung, tidak memposting apa pun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Posting jurnal untuk transaksi historis yang sudah terkonfirmasi tetapi belum punya jurnal (idempotent, aman dijalankan ulang)';

    private array $gagal = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! PostingKeuangan::aktif()) {
            $this->error('Modul GL belum aktif: tabel coa kosong. Jalankan `php artisan db:seed --class=CoaSeeder` dulu.');
            return self::FAILURE;
        }

        $dari = $this->option('dari');
        $sampai = $this->option('sampai');
        $dry = (bool) $this->option('dry-run');

        $rentang = fn ($query, $kolom = 'tanggal') => $query
            ->when($dari, fn ($q) => $q->whereDate($kolom, '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate($kolom, '<=', $sampai));

        $hasil = [];

        $hasil[] = $this->proses('penjualan',
            $rentang(Penjualan::where('is_accepted_admin', 1)->where('is_accepted_top', 1)),
            fn (Penjualan $p) => PostingKeuangan::postPenjualan($p),
            fn (Penjualan $p) => $p->penjualan_id,
            $dry
        );

        $hasil[] = $this->proses('penarikan',
            $rentang(Penarikan::where('is_accepted', 1)),
            fn (Penarikan $p) => PostingKeuangan::postPenarikan($p),
            fn (Penarikan $p) => $p->penarikan_id,
            $dry
        );

        $hasil[] = $this->proses('pembelian',
            $rentang(PajakPembelian::query(), 'created_at'),
            fn (PajakPembelian $p) => PostingKeuangan::postFakturPembelian($p),
            fn (PajakPembelian $p) => $p->pajak_pembelian_id,
            $dry
        );

        $hasil[] = $this->proses('pembayaran',
            $rentang(Pembayaran::where('is_confirmation_admin', 1)->where('is_confirmation_top', 1)),
            fn (Pembayaran $p) => PostingKeuangan::postPembayaran($p),
            fn (Pembayaran $p) => $p->pembayaran_id,
            $dry
        );

        $hasil[] = $this->proses('petty_cash',
            $rentang(PettyCash::query()),
            fn (PettyCash $p) => PostingKeuangan::postPettyCash($p),
            fn (PettyCash $p) => $p->petty_cash_id,
            $dry
        );

        $this->newLine();
        $this->table(['Sumber', 'Kandidat', 'Sudah Ada', 'Diposting' . ($dry ? ' (dry-run)' : ''), 'Gagal'], $hasil);

        foreach ($this->gagal as $g) {
            $this->warn("  GAGAL {$g['tipe']} {$g['id']}: {$g['pesan']}");
        }
        if (count($this->gagal) > 0) {
            $this->warn('Perbaiki penyebabnya lalu jalankan ulang command ini; record yang sudah terposting otomatis dilewati.');
        }

        return count($this->gagal) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function proses(string $tipe, $query, callable $poster, callable $ambilId, bool $dry): array
    {
        $kandidat = 0;
        $sudahAda = 0;
        $diposting = 0;
        $gagal = 0;

        foreach ($query->cursor() as $record) {
            $kandidat++;
            $id = $ambilId($record);
            if (JurnalPosting::sudahDiposting($tipe, $id)) {
                $sudahAda++;
                continue;
            }
            if ($dry) {
                $diposting++;
                continue;
            }
            try {
                $poster($record);
                $diposting++;
            } catch (\Throwable $e) {
                $gagal++;
                $this->gagal[] = ['tipe' => $tipe, 'id' => $id, 'pesan' => $e->getMessage()];
            }
        }

        return [$tipe, $kandidat, $sudahAda, $diposting, $gagal];
    }
}
