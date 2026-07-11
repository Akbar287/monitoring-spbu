<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PajakPembelian extends Model
{
    use HasFactory;
    protected $table = 'pajak_pembelian';
    protected $primaryKey = 'pajak_pembelian_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });

        // pengakuan persediaan + utang saat faktur pajak pembelian tercatat/berubah
        static::created(fn ($model) => \App\Support\PostingKeuangan::fakturPembelianBerubah($model));
        static::updated(fn ($model) => \App\Support\PostingKeuangan::fakturPembelianBerubah($model));
        static::deleted(fn ($model) => \App\Support\PostingKeuangan::batalFakturPembelian($model));
    }
    protected $fillable = [
        'rencana_pembelian_id',
        'pajak_pembelian_lib_id',
        'net_price',
        'ppn',
        'ppbkb',
        'pph',
        'total_amount',
        'metode_posting_pajak'
    ];

    public function rencana_pembelian()
    {
        return $this->belongsTo(RencanaPembelian::class, 'rencana_pembelian_id', 'rencana_pembelian_id');
    }

    public function pajak_pembelian_lib()
    {
        return $this->belongsTo(PajakPembelianLib::class, 'pajak_pembelian_lib_id', 'pajak_pembelian_lib_id');
    }
}
