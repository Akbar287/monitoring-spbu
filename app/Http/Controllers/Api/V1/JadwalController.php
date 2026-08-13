<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Penjadwalan;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Jadwal kerja milik pegawai yang sedang login.
 * Aksi absen masuk/pulang ada di KehadiranController.
 */
class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:semua,belum,berjalan,selesai'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $member = $request->user()->member()->first();

        $dari = $request->input('dari', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->input('sampai', Carbon::now()->endOfMonth()->format('Y-m-d'));
        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $query = Penjadwalan::where('member_id', $member->member_id)
            ->whereBetween('tanggal', [$dari, $sampai]);

        match ($request->input('status', 'semua')) {
            'belum' => $query->whereNull('start_time'),
            'berjalan' => $query->whereNotNull('start_time')->whereNull('finish_time'),
            'selesai' => $query->whereNotNull('finish_time'),
            default => null,
        };

        $paginator = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::paginated($paginator, fn (Penjadwalan $p) => self::serialize($p));
    }

    public function show(Request $request, Penjadwalan $penjadwalan)
    {
        if ($gagal = self::pastikanMilikSendiri($request, $penjadwalan)) {
            return $gagal;
        }

        return ApiResponse::ok(self::serialize($penjadwalan, lengkap: true));
    }

    /**
     * Jadwal yang sedang berjalan (sudah absen masuk, belum pulang).
     * Dipakai layar Beranda untuk menampilkan banner "shift berjalan".
     */
    public function berjalan(Request $request)
    {
        $member = $request->user()->member()->first();

        $ongoing = $member->penjadwalan()
            ->whereNotNull('start_time')
            ->whereNull('finish_time')
            ->orderBy('start_time', 'desc')
            ->first();

        return ApiResponse::ok($ongoing ? self::serialize($ongoing, lengkap: true) : null);
    }

    /**
     * Penjaga akses: satu pegawai hanya boleh melihat & mengabsen jadwalnya sendiri.
     * (Alur web lama tidak melakukan pengecekan ini — lihat MOBILE_API.md.)
     */
    public static function pastikanMilikSendiri(Request $request, Penjadwalan $penjadwalan): ?\Illuminate\Http\JsonResponse
    {
        $member = $request->user()->member()->first();

        if (is_null($member) || $penjadwalan->member_id !== $member->member_id) {
            return ApiResponse::fail('Jadwal ini bukan milik Anda.', 403);
        }

        return null;
    }

    public static function serialize(Penjadwalan $p, bool $lengkap = false): array
    {
        $jamKerja = $p->jam_kerja()->first();

        $data = [
            'penjadwalan_id' => $p->penjadwalan_id,
            'tanggal' => $p->tanggal,
            'mode' => self::mode($p),
            'status_kehadiran' => optional($p->status_kehadiran()->first())->nama_kehadiran,
            'jabatan' => optional($p->jabatan()->first())->nama_jabatan,
            'jam_kerja' => $jamKerja ? [
                'nama' => $jamKerja->nama_jam_kerja,
                'urutan' => $jamKerja->urutan,
                'jam_datang' => $jamKerja->jam_datang_in,
                'jam_pulang' => $jamKerja->jam_pulang_out,
                'batas_telat' => $jamKerja->batas_telat,
            ] : null,
            'deskripsi' => $p->deskripsi,
            'is_attended' => filter_var($p->is_attended, FILTER_VALIDATE_BOOLEAN),
            'is_confirm' => filter_var($p->is_confirm, FILTER_VALIDATE_BOOLEAN),
            'start_time' => $p->start_time,
            'finish_time' => $p->finish_time,
            'nozzles' => $p->nozzle()->get()->pluck('nama_nozzle')->values()->all(),
        ];

        if ($lengkap) {
            $data['bukti'] = [
                'selfie' => $p->image_selfie()->get()
                    ->map(fn ($i) => asset('storage/image_selfie/' . $i->nama_file))
                    ->values()->all(),
                'lokasi' => $p->location()->get()->map(fn ($l) => [
                    'latitude' => (float) $l->lattitude,
                    'longitude' => (float) $l->longitude,
                    'gmt' => (int) $l->gmt,
                    'deskripsi' => $l->deskripsi,
                ])->values()->all(),
                'perangkat' => $p->devices()->get()->map(fn ($d) => [
                    'tipe' => $d->type_devices,
                    'model' => $d->model_devices,
                    'os' => $d->os,
                    'aplikasi' => $d->browser,
                ])->values()->all(),
            ];
        }

        return $data;
    }

    /**
     * in    = perlu absen masuk (scan QR)
     * out   = sedang bertugas, perlu absen pulang
     * done  = sudah selesai
     */
    private static function mode(Penjadwalan $p): string
    {
        if (! is_null($p->finish_time)) {
            return 'done';
        }

        return is_null($p->start_time) ? 'in' : 'out';
    }
}
