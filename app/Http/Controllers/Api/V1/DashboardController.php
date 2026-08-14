<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\StokInventory;
use App\Support\Akses;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Ringkasan layar Beranda aplikasi mobile.
 * Bagian `ringkasan` (angka penjualan & stok) hanya diisi untuk jabatan
 * berakses penuh — aturan yang sama dengan akses menu, lihat App\Support\Akses.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $member = $user->member()->first();
        $profil = MobileProfile::for($user);
        $hariIni = Carbon::now()->format('Y-m-d');

        // divisi stakeholder (mis. Top Manajemen) tidak menjalani jadwal kerja
        $tanpaJadwal = Akses::tanpaJadwal($member);

        $ongoing = $tanpaJadwal ? null : $member->penjadwalan()
            ->whereNotNull('start_time')
            ->whereNull('finish_time')
            ->orderBy('start_time', 'desc')
            ->first();

        $jadwalHariIni = $tanpaJadwal
            ? collect()
            : $member->penjadwalan()->where('tanggal', $hariIni)->get();

        $data = [
            'user' => $profil,
            'tanggal' => $hariIni,
            'tanpa_jadwal' => $tanpaJadwal,
            'shift_berjalan' => $ongoing ? JadwalController::serialize($ongoing) : null,
            'jadwal_hari_ini' => [
                'total' => $jadwalHariIni->count(),
                'belum_absen' => $jadwalHariIni->whereNull('start_time')->count(),
                'items' => $jadwalHariIni
                    ->sortBy(fn ($p) => optional($p->jam_kerja()->first())->urutan ?? 0)
                    ->take(5)
                    ->map(fn ($p) => JadwalController::serialize($p))
                    ->values()->all(),
            ],
            'notifikasi_belum_dibaca' => $member->notifikasi()->where('is_read', false)->count(),
            'ringkasan' => [],
        ];

        // ringkasan operasional memakai aturan yang sama dengan akses menu
        if (Akses::penuh($member)) {
            $data['ringkasan'] = $this->ringkasanManajerial($member);
        }

        return ApiResponse::ok($data);
    }

    /**
     * Angka operasional singkat untuk PJ & direksi. Dibatasi pada SPBU tempat
     * member ditempatkan; bila tidak ada penempatan, angkanya konsolidasi.
     */
    private function ringkasanManajerial(\App\Models\Member $member): array
    {
        $spbu = MobileProfile::spbuUtama($member);
        $hariIni = Carbon::now()->format('Y-m-d');
        $awalBulan = Carbon::now()->startOfMonth()->format('Y-m-d');

        $penjualan = Penjualan::query()
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id));

        $stok = StokInventory::query()
            ->when($spbu, fn ($q) => $q->whereIn(
                'produk_id',
                \App\Models\Produk::where('spbu_id', $spbu->spbu_id)->pluck('produk_id')
            ))
            ->get()
            ->map(fn (StokInventory $s) => [
                'produk' => optional($s->produk()->first())->nama_produk,
                'stok' => (float) $s->stok,
            ])
            ->filter(fn ($row) => ! is_null($row['produk']))
            ->values()->all();

        return [
            'spbu' => $spbu ? $spbu->nama_spbu . ' (' . $spbu->nomor_spbu . ')' : 'Semua SPBU',
            'penjualan_hari_ini' => (float) (clone $penjualan)->where('tanggal', $hariIni)->sum('total_penjualan'),
            'penjualan_bulan_ini' => (float) (clone $penjualan)->whereBetween('tanggal', [$awalBulan, $hariIni])->sum('total_penjualan'),
            'setoran_menunggu_persetujuan' => (clone $penjualan)
                ->where(fn ($q) => $q->where('is_accepted_admin', false)->orWhere('is_accepted_top', false))
                ->count(),
            'stok' => $stok,
        ];
    }
}
