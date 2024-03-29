<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FilePenerimaan extends Model
{
    use HasFactory;
    protected $table = 'file_penerimaan';
    protected $primaryKey = 'file_penerimaan_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'penerimaan_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload',
    ];

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_id', 'penerimaan_id');
    }
}
