<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RencanaPembelian extends Model
{
    use HasFactory;
    protected $table = 'rencana_pembelian';
    protected $primaryKey = 'rencana_pembelian_id';
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
        'status_purchase_id',
        'spbu_id',
        'member_id',
        'keterangan',
        'keterangan_denied',
        'pembelian_id',
        'kode_pembelian',
        'tanggal_pembelian',
        'grand_total',
        'is_confirmation',
        'is_denied',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function detail_rencana_pembelian()
    {
        return $this->hasMany(DetailRencanaPembelian::class, 'rencana_pembelian_id', 'rencana_pembelian_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'rencana_pembelian_id', 'rencana_pembelian_id');
    }

    public function status_purchase()
    {
        return $this->belongsTo(StatusPurchase::class, 'status_purchase_id', 'status_purchase_id');
    }

    public function pajak_pembelian()
    {
        return $this->hasMany(PajakPembelian::class, 'rencana_pembelian_id', 'rencana_pembelian_id');
    }
}
