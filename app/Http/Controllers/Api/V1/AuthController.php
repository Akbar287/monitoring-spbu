<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserLogin;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Autentikasi token untuk aplikasi mobile (Sanctum personal access token).
 * Berbeda dengan web yang memakai sesi; di sini klien menyimpan token dan
 * mengirimkannya sebagai header `Authorization: Bearer <token>`.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:64'],
        ]);

        $user = UserLogin::where('username', $request->username)->first();

        if (is_null($user) || ! Hash::check($request->password, $user->password)) {
            // pesan sengaja generik agar tidak membocorkan username mana yang ada
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        if (! filter_var($user->is_aktif, FILTER_VALIDATE_BOOLEAN)) {
            return ApiResponse::fail('Akun Anda tidak aktif. Hubungi Penanggung Jawab.', 403);
        }

        if (is_null($user->member()->first())) {
            return ApiResponse::fail('Akun ini belum tertaut ke data pegawai.', 403);
        }

        $deviceName = $request->input('device_name') ?: 'mobile';

        // satu device satu token: token lama dengan nama sama dicabut agar
        // daftar token tidak menumpuk setiap kali user login ulang
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->forceFill(['last_login' => now()])->save();

        return ApiResponse::ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => MobileProfile::for($user),
        ], 'Berhasil masuk.');
    }

    public function me(Request $request)
    {
        return ApiResponse::ok(MobileProfile::for($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::ok([], 'Berhasil keluar.');
    }

    /**
     * Cabut seluruh token milik user (mis. saat kehilangan perangkat).
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponse::ok([], 'Semua sesi perangkat telah dikeluarkan.');
    }
}
