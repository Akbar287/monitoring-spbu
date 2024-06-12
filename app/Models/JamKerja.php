<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class JamKerja extends Model
{
    use HasFactory;
    protected $table = 'jam_kerja';
    protected $primaryKey = 'jam_kerja_id';
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
        'spbu_id',
        'nama_jam_kerja',
        'batas_awal_in',
        'batas_akhir_in',
        'jam_datang_in',
        'jam_pulang_out',
        'akhir_out',
        'jam_istirahat',
        'masuk_istirahat',
        'batas_telat',
        'urutan',
    ];

    public function spbu()
    {
        return $this->belongsTo(Spbu::class, 'spbu_id', 'spbu_id');
    }

    public function stand_meter()
    {
        return $this->hasMany(StandMeter::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function harga()
    {
        return $this->hasMany(Harga::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function dokumen_stok()
    {
        return $this->hasMany(DokumenStok::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function hari()
    {
        return $this->belongsToMany(Hari::class, 'hari_jam_kerja', 'jam_kerja_id', 'hari_id');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function dombak_transfer()
    {
        return $this->hasMany(DombakTransfer::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function penjadwalan()
    {
        return $this->hasMany(Penjadwalan::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function detail_rencana_pembelian_ms2()
    {
        return $this->hasMany(DetailRencanaPembelianMs2::class, 'jam_kerja_id', 'jam_kerja_id');
    }
}
