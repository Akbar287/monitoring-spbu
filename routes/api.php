<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JadwalKerjaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/chart', [HomeController::class, 'chart'])->name('home.chart');

Route::middleware(['auth', 'cache.header'])->group(function ($route) {
    $route->post('/jadwalkerja/cek', [JadwalKerjaController::class, 'cek'])->name('jadwal_kerja.cek_api');
    $route->post('/jadwal_absensi/jadwal', [JadwalKerjaController::class, 'jadwal'])->name('jadwal_kerja.jadwal_api');
});
