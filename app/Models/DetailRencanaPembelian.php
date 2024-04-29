<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailRencanaPembelian extends Model
{
    use HasFactory;
    protected $table = 'detail_rencana_pembelian';
    protected $primaryKey = 'detail_rencana_pembelian_id';
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
        'rencana_pembelian_id',
        'produk_id',
        'jumlah',
        'satuan_jumlah',
        'rit',
        'harga',
        'total_harga',
        'is_confirmation',
        'ms2',
        'delivery',
        'kode_detail'
    ];

    public function rencana_pembelian()
    {
        return $this->belongsTo(RencanaPembelian::class, 'rencana_pembelian_id', 'rencana_pembelian_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    public function file_lo()
    {
        return $this->belongsToMany(FileLo::class, 'detail_rencana_file', 'detail_rencana_pembelian_id', 'file_lo_id');
    }
}
