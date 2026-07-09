<?php

namespace App\Imports\Transaksi;

use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Nozzle;
use RuntimeException;

/**
 * Pemetaan jenis file import -> kelas importer, plus penyedia data referensi
 * (daftar nama master) untuk sheet "Referensi" di template Excel.
 */
class ImporterRegistry
{
    /** jenis_file (label di form) => kelas importer */
    private const MAP = [
        'Stok Taking' => StokTakingImporter::class,
        'Penerimaan BBM' => PenerimaanImporter::class,
        'Stok Transfer' => StokTransferImporter::class,
        'Totalisator' => TotalisatorImporter::class,
    ];

    public static function jenisTersedia(): array
    {
        return array_keys(self::MAP);
    }

    public static function support(string $jenis): bool
    {
        return isset(self::MAP[$jenis]);
    }

    public static function make(string $jenis): AbstractTransaksiImporter
    {
        if (! self::support($jenis)) {
            throw new RuntimeException("Jenis import \"$jenis\" belum didukung.");
        }
        $class = self::MAP[$jenis];

        return new $class();
    }

    public static function headers(string $jenis): array
    {
        return self::MAP[$jenis]::headers();
    }

    public static function example(string $jenis): array
    {
        return self::MAP[$jenis]::example();
    }

    /**
     * Daftar nama master yang relevan dengan jenis import, untuk sheet referensi.
     *
     * @return array<string,array<int,string>>  judul kolom => daftar nama
     */
    public static function referensi(string $jenis): array
    {
        $jamKerja = JamKerja::orderBy('urutan')->pluck('nama_jam_kerja')->all();
        $dombak = Dombak::orderBy('nama_dombak')->pluck('nama_dombak')->all();
        $nozzle = Nozzle::orderBy('nama_nozzle')->pluck('nama_nozzle')->all();

        return match ($jenis) {
            'Totalisator' => [
                'Jam Kerja' => $jamKerja,
                'Dombak' => $dombak,
                'Nozzle' => $nozzle,
            ],
            default => [
                'Jam Kerja' => $jamKerja,
                'Dombak' => $dombak,
            ],
        };
    }
}
