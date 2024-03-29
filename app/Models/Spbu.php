<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Spbu extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'spbu';
    protected $primaryKey = 'spbu_id';
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
        'nama_spbu',
        'nomor_spbu',
        'tanggal_pendirian',
        'luas_lahan',
        'satuan_luas',
        'alamat'
    ];

    public function member()
    {
        return $this->belongsToMany(Member::class, 'spbu_member', 'spbu_id', 'memberid');
    }
    public function divisi()
    {
        return $this->hasMany(Divisi::class, 'spbu_id', 'spbu_id');
    }
    public function jam_kerja()
    {
        return $this->hasMany(JamKerja::class, 'spbu_id', 'spbu_id');
    }
    public function fasilitas()
    {
        return $this->hasMany(Fasilitas::class, 'spbu_id', 'spbu_id');
    }
    public function aset()
    {
        return $this->hasMany(Aset::class, 'spbu_id', 'spbu_id');
    }
    public function dombak()
    {
        return $this->hasMany(Dombak::class, 'spbu_id', 'spbu_id');
    }
    public function payung()
    {
        return $this->hasMany(Payung::class, 'spbu_id', 'spbu_id');
    }
    public function petty_cash()
    {
        return $this->hasMany(PettyCash::class, 'spbu_id', 'spbu_id');
    }
    public function produk()
    {
        return $this->hasMany(Produk::class, 'spbu_id', 'spbu_id');
    }
    public function rencana_pembelian()
    {
        return $this->hasMany(RencanaPembelian::class, 'spbu_id', 'spbu_id');
    }
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'spbu_id', 'spbu_id');
    }
    public function tera()
    {
        return $this->hasMany(Tera::class, 'spbu_id', 'spbu_id');
    }
}
