<?php

namespace App\Http\Controllers\Api\V1\Persediaan;

use App\Http\Controllers\Controller;
use App\Models\Dombak;
use App\Models\DombakTransfer;
use App\Support\ApiResponse;
use App\Support\Persediaan;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Stok Transfer: pemindahan BBM antar dombak untuk produk yang sama.
 *
 * Satu transfer menghasilkan empat dokumen stok:
 *   1. Stok Taking Good Transfer (asal)   — menyelaraskan saldo tercatat
 *                                            dengan tinggi terukur sebelum pindah
 *   2. Good Transfer Send (asal)          — pengurangan nyata
 *   3. Stok Taking Good Transfer (tujuan) — penyelarasan di sisi tujuan
 *   4. Good Transfer Receive (tujuan)     — penambahan nyata
 *
 * Selisih antara yang diterima dan yang dikirim dibukukan sebagai losses
 * jenis "Good Transfer" pada dokumen penerimaan.
 */
class StokTransferController extends Controller
{
    public function index(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: false);
    }

    public function riwayat(Request $request)
    {
        return $this->daftar($request, terkonfirmasi: true);
    }

    public function show(string $id)
    {
        $t = DombakTransfer::find($id);
        if (is_null($t)) {
            return ApiResponse::fail('Transfer tidak ditemukan.', 404);
        }

        $dokumen = $t->dokumen_stok()->get();
        $kirim = $this->cari($dokumen, Persediaan::GERAK_KIRIM);
        $terima = $this->cari($dokumen, Persediaan::GERAK_TERIMA_TRANSFER);

        return ApiResponse::ok([
            'id' => $t->dombak_transfer_id,
            'dombak_transfer_id' => $t->dombak_transfer_id,
            'tanggal' => $t->tanggal,
            'waktu' => $t->waktu,
            'produk' => optional($t->produk()->first())->nama_produk,
            'jam_kerja' => optional($t->jam_kerja()->first())->nama_jam_kerja,
            'is_confirmation' => filter_var($t->is_confirmation, FILTER_VALIDATE_BOOLEAN),
            'dikirim' => $kirim ? Uang::kali(Uang::kurang($kirim->stok_akhir, $kirim->stok_awal), '-1') : '0.000000',
            'diterima' => $terima ? Uang::kurang($terima->stok_akhir, $terima->stok_awal) : '0.000000',
            'dombak_asal' => Persediaan::namaDombak($kirim),
            'dombak_tujuan' => Persediaan::namaDombak($terima),
            'dokumen' => $dokumen->map(fn ($d) => Persediaan::serialDokumen($d, lengkap: true))->values()->all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'waktu' => ['required', 'date_format:H:i,H:i:s'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'dombak_asal_id' => ['required', 'exists:dombak,dombak_id'],
            'tinggi_asal_sebelum' => ['required', 'numeric', 'min:0'],
            'tinggi_asal_sesudah' => ['required', 'numeric', 'min:0'],
            'dombak_tujuan_id' => ['required', 'exists:dombak,dombak_id', 'different:dombak_asal_id'],
            'tinggi_tujuan_sebelum' => ['required', 'numeric', 'min:0'],
            'tinggi_tujuan_sesudah' => ['required', 'numeric', 'min:0'],
        ]);

        $asal = Dombak::find($request->input('dombak_asal_id'));
        $tujuan = Dombak::find($request->input('dombak_tujuan_id'));

        $invAsal = Persediaan::inventoryDariDombak($asal);
        $invTujuan = Persediaan::inventoryDariDombak($tujuan);

        if (is_null($invAsal) || is_null($invTujuan)) {
            return ApiResponse::fail('Kedua dombak harus sudah terhubung ke produk di Monitoring Stok.', 422);
        }

        if ($invAsal->stok_inventory_id !== $invTujuan->stok_inventory_id) {
            return ApiResponse::fail('Transfer hanya boleh antar dombak dengan produk yang sama.', 422);
        }

        $memberId = $request->user()->member()->first()->member_id;

        try {
            $transfer = DB::transaction(function () use ($request, $asal, $tujuan, $invAsal, $memberId) {
                $tanggal = $request->input('tanggal');
                $waktu = $request->input('waktu');
                $jamKerja = $request->input('jam_kerja_id');

                $asalSebelum = Persediaan::volumeDariTinggi($asal, $request->input('tinggi_asal_sebelum'));
                $asalSesudah = Persediaan::volumeDariTinggi($asal, $request->input('tinggi_asal_sesudah'));
                $tujuanSebelum = Persediaan::volumeDariTinggi($tujuan, $request->input('tinggi_tujuan_sebelum'));
                $tujuanSesudah = Persediaan::volumeDariTinggi($tujuan, $request->input('tinggi_tujuan_sesudah'));

                if (Uang::bandingkan($asalSesudah, $asalSebelum) >= 0) {
                    throw new RuntimeException('Tinggi dombak asal setelah transfer harus lebih rendah dari sebelumnya.');
                }
                if (Uang::bandingkan($tujuanSesudah, $tujuanSebelum) <= 0) {
                    throw new RuntimeException('Tinggi dombak tujuan setelah transfer harus lebih tinggi dari sebelumnya.');
                }

                $produk = $invAsal->produk()->first();
                $transfer = $produk->dombak_transfer()
                    ->where('jam_kerja_id', $jamKerja)->where('tanggal', $tanggal)
                    ->where('is_confirmation', 0)->first()
                    ?? $produk->dombak_transfer()->create([
                        'jam_kerja_id' => $jamKerja,
                        'tanggal' => $tanggal,
                        'waktu' => $waktu,
                        'is_confirmation' => 0,
                    ]);

                $tipeTaking = Persediaan::tipe(Persediaan::GERAK_TRANSFER_TAKING);
                $tipeKirim = Persediaan::tipe(Persediaan::GERAK_KIRIM);
                $tipeTerima = Persediaan::tipe(Persediaan::GERAK_TERIMA_TRANSFER);

                $buat = function ($tipe, $dombak, $awal, $akhir) use ($invAsal, $memberId, $jamKerja, $tanggal, $waktu, $transfer) {
                    $d = Persediaan::dokumen($tipe, $invAsal, $memberId, $dombak->dombak_id, $jamKerja, $tanggal, $awal, $akhir, $waktu);

                    $ada = DB::table('dombak_transfer_dokumen_stok')
                        ->where('dombak_transfer_id', $transfer->dombak_transfer_id)
                        ->where('dokumen_stok_id', $d->dokumen_stok_id)->exists();
                    if (! $ada) {
                        DB::table('dombak_transfer_dokumen_stok')->insert([
                            'dombak_transfer_id' => $transfer->dombak_transfer_id,
                            'dokumen_stok_id' => $d->dokumen_stok_id,
                        ]);
                    }

                    return $d;
                };

                // 1 & 2 — sisi asal
                $takingAsal = $buat($tipeTaking, $asal, Persediaan::stokDombak($asal), $asalSebelum);
                Persediaan::kaitkanKonversi($takingAsal, Persediaan::konversi($asal, $request->input('tinggi_asal_sebelum')));
                $kirim = $buat($tipeKirim, $asal, $asalSebelum, $asalSesudah);
                Persediaan::kaitkanKonversi($kirim, Persediaan::konversi($asal, $request->input('tinggi_asal_sesudah')));

                // 3 & 4 — sisi tujuan
                $takingTujuan = $buat($tipeTaking, $tujuan, Persediaan::stokDombak($tujuan), $tujuanSebelum);
                Persediaan::kaitkanKonversi($takingTujuan, Persediaan::konversi($tujuan, $request->input('tinggi_tujuan_sebelum')));
                $terima = $buat($tipeTerima, $tujuan, $tujuanSebelum, $tujuanSesudah);
                Persediaan::kaitkanKonversi($terima, Persediaan::konversi($tujuan, $request->input('tinggi_tujuan_sesudah')));

                // losses = diterima − dikirim
                $dikirim = Uang::kurang($asalSebelum, $asalSesudah);
                $diterima = Uang::kurang($tujuanSesudah, $tujuanSebelum);
                Persediaan::catatSelisih($terima, 'Good Transfer', $tanggal, Uang::kurang($diterima, $dikirim));

                return $transfer;
            });
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return $this->show($transfer->dombak_transfer_id);
    }

    public function konfirmasi(string $id)
    {
        $t = DombakTransfer::find($id);
        if (is_null($t)) {
            return ApiResponse::fail('Transfer tidak ditemukan.', 404);
        }

        if (filter_var($t->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return $this->show($id);
        }

        try {
            DB::transaction(function () use ($t) {
                $dokumen = $t->dokumen_stok()->get();

                // urutan penting: penyelarasan dulu, baru pergerakan nyata
                foreach ([Persediaan::GERAK_TRANSFER_TAKING, Persediaan::GERAK_KIRIM, Persediaan::GERAK_TERIMA_TRANSFER] as $gerak) {
                    foreach ($this->semua($dokumen, $gerak) as $d) {
                        if (! filter_var($d->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
                            Persediaan::terapkan($d);
                        }
                    }
                }

                $t->update(['is_confirmation' => 1]);
            });
        } catch (RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return $this->show($id);
    }

    public function destroy(string $id)
    {
        $t = DombakTransfer::find($id);
        if (is_null($t)) {
            return ApiResponse::fail('Transfer tidak ditemukan.', 404);
        }

        if (filter_var($t->is_confirmation, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Transfer yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($t) {
            foreach ($t->dokumen_stok()->get() as $d) {
                Persediaan::hapusDokumen($d);
            }
            $t->delete();
        });

        return ApiResponse::ok([], 'Transfer dihapus.');
    }

    // --------------------------------------------------------------- bantuan

    private function cari($dokumen, string $gerak)
    {
        return $this->semua($dokumen, $gerak)->first();
    }

    private function semua($dokumen, string $gerak)
    {
        $tipeId = Persediaan::tipe($gerak)->type_dokumen_stok_id;

        return $dokumen->filter(fn ($d) => $d->type_dokumen_stok_id === $tipeId)->values();
    }

    private function daftar(Request $request, bool $terkonfirmasi)
    {
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = DombakTransfer::where('is_confirmation', $terkonfirmasi ? 1 : 0)
            ->when($request->filled('dari'), fn ($q) => $q->where('tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $pola = '%' . mb_strtolower($request->input('cari')) . '%';
                $q->where(fn ($w) => $w
                    ->whereRaw('LOWER(CAST(tanggal AS TEXT)) LIKE ?', [$pola])
                    ->orWhereHas('produk', fn ($p) => $p->whereRaw('LOWER(nama_produk) LIKE ?', [$pola]))
                    ->orWhereHas('dokumen_stok.dombak', fn ($d) => $d->whereRaw('LOWER(nama_dombak) LIKE ?', [$pola])));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::paginated($paginator, function (DombakTransfer $t) {
            $dokumen = $t->dokumen_stok()->get();
            $kirim = $this->cari($dokumen, Persediaan::GERAK_KIRIM);
            $terima = $this->cari($dokumen, Persediaan::GERAK_TERIMA_TRANSFER);
            $jumlah = $kirim ? Uang::kurang($kirim->stok_awal, $kirim->stok_akhir) : '0.000000';

            $asal = Persediaan::namaDombak($kirim);
            $tujuan = Persediaan::namaDombak($terima);
            $produk = optional($t->produk()->first())->nama_produk;

            // sebagian transfer lama tidak punya dokumen kirim/terima sama
            // sekali; tampilkan nama produk agar barisnya tetap bermakna
            $judul = ($asal && $tujuan)
                ? $asal . ' → ' . $tujuan
                : ($produk ? $produk . ' (dokumen tidak lengkap)' : 'Transfer tanpa dokumen');

            return [
                'id' => $t->dombak_transfer_id,
                'judul' => $judul,
                'subjudul' => $t->tanggal . ' · ' . $jumlah . ' L',
                'tanggal' => $t->tanggal,
                'produk' => optional($t->produk()->first())->nama_produk,
                'jumlah' => $jumlah,
                'is_confirmation' => filter_var($t->is_confirmation, FILTER_VALIDATE_BOOLEAN),
            ];
        });
    }
}
