<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Pengaturan tingkat sistem berbentuk kunci/nilai.
 * Pembacaan & penulisannya sebaiknya lewat App\Support\Setup agar
 * daftar kunci yang dipakai tetap terkumpul di satu tempat.
 */
class PengaturanSistem extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sistem';
    protected $primaryKey = 'pengaturan_sistem_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'kunci',
        'nilai',
    ];

    public static function ambil(string $kunci, ?string $bawaan = null): ?string
    {
        return static::where('kunci', $kunci)->value('nilai') ?? $bawaan;
    }

    public static function simpan(string $kunci, ?string $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    public static function hapus(string $kunci): void
    {
        static::where('kunci', $kunci)->delete();
    }
}
