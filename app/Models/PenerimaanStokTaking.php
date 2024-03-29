<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PenerimaanStokTaking extends Model
{
    use HasFactory;
    protected $table = 'penerimaan_stok_taking';
    protected $primaryKey = 'penerimaan_stok_taking_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'penerimaan_id',
        'dokumen_stok_id',
    ];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_id', 'penerimaan_id');
    }
    public function dokumen_stok()
    {
        return $this->belongsTo(DokumenStok::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }
}
