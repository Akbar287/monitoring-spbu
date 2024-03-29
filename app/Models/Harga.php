<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Harga extends Model
{
    use HasFactory;
    protected $table = 'harga';
    protected $primaryKey = 'harga_id';
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
        'produk_id',
        'jam_kerja_id',
        'tanggal',
        'harga_jual',
        'harga_beli',
        'is_default',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function detail_penjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'harga_id', 'harga_id');
    }
}
