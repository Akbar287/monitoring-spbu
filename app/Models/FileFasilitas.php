<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FileFasilitas extends Model
{
    use HasFactory;
    protected $table = 'file_fasilitas';
    protected $primaryKey = 'file_fasilitas_id';
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
        'fasilitas_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload'
    ];

    public function fasilitas()
    {
        return $this->belongsTo(Fasilitas::class, 'fasilitas_id', 'fasilitas_id');
    }
}
