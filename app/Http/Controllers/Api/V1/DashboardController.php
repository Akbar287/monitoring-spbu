<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\StokInventory;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Ringkasan layar Beranda aplikasi mobile.
 * Isinya menyesuaikan level pegawai — sama seperti sidebar web yang disusun
 * App\Support\Navigation.
 */
class DashboardController extends Controller
{
    private const LEVEL_MANAJERIAL = ['Penanggung Jawab', 'Komisaris', 'Direktur Utama', 'Direktur'];

    public function index(Request $request)
    {
        $user = $request->user();
        $member = $user->member()->first();
        $profil = MobileProfile::for($user);
        $hariIni = Carbon::now()->format('Y-m-d');

        $ongoing = $member->penjadwalan()
            ->whereNotNull('start_time')
            ->whereNull('finish_time')
            ->orderBy('start_time', 'desc')
            ->first();

        $jadwalHariIni = $member->penjadwalan()
            ->where('tanggal', $hariIni)
            ->get();

        $data = [
            'user' => $profil,
            'tanggal' => $hariIni,
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

        if (in_array($profil['level'], self::LEVEL_MANAJERIAL, true)) {
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
