<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FileLo extends Model
{
    use HasFactory;
    protected $table = 'file_lo';
    protected $primaryKey = 'file_lo_id';
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
        'pengiriman_id',
        'produk_id',
        'jumlah',
        'satuan_jumlah',
        'no_faktur',
        'no_lo',
        'nama_dokumen',
        'nama_file',
        'tanggal_upload',
    ];

    public function file_lampiran_lo()
    {
        return $this->hasMany(FileLampiranLo::class, 'file_lo_id', 'file_lo_id');
    }
    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id', 'pengiriman_id');
    }
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }
    public function segel()
    {
        return $this->hasMany(Segel::class, 'file_lo_id', 'file_lo_id');
    }
    public function penerimaan()
    {
        return $this->hasOne(Penerimaan::class, 'file_lo_id', 'file_lo_id');
    }
    public function detail_rencana_pembelian()
    {
        return $this->belongsToMany(DetailRencanaPembelian::class, 'detail_rencana_file', 'file_lo_id', 'detail_rencana_pembelian_id');
    }
}
