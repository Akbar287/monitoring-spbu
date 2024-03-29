<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class MesinPresensi extends Model
{
    use HasFactory;
    protected $table = 'mesin_presensi';
    protected $primaryKey = 'mesin_presensi_id';
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
        'nama_mesin',
        'tipe_koneksi',
        'ip_address',
        'com_port'
    ];

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'mesin_presensi_id', 'mesin_presensi_id');
    }
}
