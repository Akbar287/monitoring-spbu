<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penerimaan extends Model
{
    use HasFactory;
    protected $table = 'penerimaan';
    protected $primaryKey = 'penerimaan_id';
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
        'file_lo_id',
        'dokumen_stok_id',
        'tanggal',
    ];

    public function file_lo()
    {
        return $this->belongsTo(FileLo::class, 'file_lo_id', 'file_lo_id');
    }

    public function file_penerimaan()
    {
        return $this->hasMany(FilePenerimaan::class, 'penerimaan_id', 'penerimaan_id');
    }

    public function dokumen_stok()
    {
        return $this->belongsTo(DokumenStok::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }

    public function petty_cash()
    {
        return $this->belongsToMany(PettyCash::class, 'penerimaan_petty_cash', 'penerimaan_id', 'petty_cash_id');
    }

    public function penerimaan_stok_taking()
    {
        return $this->hasOne(PenerimaanStokTaking::class, 'penerimaan_id', 'penerimaan_id');
    }

    public function penerimaan_stok_taking_penyesuaian()
    {
        return $this->hasOne(PenerimaanStokTakingPenyesuaian::class, 'penerimaan_id', 'penerimaan_id');
    }
}
