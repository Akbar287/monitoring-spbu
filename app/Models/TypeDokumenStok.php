<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class TypeDokumenStok extends Model
{
    use HasFactory;
    protected $table = 'type_dokumen_stok';
    protected $primaryKey = 'type_dokumen_stok_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
    public $timestamps = false;
    protected $fillable = [
        'type_movement',
        'deskripsi'
    ];

    public function dokumen_stok()
    {
        return $this->hasMany(DokumenStok::class, 'type_dokumen_stok_id', 'type_dokumen_stok_id');
    }
}
