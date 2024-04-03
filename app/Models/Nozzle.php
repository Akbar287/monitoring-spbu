<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Nozzle extends Model
{
    use HasFactory;
    protected $table = 'nozzle';
    protected $primaryKey = 'nozzle_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
    public $timestamps = false;
    protected $fillable = [
        'dispenser_id',
        'produk_id',
        'nama_nozzle',
        'keterangan',
        'is_aktif'
    ];

    public function dispenser()
    {
        return $this->belongsTo(Dispenser::class, 'dispenser_id', 'dispenser_id');
    }

    public function stand_meter()
    {
        return $this->hasMany(StandMeter::class, 'nozzle_id', 'nozzle_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    public function penjadwalan()
    {
        return $this->belongsToMany(Penjadwalan::class, 'penjadwalan_nozzle', 'nozzle_id', 'penjadwalan_id');
    }
}
