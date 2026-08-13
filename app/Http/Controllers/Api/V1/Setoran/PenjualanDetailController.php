<?php

namespace App\Http\Controllers\Api\V1\Setoran;

use App\Http\Controllers\Controller;
use App\Models\Harga;
use App\Models\Jabatan;
use App\Models\Penjualan;
use App\Models\StandMeter;
use App\Models\StatusSetoran;
use App\Support\ApiResponse;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pembuatan setoran (Open Penjualan) dan pengelolaan isinya:
 * baris stand meter serta penanggung jawab operasional shift.
 */
class PenjualanDetailController extends Controller
{
    /**
     * Stand meter yang siap dimasukkan ke setoran: sudah terkonfirmasi pada
     * tanggal & shift tersebut dan belum dipakai setoran mana pun.
     */
    public function standMeterTersedia(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
        ]);

        $items = StandMeter::where('tanggal', $request->input('tanggal'))
            ->where('jam_kerja_id', $request->input('jam_kerja_id'))
            ->whereDoesntHave('detail_penjualan')
            ->get()
            ->map(function (StandMeter $s) {
                $nozzle = $s->nozzle()->first();
                $produk = $nozzle?->produk()->first();
                $harga = $produk?->harga()->where('is_default', true)->first();
                $liter = Uang::kurang($s->stand_meter_akhir, $s->stand_meter_awal);

                return [
                    'value' => $s->stand_meter_id,
                    'label' => (optional($nozzle)->nama_nozzle ?? '-') . ' — ' . $liter . ' L',
                    'stand_meter_id' => $s->stand_meter_id,
                    'nozzle' => optional($nozzle)->nama_nozzle,
                    'nozzle_id' => optional($nozzle)->nozzle_id,
                    'produk' => optional($produk)->nama_produk,
                    'stand_meter_awal' => Uang::of($s->stand_meter_awal),
                    'stand_meter_akhir' => Uang::of($s->stand_meter_akhir),
                    'liter' => $liter,
                    'harga_jual' => $harga ? Uang::of($harga->harga_jual) : null,
                    'total' => $harga ? Uang::kali($liter, $harga->harga_jual) : null,
                    'is_confirmation' => filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN),
                    'siap' => ! is_null($harga) && filter_var($s->is_confirmation, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->sortBy('nozzle')->values()->all();

        return ApiResponse::ok(['items' => $items]);
    }

    /** Buka setoran baru untuk satu shift, sekaligus isi baris stand meter. */
    public function store(Request $request)
    {
        $request->validate([
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'stand_meter_id' => ['required', 'array', 'min:1'],
            'stand_meter_id.*' => ['exists:stand_meter,stand_meter_id'],
        ]);

        $status = StatusSetoran::where('nama_status', SetoranController::TAHAP['open'])->first();
        if (is_null($status)) {
            return ApiResponse::fail('Status "Open Penjualan" belum ada di tabel status_setoran.', 422);
        }

        $penjualan = DB::transaction(function () use ($request, $status) {
            $p = Penjualan::where('spbu_id', $request->input('spbu_id'))
                ->where('status_setoran_id', $status->status_setoran_id)
                ->where('jam_kerja_id', $request->input('jam_kerja_id'))
                ->where('tanggal', $request->input('tanggal'))
                ->first()
                ?? Penjualan::create([
                    'status_setoran_id' => $status->status_setoran_id,
                    'spbu_id' => $request->input('spbu_id'),
                    'jam_kerja_id' => $request->input('jam_kerja_id'),
                    'tanggal' => $request->input('tanggal'),
                    'total_penjualan' => 0,
                    'total_uang_kotak' => 0,
                    'is_accepted_admin' => 0,
                    'is_accepted_top' => 0,
                ]);

            foreach ($request->input('stand_meter_id') as $smId) {
                $this->tambahBaris($p, $smId);
            }

            $this->hitungUlangTotal($p);

            return $p;
        });

        return ApiResponse::ok(
            ['penjualan_id' => $penjualan->penjualan_id],
            'Setoran dibuka.',
            201
        );
    }

    public function tambahStandMeter(Request $request, string $id)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $request->validate(['stand_meter_id' => ['required', 'exists:stand_meter,stand_meter_id']]);

        try {
            DB::transaction(function () use ($p, $request) {
                $this->tambahBaris($p, $request->input('stand_meter_id'));
                $this->hitungUlangTotal($p);
            });
        } catch (\RuntimeException $e) {
            return ApiResponse::fail($e->getMessage(), 422);
        }

        return ApiResponse::ok(['penjualan_id' => $p->penjualan_id], 'Stand meter ditambahkan.');
    }

    public function hapusStandMeter(string $id, string $detailId)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $detail = $p->detail_penjualan()->where('detail_penjualan_id', $detailId)->first();
        if (is_null($detail)) {
            return ApiResponse::fail('Baris tidak ditemukan.', 404);
        }

        if (filter_var($detail->is_confirmed, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Baris yang sudah dikonfirmasi tidak bisa dihapus.', 422);
        }

        DB::transaction(function () use ($detail, $p) {
            $detail->delete();
            $this->hitungUlangTotal($p);
        });

        return ApiResponse::ok(['penjualan_id' => $p->penjualan_id], 'Baris stand meter dihapus.');
    }

    // ------------------------------------------------- penanggung jawab shift

    public function tambahPj(Request $request, string $id)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $request->validate([
            'jabatan_id' => ['required', 'exists:jabatan,jabatan_id'],
            'member_id' => ['required', 'exists:member,member_id'],
        ]);

        $ada = $p->pj_operasional_setoran()
            ->where('jabatan_id', $request->input('jabatan_id'))
            ->where('member_id', $request->input('member_id'))
            ->exists();

        if ($ada) {
            return ApiResponse::fail('Petugas ini sudah tercatat pada jabatan tersebut.', 422);
        }

        $p->pj_operasional_setoran()->create([
            'jabatan_id' => $request->input('jabatan_id'),
            'member_id' => $request->input('member_id'),
        ]);

        return ApiResponse::ok(['penjualan_id' => $p->penjualan_id], 'Penanggung jawab ditambahkan.');
    }

    public function hapusPj(string $id, string $pjId)
    {
        $p = Penjualan::find($id);
        if (is_null($p)) {
            return ApiResponse::fail('Data setoran tidak ditemukan.', 404);
        }

        $pj = $p->pj_operasional_setoran()->where('pj_operasional_setoran_id', $pjId)->first();
        if (is_null($pj)) {
            return ApiResponse::fail('Penanggung jawab tidak ditemukan.', 404);
        }

        $pj->delete();

        return ApiResponse::ok(['penjualan_id' => $p->penjualan_id], 'Penanggung jawab dilepas.');
    }

    /** Pegawai yang memegang sebuah jabatan — untuk memilih PJ. */
    public function petugas(string $jabatanId)
    {
        $jabatan = Jabatan::find($jabatanId);
        if (is_null($jabatan)) {
            return ApiResponse::fail('Jabatan tidak ditemukan.', 404);
        }

        $items = $jabatan->member()->get()->map(fn ($m) => [
            'value' => $m->member_id,
            'label' => optional($m->ktp()->first())->nama ?? $m->email,
        ])->sortBy('label')->values()->all();

        return ApiResponse::ok(['jabatan' => $jabatan->nama_jabatan, 'items' => $items]);
    }

    // --------------------------------------------------------------- privat

    private function tambahBaris(Penjualan $p, string $standMeterId): void
    {
        $sm = StandMeter::find($standMeterId);
        if (is_null($sm)) {
            throw new \RuntimeException('Stand meter tidak ditemukan.');
        }

        if ($p->detail_penjualan()->where('stand_meter_id', $sm->stand_meter_id)->exists()) {
            return; // idempoten
        }

        $produk = $sm->nozzle()->first()?->produk()->first();
        $harga = $produk?->harga()->where('is_default', true)->first();

        if (is_null($harga)) {
            throw new \RuntimeException(
                'Produk ' . (optional($produk)->nama_produk ?? '?') . ' belum punya harga berlaku. '
                . 'Tetapkan harga lebih dulu di Master → Harga.'
            );
        }

        $liter = Uang::kurang($sm->stand_meter_akhir, $sm->stand_meter_awal);

        $p->detail_penjualan()->create([
            'stand_meter_id' => $sm->stand_meter_id,
            'harga_id' => $harga->harga_id,
            'liter' => $liter,
            'total_detail_jual' => Uang::kali($liter, $harga->harga_jual),
            'is_confirmed' => 0,
        ]);
    }

    /**
     * Total penjualan selalu diturunkan ulang dari seluruh baris detail.
     * Alur web menjumlahkannya sambil berjalan dan menambahkan nilai yang sama
     * dua kali pada baris baru, sehingga totalnya bisa menggelembung.
     */
    private function hitungUlangTotal(Penjualan $p): void
    {
        $p->update([
            'total_penjualan' => Uang::jumlah($p->detail_penjualan()->get(), 'total_detail_jual'),
        ]);
    }
}
