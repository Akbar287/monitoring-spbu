<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Konversi extends Model
{
    use HasFactory;
    protected $table = 'konversi';
    protected $primaryKey = 'konversi_id';
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
        'satuan_ukur_tinggi_id',
        'satuan_ukur_volume_id',
        'dombak_id',
        'tinggi',
        'volume'
    ];

    public function satuan_ukur_tinggi()
    {
        return $this->belongsTo(SatuanUkurTinggi::class, 'satuan_ukur_tinggi_id', 'satuan_ukur_tinggi_id');
    }

    public function satuan_ukur_volume()
    {
        return $this->belongsTo(SatuanUkurVolume::class, 'satuan_ukur_volume_id', 'satuan_ukur_volume_id');
    }

    public function dombak()
    {
        return $this->belongsTo(Dombak::class, 'dombak_id', 'dombak_id');
    }

    public function dokumen_stok()
    {
        return $this->belongsToMany(DokumenStok::class, 'konversi_dokumen_stok', 'konversi_id', 'dokumen_stok_id');
    }
}
