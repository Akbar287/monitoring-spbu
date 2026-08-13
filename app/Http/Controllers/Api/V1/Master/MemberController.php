<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Jabatan;
use App\Models\Ktp;
use App\Models\Member;
use App\Models\Spbu;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Upload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * CRUD pegawai. Satu pegawai tersebar di tiga tabel — `ktp` (identitas),
 * `member` (kepegawaian), dan `userlogin` (kredensial) — sehingga store/update
 * ditulis ulang, tidak memakai implementasi generik CrudController.
 */
class MemberController extends CrudController
{
    protected function model(): string { return Member::class; }
    protected function label(): string { return 'Pegawai'; }
    protected function urutan(): array { return ['created_at', 'desc']; }

    protected function terapkanCari(Builder $query, string $cari): void
    {
        $pola = '%' . mb_strtolower($cari) . '%';

        $query->where(function (Builder $q) use ($pola) {
            $q->whereRaw('LOWER(email) LIKE ?', [$pola])
                ->orWhereHas('ktp', fn ($k) => $k->whereRaw('LOWER(nama) LIKE ?', [$pola])->orWhere('nik', 'like', $pola))
                ->orWhereHas('user_login', fn ($u) => $u->whereRaw('LOWER(username) LIKE ?', [$pola]));
        });
    }

    // -------------------------------------------------------------- validasi

    protected function aturan(Request $r, ?Model $m): array
    {
        /** @var Member|null $m */
        $memberId = $m?->member_id;
        $loginId = $m?->user_login()->first()?->user_login_id;

        return [
            // ktp
            'nik' => ['required', 'string', 'max:16'],
            'nama' => ['required', 'string', 'max:128'],
            'jenis_kelamin' => ['required', Rule::in(['pria', 'wanita'])],
            'tempat_lahir' => ['required', 'string', 'max:128'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d'],
            'verified' => ['nullable', 'boolean'],

            // member
            'status_member_id' => ['required', 'exists:status_member,status_member_id'],
            'email' => ['required', 'email', 'max:255', Rule::unique('member', 'email')->ignore($memberId, 'member_id')],
            'no_hp' => ['nullable', 'string', 'max:16'],
            'no_wa' => ['nullable', 'string', 'max:16'],
            'bergabung_sejak' => ['required', 'date_format:Y-m-d'],

            // userlogin — password wajib hanya saat membuat pegawai baru
            'username' => ['required', 'string', 'max:32', Rule::unique('userlogin', 'username')->ignore($loginId, 'user_login_id')],
            'password' => [$m ? 'nullable' : 'required', 'string', Password::min(8)],
            'is_aktif' => ['required', 'boolean'],

            // opsional saat membuat
            'jabatan_id' => ['nullable', 'exists:jabatan,jabatan_id'],
            'spbu_id' => ['nullable', 'exists:spbu,spbu_id'],
        ];
    }

    /** Tidak dipakai — store/update di kelas ini ditulis manual. */
    protected function isi(Request $r): array
    {
        return [];
    }

    // ------------------------------------------------------------------ CRUD

    public function store(Request $request)
    {
        $request->validate($this->aturan($request, null));

        $member = DB::transaction(function () use ($request) {
            $ktp = Ktp::create([
                'nik' => $request->input('nik'),
                'nama' => $request->input('nama'),
                'jenis_kelamin' => $request->input('jenis_kelamin'),
                'tempat_lahir' => $request->input('tempat_lahir'),
                'tanggal_lahir' => $request->input('tanggal_lahir'),
                'verified' => self::bool($request, 'verified'),
            ]);

            $member = $ktp->member()->create([
                'status_member_id' => $request->input('status_member_id'),
                'email' => $request->input('email'),
                'no_hp' => $request->input('no_hp'),
                'no_wa' => $request->input('no_wa'),
                'avatar' => 'default.png',
                'bergabung_sejak' => $request->input('bergabung_sejak'),
            ]);

            $member->user_login()->create([
                'username' => $request->input('username'),
                'password' => $request->input('password'), // cast 'hashed' di UserLogin
                'is_aktif' => self::bool($request, 'is_aktif', true),
            ]);

            if ($request->filled('jabatan_id')) {
                $member->jabatan()->syncWithoutDetaching([$request->input('jabatan_id')]);
            }
            if ($request->filled('spbu_id')) {
                $member->spbu()->syncWithoutDetaching([$request->input('spbu_id')]);
            }

            return $member;
        });

        return ApiResponse::ok($this->lengkap($member->fresh()), 'Pegawai berhasil ditambahkan.', 201);
    }

    public function update(Request $request, string $id)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $request->validate($this->aturan($request, $member));

        DB::transaction(function () use ($request, $member) {
            $member->ktp()->first()?->update([
                'nik' => $request->input('nik'),
                'nama' => $request->input('nama'),
                'jenis_kelamin' => $request->input('jenis_kelamin'),
                'tempat_lahir' => $request->input('tempat_lahir'),
                'tanggal_lahir' => $request->input('tanggal_lahir'),
                'verified' => self::bool($request, 'verified'),
            ]);

            $member->update([
                'status_member_id' => $request->input('status_member_id'),
                'email' => $request->input('email'),
                'no_hp' => $request->input('no_hp'),
                'no_wa' => $request->input('no_wa'),
                'bergabung_sejak' => $request->input('bergabung_sejak'),
            ]);

            $login = $member->user_login()->first();
            if ($login) {
                $atribut = [
                    'username' => $request->input('username'),
                    'is_aktif' => self::bool($request, 'is_aktif', true),
                ];
                // password kosong = tidak diubah
                if ($request->filled('password')) {
                    $atribut['password'] = $request->input('password');
                }
                $login->update($atribut);
            }
        });

        return ApiResponse::ok($this->lengkap($member->fresh()), 'Pegawai berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        if ($member->member_id === request()->user()->member()->first()?->member_id) {
            return ApiResponse::fail('Anda tidak bisa menghapus akun Anda sendiri.', 422);
        }

        // ktp, member, dan userlogin sama-sama soft delete, jadi riwayat
        // transaksi yang menunjuk member ini tetap utuh
        DB::transaction(function () use ($member) {
            $member->user_login()->delete();
            $member->ktp()->delete();
            $member->delete();
        });

        return ApiResponse::ok([], 'Pegawai berhasil dinonaktifkan (soft delete).');
    }

    // -------------------------------------------------------------- tambahan

    public function resetPassword(string $id)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $login = $member->user_login()->first();
        if (! $login) {
            return ApiResponse::fail('Pegawai ini belum punya akun login.', 422);
        }

        $login->update(['password' => '12345678']); // mengikuti default web
        $login->tokens()->delete();                 // paksa keluar dari semua perangkat

        return ApiResponse::ok(
            ['password_baru' => '12345678'],
            'Password direset ke 12345678. Minta pegawai segera menggantinya.'
        );
    }

    public function unggahAvatar(Request $request, string $id)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $request->validate(['avatar' => ['required', 'image', 'max:' . Upload::maxKb()]]);

        $file = $request->file('avatar');
        $nama = 'avatar_' . $member->member_id . '_' . time() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
        $file->storeAs('public/profile_img', $nama);

        $lama = $member->avatar;
        $member->update(['avatar' => $nama]);

        if (! empty($lama) && $lama !== 'default.png' && Storage::exists('public/profile_img/' . $lama)) {
            Storage::delete('public/profile_img/' . $lama);
        }

        return ApiResponse::ok(
            ['avatar_url' => MobileProfile::avatarUrl($member->fresh())],
            'Foto pegawai diperbarui.'
        );
    }

    public function tambahJabatan(Request $request, string $id)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $request->validate(['jabatan_id' => ['required', 'exists:jabatan,jabatan_id']]);
        $member->jabatan()->syncWithoutDetaching([$request->input('jabatan_id')]);

        return ApiResponse::ok($this->lengkap($member->fresh()), 'Jabatan ditambahkan.');
    }

    public function hapusJabatan(string $id, string $jabatanId)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $member->jabatan()->detach($jabatanId);

        return ApiResponse::ok($this->lengkap($member->fresh()), 'Jabatan dilepas.');
    }

    public function tambahSpbu(Request $request, string $id)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $request->validate(['spbu_id' => ['required', 'exists:spbu,spbu_id']]);
        $member->spbu()->syncWithoutDetaching([$request->input('spbu_id')]);

        return ApiResponse::ok($this->lengkap($member->fresh()), 'Penempatan SPBU ditambahkan.');
    }

    public function hapusSpbu(string $id, string $spbuId)
    {
        $member = $this->temukan($id);
        if (! $member) {
            return ApiResponse::fail('Pegawai tidak ditemukan.', 404);
        }

        $member->spbu()->detach($spbuId);

        return ApiResponse::ok($this->lengkap($member->fresh()), 'Penempatan SPBU dilepas.');
    }

    // --------------------------------------------------------- serialisasi

    protected function ringkas(Model $m): array
    {
        /** @var Member $m */
        $ktp = $m->ktp()->first();
        $jabatan = $m->jabatan()->first();
        $login = $m->user_login()->first();

        return [
            'id' => $m->member_id,
            'judul' => optional($ktp)->nama ?? $m->email,
            'subjudul' => trim((optional($jabatan)->nama_jabatan ?? 'Tanpa jabatan') . ' · ' . (optional($login)->username ?? 'tanpa akun')),
            'member_id' => $m->member_id,
            'nama' => optional($ktp)->nama,
            'email' => $m->email,
            'username' => optional($login)->username,
            'jabatan' => optional($jabatan)->nama_jabatan,
            'avatar_url' => MobileProfile::avatarUrl($m),
            'is_aktif' => filter_var(optional($login)->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Member $m */
        $ktp = $m->ktp()->first();
        $login = $m->user_login()->first();

        return array_merge($this->ringkas($m), [
            'nik' => optional($ktp)->nik,
            'jenis_kelamin' => optional($ktp)->jenis_kelamin,
            'tempat_lahir' => optional($ktp)->tempat_lahir,
            'tanggal_lahir' => optional($ktp)->tanggal_lahir,
            'verified' => filter_var(optional($ktp)->verified, FILTER_VALIDATE_BOOLEAN),
            'status_member_id' => $m->status_member_id,
            'status_member' => optional($m->status_member()->first())->nama_status,
            'no_hp' => $m->no_hp,
            'no_wa' => $m->no_wa,
            'bergabung_sejak' => $m->bergabung_sejak,
            'daftar_jabatan' => $m->jabatan()->get()->map(fn (Jabatan $j) => [
                'jabatan_id' => $j->jabatan_id,
                'nama_jabatan' => $j->nama_jabatan,
                'level' => optional($j->level()->first())->nama_level,
            ])->values()->all(),
            'daftar_spbu' => $m->spbu()->get()->map(fn (Spbu $s) => [
                'spbu_id' => $s->spbu_id,
                'nama_spbu' => $s->nama_spbu,
            ])->values()->all(),
            'punya_akun' => ! is_null($login),
        ]);
    }
}
