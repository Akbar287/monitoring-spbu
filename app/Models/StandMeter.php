<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StandMeter extends Model
{
    use HasFactory;
    protected $table = 'stand_meter';
    protected $primaryKey = 'stand_meter_id';
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
        'nozzle_id',
        'dombak_id',
        'jam_kerja_id',
        'tanggal',
        'stand_meter_awal',
        'stand_meter_akhir',
        'is_confirmation'
    ];

    public function nozzle()
    {
        return $this->belongsTo(Nozzle::class, 'nozzle_id', 'nozzle_id');
    }

    public function dombak()
    {
        return $this->belongsTo(Dombak::class, 'dombak_id', 'dombak_id');
    }

    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function detail_penjualan()
    {
        return $this->hasOne(DetailPenjualan::class, 'stand_meter_id', 'stand_meter_id');
    }

    public function file_stand_meter()
    {
        return $this->hasMany(FileStandMeter::class, 'stand_meter_id', 'stand_meter_id');
    }

    public function dokumen_stok()
    {
        return $this->belongsToMany(DokumenStok::class, 'stand_meter_dokumen_stok', 'stand_meter_id', 'dokumen_stok_id');
    }
}
