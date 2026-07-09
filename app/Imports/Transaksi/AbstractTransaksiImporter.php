<?php

namespace App\Imports\Transaksi;

use App\Models\Dombak;
use App\Models\DokumenStok;
use App\Support\Import\MasterResolver;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dasar importer transaksi berbasis Excel.
 *
 * Tiap baris Excel dipetakan ke header (lihat {@see headers()}), divalidasi,
 * lalu dieksekusi dalam satu transaksi DB. Bila ada baris yang gagal, seluruh
 * import di-rollback dan daftar error per baris dikembalikan ke pemanggil.
 */
abstract class AbstractTransaksiImporter
{
    protected MasterResolver $master;

    public function __construct(?MasterResolver $master = null)
    {
        $this->master = $master ?? new MasterResolver();
    }

    /** Label kolom (urutan = urutan kolom di sheet "Data"). */
    abstract public static function headers(): array;

    /** Satu baris contoh untuk template (selaras dengan headers()). */
    abstract public static function example(): array;

    /** Eksekusi satu baris yang sudah dipetakan ke nama kolom. */
    abstract protected function handleRow(array $row, string $memberId): void;

    /**
     * @param  array  $rows  Baris mentah dari Excel (termasuk baris header di indeks 0).
     * @return array{inserted:int, errors:array<int,string>}
     */
    public function import(array $rows, string $memberId): array
    {
        $headers = static::headers();
        $errors = [];
        $inserted = 0;

        // Baris 0 = header, data mulai baris ke-2 (indeks 1) di Excel.
        $dataRows = array_slice($rows, 1);

        DB::beginTransaction();
        try {
            foreach ($dataRows as $i => $raw) {
                $excelLine = $i + 2; // nomor baris di file Excel
                if ($this->isBlank($raw)) {
                    continue;
                }

                $mapped = [];
                foreach ($headers as $col => $key) {
                    $mapped[$key] = $raw[$col] ?? null;
                }

                try {
                    $this->handleRow($mapped, $memberId);
                    $inserted++;
                } catch (RuntimeException $e) {
                    $errors[] = "Baris $excelLine: " . $e->getMessage();
                }
            }

            if (! empty($errors)) {
                DB::rollBack();

                return ['inserted' => 0, 'errors' => $errors];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return ['inserted' => 0, 'errors' => ['Gagal mengimpor: ' . $e->getMessage()]];
        }

        return ['inserted' => $inserted, 'errors' => []];
    }

    protected function isBlank(array $row): bool
    {
        foreach ($row as $cell) {
            if (! is_null($cell) && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /** Bersihkan angka: hapus pemisah ribuan, jadikan float. */
    protected function num($value, string $label): float
    {
        if (is_null($value) || trim((string) $value) === '') {
            throw new RuntimeException("Kolom \"$label\" wajib diisi angka.");
        }
        $clean = str_replace([',', ' '], '', (string) $value);
        if (! is_numeric($clean)) {
            throw new RuntimeException("Kolom \"$label\" harus berupa angka, diterima \"$value\".");
        }

        return (float) $clean;
    }

    protected function text($value, string $label): string
    {
        if (is_null($value) || trim((string) $value) === '') {
            throw new RuntimeException("Kolom \"$label\" wajib diisi.");
        }

        return trim((string) $value);
    }

    /** Normalisasi tanggal ke Y-m-d (terima Y-m-d, d/m/Y, atau serial Excel). */
    protected function tanggal($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            throw new RuntimeException('Kolom "tanggal" wajib diisi.');
        }

        // Serial date Excel (angka murni).
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }
        }

        throw new RuntimeException("Format tanggal \"$value\" tidak dikenali (pakai Y-m-d).");
    }

    /** Hubungkan dokumen stok ke baris konversi dombak yang volumenya cocok. */
    protected function linkKonversi(Dombak $dombak, DokumenStok $dokumenStok, float $volume): void
    {
        $konversi = $dombak->konversi()->where('volume', $volume)->first();
        if (is_null($konversi)) {
            return;
        }
        $exists = DB::table('konversi_dokumen_stok')
            ->where('konversi_id', $konversi->konversi_id)
            ->where('dokumen_stok_id', $dokumenStok->dokumen_stok_id)
            ->exists();
        if (! $exists) {
            DB::table('konversi_dokumen_stok')->insert([
                'konversi_id' => $konversi->konversi_id,
                'dokumen_stok_id' => $dokumenStok->dokumen_stok_id,
            ]);
        }
    }
}
