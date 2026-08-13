<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Penjadwalan;
use App\Support\ApiResponse;
use App\Support\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Absen masuk & pulang dari aplikasi mobile.
 *
 * Padanan web-nya adalah KehadiranOperatorController::store()/close(), dengan
 * tiga perbedaan yang disengaja (lihat MOBILE_API.md):
 *   1. jadwal diverifikasi milik pegawai yang login;
 *   2. lokasi diambil dari GPS perangkat, bukan geolokasi berbasis IP;
 *   3. selfie wajib pada absen masuk dan benar-benar disimpan
 *      (tabel image_selfie belum pernah diisi oleh alur web).
 */
class KehadiranController extends Controller
{
    private const DIREKTORI_SELFIE = 'public/image_selfie';

    public function masuk(Request $request, Penjadwalan $penjadwalan)
    {
        if ($gagal = JadwalController::pastikanMilikSendiri($request, $penjadwalan)) {
            return $gagal;
        }

        $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'gmt' => ['nullable', 'integer', 'between:-14,14'],
            'selfie' => ['required', 'image', 'max:' . Upload::maxKb()],
            'deskripsi_lokasi' => ['nullable', 'string', 'max:255'],
            'device' => ['nullable', 'array'],
            'device.tipe' => ['nullable', 'string', 'max:32'],
            'device.model' => ['nullable', 'string', 'max:64'],
            'device.os' => ['nullable', 'string', 'max:64'],
            'device.versi_os' => ['nullable', 'string', 'max:16'],
            'device.aplikasi' => ['nullable', 'string', 'max:32'],
            'device.versi_aplikasi' => ['nullable', 'string', 'max:16'],
        ]);

        // idempoten: pengiriman ulang (mis. jaringan putus setelah sukses)
        // tidak boleh dianggap error oleh klien
        if (filter_var($penjadwalan->is_attended, FILTER_VALIDATE_BOOLEAN) || ! is_null($penjadwalan->start_time)) {
            return ApiResponse::ok(
                JadwalController::serialize($penjadwalan->fresh(), lengkap: true),
                'Anda sudah absen masuk untuk jadwal ini.'
            );
        }

        $qrcode = $penjadwalan->random_string_barcode()
            ->where('is_active', 1)
            ->where('is_done', 0)
            ->first();

        if (is_null($qrcode)) {
            return ApiResponse::fail('QR code untuk jadwal ini belum tersedia atau sudah terpakai. Minta Penanggung Jawab menampilkan ulang QR.', 422);
        }

        if (! hash_equals((string) $qrcode->barcode, (string) $request->input('barcode'))) {
            return ApiResponse::fail('QR code tidak cocok dengan jadwal ini.', 422);
        }

        $namaSelfie = $this->simpanSelfie($request->file('selfie'), $penjadwalan, 'in');

        DB::transaction(function () use ($request, $penjadwalan, $qrcode, $namaSelfie) {
            $qrcode->update(['is_done' => 1, 'is_active' => 0]);

            $penjadwalan->update([
                'is_attended' => 1,
                // kode_penjadwalan bersifat unik; dibuat server agar klien tidak
                // bisa menabrakkan nilainya
                'kode_penjadwalan' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'start_time' => now(),
                'attended' => now(),
            ]);

            $penjadwalan->image_selfie()->create(['nama_file' => $namaSelfie]);

            $penjadwalan->location()->create([
                'lattitude' => $request->float('latitude'),
                'longitude' => $request->float('longitude'),
                'gmt' => $request->integer('gmt', 7), // default WIB
                'deskripsi' => $request->input('deskripsi_lokasi'),
            ]);

            if ($penjadwalan->devices()->count() === 0) {
                $penjadwalan->devices()->create($this->dataPerangkat($request));
            }
        });

        return ApiResponse::ok(
            JadwalController::serialize($penjadwalan->fresh(), lengkap: true),
            'Absen masuk berhasil.'
        );
    }

    public function pulang(Request $request, Penjadwalan $penjadwalan)
    {
        if ($gagal = JadwalController::pastikanMilikSendiri($request, $penjadwalan)) {
            return $gagal;
        }

        $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gmt' => ['nullable', 'integer', 'between:-14,14'],
            'selfie' => ['nullable', 'image', 'max:' . Upload::maxKb()],
            'deskripsi_lokasi' => ['nullable', 'string', 'max:255'],
        ]);

        if (is_null($penjadwalan->start_time)) {
            return ApiResponse::fail('Anda belum absen masuk untuk jadwal ini.', 422);
        }

        if (! is_null($penjadwalan->finish_time)) {
            return ApiResponse::ok(
                JadwalController::serialize($penjadwalan->fresh(), lengkap: true),
                'Anda sudah absen pulang untuk jadwal ini.'
            );
        }

        $namaSelfie = $request->hasFile('selfie')
            ? $this->simpanSelfie($request->file('selfie'), $penjadwalan, 'out')
            : null;

        DB::transaction(function () use ($request, $penjadwalan, $namaSelfie) {
            $penjadwalan->update(['finish_time' => now()]);

            if ($namaSelfie) {
                $penjadwalan->image_selfie()->create(['nama_file' => $namaSelfie]);
            }

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $penjadwalan->location()->create([
                    'lattitude' => $request->float('latitude'),
                    'longitude' => $request->float('longitude'),
                    'gmt' => $request->integer('gmt', 7),
                    'deskripsi' => $request->input('deskripsi_lokasi') ?? 'Absen pulang',
                ]);
            }
        });

        return ApiResponse::ok(
            JadwalController::serialize($penjadwalan->fresh(), lengkap: true),
            'Absen pulang berhasil.'
        );
    }

    private function simpanSelfie(UploadedFile $file, Penjadwalan $penjadwalan, string $tahap): string
    {
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        // image_selfie.nama_file dibatasi 128 karakter — pola di bawah aman
        $nama = 'selfie_' . $tahap . '_' . $penjadwalan->penjadwalan_id . '_' . time() . '.' . $ext;

        $file->storeAs(self::DIREKTORI_SELFIE, $nama);

        return $nama;
    }

    /**
     * Kolom tabel `devices` sangat pendek (type_devices 8, model_devices 20,
     * versi_os 8, browser 32, versi_browser 8) — semua nilai dipotong agar
     * insert tidak gagal.
     */
    private function dataPerangkat(Request $request): array
    {
        $d = (array) $request->input('device', []);

        return [
            'ipv4' => mb_substr((string) $request->ip(), 0, 16),
            'type_devices' => mb_substr($d['tipe'] ?? 'Mobile', 0, 8),
            'model_devices' => mb_substr($d['model'] ?? 'Unknown', 0, 20),
            'os' => mb_substr($d['os'] ?? 'Unknown', 0, 64),
            'versi_os' => mb_substr((string) ($d['versi_os'] ?? '0'), 0, 8),
            'browser' => mb_substr($d['aplikasi'] ?? 'Mobile App', 0, 32),
            'versi_browser' => mb_substr((string) ($d['versi_aplikasi'] ?? '0'), 0, 8),
        ];
    }
}
