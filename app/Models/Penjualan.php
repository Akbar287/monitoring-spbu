<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penjualan extends Model
{
    use HasFactory;
    protected $table = 'penjualan';
    protected $primaryKey = 'penjualan_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });

        // posting/pembatalan jurnal otomatis saat setoran di-accept/dicabut
        static::created(fn ($model) => \App\Support\PostingKeuangan::penjualanBerubah($model));
        static::updated(fn ($model) => \App\Support\PostingKeuangan::penjualanBerubah($model));
        static::deleted(fn ($model) => \App\Support\PostingKeuangan::batalPenjualan($model));
    }

    protected $fillable = [
        'status_setoran_id',
        'spbu_id',
        'jam_kerja_id',
        'tanggal',
        'total_penjualan',
        'total_uang_kotak',
        'is_accepted_admin',
        'is_accepted_top',
    ];

    public function status_setoran()
    {
        return $this->belongsTo(StatusSetoran::class, 'status_setoran_id', 'status_setoran_id');
    }

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class, 'penjualan_id', 'penjualan_id');
    }

    public function detail_penjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id', 'penjualan_id');
    }

    public function pj_operasional_setoran()
    {
        return $this->hasMany(PjOperasionalSetoran::class, 'penjualan_id', 'penjualan_id');
    }
}
