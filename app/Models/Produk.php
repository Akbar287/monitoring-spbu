<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produk';
    protected $primaryKey = 'produk_id';
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
        'nama_produk',
        'singkatan',
        'is_aktif',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function detail_produk()
    {
        return $this->hasOne(DetailProduk::class, 'produk_id', 'produk_id');
    }

    public function stok_inventory()
    {
        return $this->hasOne(StokInventory::class, 'produk_id', 'produk_id');
    }

    public function harga()
    {
        return $this->hasMany(Harga::class, 'produk_id', 'produk_id');
    }

    public function detail_rencana_pembelian()
    {
        return $this->hasMany(DetailRencanaPembelian::class, 'produk_id', 'produk_id');
    }

    public function file_lo()
    {
        return $this->hasMany(FileLo::class, 'produk_id', 'produk_id');
    }

    public function nozzle()
    {
        return $this->hasMany(Nozzle::class, 'produk_id', 'produk_id');
    }

    public function dombak_transfer()
    {
        return $this->hasMany(DombakTransfer::class, 'produk_id', 'produk_id');
    }
}
