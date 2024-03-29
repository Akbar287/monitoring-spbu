<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DetailTera extends Model
{
    use HasFactory;

    protected $table = 'detail_tera';
    protected $primaryKey = 'detail_tera_id';
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
        'tera_id',
        'dokumen_stok_id',
        'quantity',
        'harga',
        'keterangan',
    ];

    public function tera()
    {
        return $this->belongsTo(Tera::class, 'tera_id', 'tera_id');
    }
    public function dokumen_stok()
    {
        return $this->belongsTo(DokumenStok::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }
}
