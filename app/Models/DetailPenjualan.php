<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailPenjualan extends Model
{
    use HasFactory;
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'detail_penjualan_id';
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
        'penjualan_id',
        'stand_meter_id',
        'harga_id',
        'liter',
        'total_detail_jual',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id', 'penjualan_id');
    }

    public function stand_meter()
    {
        return $this->belongsTo(StandMeter::class, 'stand_meter_id', 'stand_meter_id');
    }

    public function harga()
    {
        return $this->belongsTo(Harga::class, 'harga_id', 'harga_id');
    }
}
