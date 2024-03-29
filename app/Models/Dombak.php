<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Dombak extends Model
{
    use HasFactory;
    protected $table = 'dombak';
    protected $primaryKey = 'dombak_id';
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
        'spbu_id',
        'nama_dombak',
        'keterangan',
        'is_aktif'
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function konversi()
    {
        return $this->hasMany(Konversi::class, 'dombak_id', 'dombak_id');
    }

    public function payung()
    {
        return $this->belongsToMany(Payung::class, 'dombak_payung', 'dombak_id', 'payung_id');
    }

    public function stok_inventory_dombak()
    {
        return $this->hasMany(StokInventoryDombak::class, 'dombak_id', 'dombak_id');
    }

    public function stand_meter()
    {
        return $this->hasMany(StandMeter::class, 'dombak_id', 'dombak_id');
    }

    public function dokumen_stok()
    {
        return $this->hasMany(DokumenStok::class, 'dombak_id', 'dombak_id');
    }
}
