<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function show(Request $request)
    {
        return ApiResponse::ok(MobileProfile::for($request->user()));
    }

    public function update(Request $request)
    {
        $member = $request->user()->member()->first();

        $request->validate([
            'no_hp' => ['nullable', 'string', 'max:16'],
            'no_wa' => ['nullable', 'string', 'max:16'],
            'email' => ['required', 'email', 'max:255', 'unique:member,email,' . $member->member_id . ',member_id'],
        ]);

        $member->update($request->only(['no_hp', 'no_wa', 'email']));

        return ApiResponse::ok(MobileProfile::for($request->user()->fresh()), 'Profil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            return ApiResponse::fail('Password lama tidak cocok.', 422);
        }

        $user->forceFill(['password' => $request->password])->save();

        // seluruh sesi perangkat lain dicabut, token saat ini dibuat ulang
        $tokenName = $user->currentAccessToken()->name;
        $user->tokens()->delete();
        $token = $user->createToken($tokenName)->plainTextToken;

        return ApiResponse::ok([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Password berhasil diubah. Perangkat lain telah dikeluarkan.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:' . Upload::maxKb()],
        ]);

        $member = $request->user()->member()->first();

        $file = $request->file('avatar');
        $nama = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
            . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/profile_img', $nama);

        $lama = $member->avatar;
        $member->update(['avatar' => $nama]);

        // hapus berkas lama, kecuali avatar bawaan yang dipakai bersama
        if (! empty($lama) && $lama !== 'default.png' && Storage::exists('public/profile_img/' . $lama)) {
            Storage::delete('public/profile_img/' . $lama);
        }

        return ApiResponse::ok([
            'avatar_url' => MobileProfile::avatarUrl($member->fresh()),
        ], 'Foto profil diperbarui.');
    }
}
