<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FileLampiranLo extends Model
{
    use HasFactory;
    protected $table = 'file_lampiran_lo';
    protected $primaryKey = 'file_lampiran_lo_id';
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
        'file_lo_id',
        'nama_file',
        'nama_dokumen',
        'tanggal_upload'
    ];

    public function file_lo()
    {
        return $this->belongsTo(FileLo::class, 'file_lo_id', 'file_lo_id');
    }
}
