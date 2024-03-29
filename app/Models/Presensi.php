<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Presensi extends Model
{
    use HasFactory;
    protected $table = 'presensi';
    protected $primaryKey = 'presensi_id';
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
        'status_presensi_id',
        'mesin_presensi_id',
        'jam_kerja_id',
        'member_id',
        'tanggal',
        'waktu_datang',
        'waktu_selesai',
        'is_verified',
        'is_aktif',
        'terlambat',
        'keterangan',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }
    public function status_presensi()
    {
        return $this->belongsTo(StatusPresensi::class, 'status_presensi_id', 'member_id');
    }
    public function mesin_presensi()
    {
        return $this->belongsTo(MesinPresensi::class, 'mesin_presensi_id', 'mesin_presensi_id');
    }
    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }
}
