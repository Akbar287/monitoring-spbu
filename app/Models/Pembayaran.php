<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';
    protected $primaryKey = 'pembayaran_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });

        // posting/pembatalan jurnal otomatis saat pembayaran dikonfirmasi/dicabut
        static::created(fn ($model) => \App\Support\PostingKeuangan::pembayaranBerubah($model));
        static::updated(fn ($model) => \App\Support\PostingKeuangan::pembayaranBerubah($model));
        static::deleted(fn ($model) => \App\Support\PostingKeuangan::batalPembayaran($model));
    }

    protected $fillable = [
        'rencana_pembelian_id',
        'member_id',
        'tanggal',
        'no_cek_bg',
        'no_rekening',
        'nama_rekening',
        'total_setoran',
        'is_confirmation_admin',
        'is_confirmation_top',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function rencana_pembelian()
    {
        return $this->belongsTo(RencanaPembelian::class, 'rencana_pembelian_id', 'rencana_pembelian_id');
    }

    public function file_pembayaran()
    {
        return $this->hasMany(FilePembayaran::class, 'pembayaran_id', 'pembayaran_id');
    }
}
