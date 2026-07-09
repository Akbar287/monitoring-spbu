<?php

namespace App\Exports;

use App\Imports\Transaksi\ImporterRegistry;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Template Excel untuk import transaksi: sheet "Data" (header + 1 baris contoh
 * yang siap diganti) dan sheet "Referensi" (daftar nama master yang valid).
 */
class TemplateTransaksiExport implements WithMultipleSheets
{
    public function __construct(private string $jenis)
    {
    }

    public function sheets(): array
    {
        $headers = ImporterRegistry::headers($this->jenis);
        $example = ImporterRegistry::example($this->jenis);

        $dataRows = [$headers, $example];

        $referensi = ImporterRegistry::referensi($this->jenis);
        // Susun referensi jadi tabel kolom-kolom sejajar.
        $refRows = [array_keys($referensi)];
        $maxLen = max(array_map('count', $referensi) ?: [0]);
        for ($i = 0; $i < $maxLen; $i++) {
            $row = [];
            foreach ($referensi as $list) {
                $row[] = $list[$i] ?? null;
            }
            $refRows[] = $row;
        }

        return [
            new ArraySheet('Data', $dataRows),
            new ArraySheet('Referensi', $refRows),
        ];
    }
}
