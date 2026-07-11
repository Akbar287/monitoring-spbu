<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penarikan extends Model
{
    use HasFactory;
    protected $table = 'penarikan';
    protected $primaryKey = 'penarikan_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });

        // posting/pembatalan jurnal otomatis saat setoran bank di-accept/dicabut
        static::created(fn ($model) => \App\Support\PostingKeuangan::penarikanBerubah($model));
        static::updated(fn ($model) => \App\Support\PostingKeuangan::penarikanBerubah($model));
        static::deleted(fn ($model) => \App\Support\PostingKeuangan::batalPenarikan($model));
    }

    protected $fillable = [
        'penjualan_id',
        'member_id',
        'tanggal',
        'nama_bank',
        'no_referensi',
        'metode_transfer',
        'total_uang',
        'is_accepted',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id', 'penjualan_id');
    }

    public function file_penarikan()
    {
        return $this->hasMany(FilePenarikan::class, 'penarikan_id', 'penarikan_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }
}
