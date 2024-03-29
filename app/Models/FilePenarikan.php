<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FilePenarikan extends Model
{
    use HasFactory;
    protected $table = 'file_penarikan';
    protected $primaryKey = 'file_penarikan_id';
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
        'penarikan_id',
        'tanggal',
        'nama_file',
        'nama_dokumen',
        'keterangan',
    ];

    public function penarikan()
    {
        return $this->belongsTo(Penarikan::class, 'penarikan_id', 'penarikan_id');
    }
}
