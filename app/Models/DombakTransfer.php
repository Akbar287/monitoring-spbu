<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DombakTransfer extends Model
{
    use HasFactory;
    protected $table = 'dombak_transfer';
    protected $primaryKey = 'dombak_transfer_id';
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
        'is_confirmation'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function dokumen_stok()
    {
        return $this->belongsToMany(DokumenStok::class, 'dombak_transfer_dokumen_stok', 'dombak_transfer_id', 'dokumen_stok_id');
    }
}
