<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DokumenStok extends Model
{
    use HasFactory;
    protected $table = 'dokumen_stok';
    protected $primaryKey = 'dokumen_stok_id';
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
        'stok_inventory_id',
        'member_id',
        'dombak_id',
        'jam_kerja_id',
        'type_dokumen_stok_id',
        'tanggal',
        'stok_awal',
        'stok_akhir',
        'is_confirmation'
    ];

    public function losses()
    {
        return $this->hasMany(Losses::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }

    public function stok_inventory()
    {
        return $this->belongsTo(StokInventory::class, 'stok_inventory_id', 'stok_inventory_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function jam_kerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id', 'jam_kerja_id');
    }

    public function type_dokumen_stok()
    {
        return $this->belongsTo(TypeDokumenStok::class, 'type_dokumen_stok_id', 'type_dokumen_stok_id');
    }

    public function dombak()
    {
        return $this->belongsTo(Dombak::class, 'dombak_id', 'dombak_id');
    }

    public function penerimaan()
    {
        return $this->hasOne(Penerimaan::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }

    public function detail_tera()
    {
        return $this->hasOne(DetailTera::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }

    public function stand_meter()
    {
        return $this->belongsToMany(StandMeter::class, 'stand_meter_dokumen_stok', 'dokumen_stok_id', 'stand_meter_id');
    }

    public function file_dokumen_stok()
    {
        return $this->hasMany(FileDokumenStok::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }

    public function konversi()
    {
        return $this->belongsToMany(Konversi::class, 'konversi_dokumen_stok', 'dokumen_stok_id', 'konversi_id');
    }

    public function penerimaan_stok_taking()
    {
        return $this->hasMany(PenerimaanStokTaking::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }

    public function dombak_transfer()
    {
        return $this->belongsToMany(DombakTransfer::class, 'dombak_transfer_dokumen_stok', 'dokumen_stok_id', 'dombak_transfer_id');
    }
}
