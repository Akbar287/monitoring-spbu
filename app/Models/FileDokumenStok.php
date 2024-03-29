<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FileDokumenStok extends Model
{
    use HasFactory;
    protected $table = 'file_dokumen_stok';
    protected $primaryKey = 'file_dokumen_stok_id';
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
        'dokumen_stok_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload'
    ];

    public function dokumen_stok()
    {
        return $this->belongsTo(DokumenStok::class, 'dokumen_stok_id', 'dokumen_stok_id');
    }
}
